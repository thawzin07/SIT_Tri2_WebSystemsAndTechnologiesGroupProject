<?php

namespace App\Services;

use App\Core\Database;
use App\Models\MembershipModel;
use App\Models\MembershipPlanModel;
use App\Models\NotificationLogModel;
use App\Models\PaymentModel;
use DateTime;
use PDO;
use RuntimeException;
use Throwable;

class PaymentService
{
    private PDO $db;
    private PaymentModel $paymentModel;
    private MembershipModel $membershipModel;
    private MembershipPlanModel $planModel;
    private NotificationLogModel $notificationLogModel;
    private StripeGateway $stripe;
    private array $config;

    public function __construct()
    {
        $this->db = Database::connection();
        $this->paymentModel = new PaymentModel();
        $this->membershipModel = new MembershipModel();
        $this->planModel = new MembershipPlanModel();
        $this->notificationLogModel = new NotificationLogModel();

        $this->config = config('payments');
        $stripeConfig = $this->config['stripe'] ?? [];
        $this->stripe = new StripeGateway(
            (string) ($stripeConfig['secret_key'] ?? ''),
            (string) ($stripeConfig['webhook_secret'] ?? '')
        );
    }

    public function createCheckout(int $userId, string $userEmail, int $planId, string $paymentType, ?string $promoCode = null): array
    {
        $this->assertSupportedPaymentType($paymentType);
        $plan = $this->planModel->find($planId);
        if (!$plan || ($plan['status'] ?? '') !== 'active') {
            throw new RuntimeException('Selected plan is unavailable.');
        }

        $currency = strtoupper((string) ($this->config['stripe']['currency'] ?? 'usd'));
        $amount = (float) $plan['price'];

        $existingPending = $this->paymentModel->findPendingByUserPlanAndType($userId, (int) $plan['id'], $paymentType);
        if ($existingPending) {
            try {
                $existingSession = $this->stripe->retrieveCheckoutSession((string) $existingPending['provider_session_id']);
                if (($existingSession['status'] ?? '') === 'open' && !empty($existingSession['url'])) {
                    return [
                        'payment_id' => (int) $existingPending['id'],
                        'checkout_url' => (string) $existingSession['url'],
                        'session_id' => (string) $existingSession['id'],
                    ];
                }
            } catch (Throwable $e) {
                error_log('Unable to reuse existing pending session for payment #' . $existingPending['id'] . ': ' . $e->getMessage());
            }
        }

        $existingPlan = $this->membershipModel->currentForUser($userId);
        if ($paymentType === 'renew') {
            if (!$existingPlan) {
                throw new RuntimeException('No existing plan found. Please purchase a new plan first.');
            }
            if ((int) $existingPlan['plan_id'] !== (int) $plan['id']) {
                throw new RuntimeException('Renew must use your current existing plan.');
            }
        }

        $session = $this->createStripeCheckoutSession(
            $userId,
            $userEmail,
            (int) $plan['id'],
            (string) $plan['name'],
            $amount,
            $currency,
            $paymentType,
            $promoCode
        );

        $paymentId = $this->paymentModel->createPending([
            'user_id' => $userId,
            'membership_id' => null,
            'plan_id' => (int) $plan['id'],
            'provider' => 'stripe',
            'provider_session_id' => (string) $session['id'],
            'provider_payment_intent_id' => null,
            'amount' => $amount,
            'currency' => $currency,
            'payment_type' => $paymentType,
            'status' => 'pending',
        ]);

        return [
            'payment_id' => $paymentId,
            'checkout_url' => (string) ($session['url'] ?? ''),
            'session_id' => (string) $session['id'],
        ];
    }

    public function resumeCheckout(int $userId, string $userEmail, int $paymentId): array
    {
        $payment = $this->paymentModel->findByIdForUser($paymentId, $userId);
        if (!$payment) {
            throw new RuntimeException('Payment record not found.');
        }
        if (!in_array($payment['status'], ['pending', 'failed'], true)) {
            throw new RuntimeException('Only pending or failed payments can be resumed.');
        }

        try {
            $session = $this->stripe->retrieveCheckoutSession((string) $payment['provider_session_id']);
            if (($session['status'] ?? '') === 'open' && !empty($session['url'])) {
                return [
                    'payment_id' => (int) $payment['id'],
                    'checkout_url' => (string) $session['url'],
                    'session_id' => (string) $session['id'],
                    'reused' => true,
                ];
            }
        } catch (Throwable $e) {
            error_log('Unable to reuse Stripe session for payment #' . $payment['id'] . ': ' . $e->getMessage());
        }

        $session = $this->createStripeCheckoutSession(
            (int) $payment['user_id'],
            $userEmail,
            (int) $payment['plan_id'],
            (string) $payment['plan_name'],
            (float) $payment['amount'],
            (string) $payment['currency'],
            (string) $payment['payment_type'],
            null
        );

        $this->paymentModel->updateSessionForRetry((int) $payment['id'], (string) $session['id']);

        return [
            'payment_id' => (int) $payment['id'],
            'checkout_url' => (string) ($session['url'] ?? ''),
            'session_id' => (string) $session['id'],
            'reused' => false,
        ];
    }

    public function processCheckoutCompleted(array $session): void
    {
        $sessionId = (string) ($session['id'] ?? '');
        $intentId = is_string($session['payment_intent'] ?? null) ? (string) $session['payment_intent'] : null;

        $this->db->beginTransaction();
        try {
            $payment = $this->paymentModel->findBySessionOrIntentForUpdate($sessionId, $intentId);
            if (!$payment) {
                throw new RuntimeException('No payment record found for Stripe session.');
            }

            if (($payment['status'] ?? '') === 'paid' && !empty($payment['membership_id'])) {
                $this->db->commit();
                return;
            }

            $metadata = $session['metadata'] ?? [];
            $this->guardAgainstTamperedMetadata($payment, is_array($metadata) ? $metadata : []);
            $this->guardAgainstAmountMismatch($payment, (int) ($session['amount_total'] ?? 0), (string) ($session['currency'] ?? ''));

            $this->paymentModel->markPaid((int) $payment['id'], $intentId);

            if (empty($payment['membership_id'])) {
                $membershipId = $this->applyMembershipFromPayment($payment);
                $this->paymentModel->attachMembership((int) $payment['id'], $membershipId);
                $payment['membership_id'] = $membershipId;
            }

            $this->queueNotificationHooks($payment);
            $this->db->commit();
        } catch (Throwable $e) {
            if ($this->db->inTransaction()) {
                $this->db->rollBack();
            }
            throw $e;
        }
    }

    public function processPaymentFailed(?string $sessionId, ?string $intentId): void
    {
        $payment = $this->paymentModel->findBySessionOrIntent($sessionId, $intentId);
        if (!$payment) {
            return;
        }

        $this->paymentModel->markFailed((int) $payment['id'], $intentId);
    }

    public function verifyStripeSignature(string $payload, string $signatureHeader): bool
    {
        return $this->stripe->verifyWebhookSignature($payload, $signatureHeader);
    }

    private function createStripeCheckoutSession(
        int $userId,
        string $userEmail,
        int $planId,
        string $planName,
        float $amount,
        string $currency,
        string $paymentType,
        ?string $promoCode
    ): array {
        $appUrl = rtrim((string) (config('app')['url'] ?? ''), '/');
        if ($appUrl === '') {
            throw new RuntimeException('APP_URL is not configured.');
        }

        $lineItemAmount = (int) round($amount * 100);
        if ($lineItemAmount < 50) {
            throw new RuntimeException('Invalid plan price configured.');
        }

        $params = [
            'mode' => 'payment',
            'success_url' => $appUrl . '/member/dashboard?payment=success&session_id={CHECKOUT_SESSION_ID}#billing',
            'cancel_url' => $appUrl . '/member/dashboard?payment=cancelled#billing',
            'customer_email' => $userEmail,
            'metadata[user_id]' => (string) $userId,
            'metadata[plan_id]' => (string) $planId,
            'metadata[payment_type]' => $paymentType,
            'line_items[0][quantity]' => '1',
            'line_items[0][price_data][currency]' => strtolower($currency),
            'line_items[0][price_data][unit_amount]' => (string) $lineItemAmount,
            'line_items[0][price_data][product_data][name]' => 'PulsePoint ' . ucfirst($paymentType) . ' - ' . $planName,
            'line_items[0][price_data][product_data][description]' => 'Membership ' . $paymentType . ' for plan: ' . $planName,
            'payment_intent_data[metadata][user_id]' => (string) $userId,
            'payment_intent_data[metadata][plan_id]' => (string) $planId,
            'payment_intent_data[metadata][payment_type]' => $paymentType,
        ];

        $couponId = $this->resolveCouponId($promoCode);
        if ($couponId !== null) {
            $params['discounts[0][coupon]'] = $couponId;
        } else {
            $params['allow_promotion_codes'] = 'true';
        }

        return $this->stripe->createCheckoutSession($params);
    }

    private function applyMembershipFromPayment(array $payment): int
    {
        $plan = $this->planModel->find((int) $payment['plan_id']);
        if (!$plan) {
            throw new RuntimeException('Unable to locate membership plan during payment finalization.');
        }

        $today = new DateTime('today');
        $userId = (int) $payment['user_id'];
        $planId = (int) $payment['plan_id'];
        $paymentType = (string) ($payment['payment_type'] ?? '');
        if (!in_array($paymentType, ['purchase', 'renew'], true)) {
            throw new RuntimeException('Unsupported payment type.');
        }

        $existingPlan = $this->membershipModel->currentForUser($userId);
        $latestScheduled = $this->membershipModel->latestScheduledActiveForUser($userId);
        if ($paymentType === 'renew') {
            if (!$existingPlan) {
                throw new RuntimeException('No existing plan found to renew.');
            }
            if ((int) $existingPlan['plan_id'] !== $planId) {
                throw new RuntimeException('Renew payment does not match your existing plan.');
            }
        }

        $startDate = clone $today;
        if ($latestScheduled) {
            $startDate = (new DateTime((string) $latestScheduled['end_date']))->modify('+1 day');
        }

        $startYmd = $startDate->format('Y-m-d');
        $endYmd = (clone $startDate)->modify('+' . (int) $plan['duration_months'] . ' months')->format('Y-m-d');
        $existing = $this->membershipModel->findDuplicateWindow($userId, $planId, $startYmd, $endYmd);
        if ($existing) {
            return (int) $existing['id'];
        }

        return $this->membershipModel->create($userId, $planId, $startYmd, $endYmd, 'active');
    }

    private function queueNotificationHooks(array $payment): void
    {
        $payload = [
            'payment_id' => (int) $payment['id'],
            'plan_id' => (int) $payment['plan_id'],
            'payment_type' => (string) $payment['payment_type'],
            'amount' => (float) $payment['amount'],
            'currency' => (string) $payment['currency'],
        ];

        $this->notificationLogModel->queue(
            (int) $payment['user_id'],
            'email',
            'payment_success',
            'member_email_placeholder',
            $payload
        );

        $eventType = ($payment['payment_type'] ?? '') === 'renew' ? 'membership_renewed' : 'payment_success';
        $this->notificationLogModel->queue(
            (int) $payment['user_id'],
            'telegram',
            $eventType,
            'member_telegram_placeholder',
            $payload
        );
    }

    private function guardAgainstTamperedMetadata(array $payment, array $metadata): void
    {
        $metaUserId = (int) ($metadata['user_id'] ?? 0);
        $metaPlanId = (int) ($metadata['plan_id'] ?? 0);
        $metaType = (string) ($metadata['payment_type'] ?? '');

        if ($metaUserId !== (int) $payment['user_id'] || $metaPlanId !== (int) $payment['plan_id'] || $metaType !== (string) $payment['payment_type']) {
            throw new RuntimeException('Webhook metadata mismatch detected.');
        }
    }

    private function guardAgainstAmountMismatch(array $payment, int $amountTotal, string $currency): void
    {
        $expectedCents = (int) round(((float) $payment['amount']) * 100);
        $eventCents = max(0, $amountTotal);
        $eventCurrency = strtoupper($currency);
        $expectedCurrency = strtoupper((string) $payment['currency']);

        if ($expectedCents !== $eventCents || $eventCurrency !== $expectedCurrency) {
            throw new RuntimeException('Stripe amount/currency mismatch detected.');
        }
    }

    private function resolveCouponId(?string $promoCode): ?string
    {
        $code = strtoupper(trim((string) $promoCode));
        if ($code === '') {
            return null;
        }

        $raw = (string) ($this->config['stripe']['promo_codes'] ?? '');
        if ($raw === '') {
            throw new RuntimeException('Promo codes are not configured.');
        }

        $map = [];
        foreach (explode(',', $raw) as $pair) {
            $pair = trim($pair);
            if ($pair === '' || !str_contains($pair, ':')) {
                continue;
            }
            [$label, $couponId] = explode(':', $pair, 2);
            $label = strtoupper(trim($label));
            $couponId = trim($couponId);
            if ($label !== '' && $couponId !== '') {
                $map[$label] = $couponId;
            }
        }

        if (!isset($map[$code])) {
            throw new RuntimeException('Promo code is invalid.');
        }

        return $map[$code];
    }

    private function assertSupportedPaymentType(string $paymentType): void
    {
        if (!in_array($paymentType, ['purchase', 'renew'], true)) {
            throw new RuntimeException('Unsupported payment type.');
        }
    }
}
