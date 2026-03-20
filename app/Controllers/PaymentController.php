<?php

namespace App\Controllers;

use App\Core\Controller;
use App\Services\PaymentService;
use Throwable;

class PaymentController extends Controller
{
    public function checkout(): void
    {
        $this->requireMember();
        verify_csrf();

        $user = current_user();
        $planId = (int) ($_POST['plan_id'] ?? 0);
        $paymentType = trim((string) ($_POST['payment_type'] ?? 'purchase'));
        $promoCode = trim((string) ($_POST['promo_code'] ?? ''));

        if ($planId < 1) {
            flash('error', 'Invalid plan selected.');
            redirect('/plans');
        }

        try {
            $result = (new PaymentService())->createCheckout(
                (int) $user['id'],
                (string) $user['email'],
                $planId,
                $paymentType,
                $promoCode !== '' ? $promoCode : null
            );

            if (($result['checkout_url'] ?? '') === '') {
                throw new \RuntimeException('Stripe checkout URL was not returned.');
            }

            redirect((string) $result['checkout_url']);
        } catch (Throwable $e) {
            error_log('Checkout initiation failed for user #' . (int) $user['id'] . ': ' . $e->getMessage());
            flash('error', 'Unable to start checkout: ' . $e->getMessage());
            redirect('/member/dashboard#billing');
        }
    }

    public function resumeCheckout(): void
    {
        $this->requireMember();
        verify_csrf();

        $paymentId = (int) ($_POST['payment_id'] ?? 0);
        if ($paymentId < 1) {
            flash('error', 'Invalid payment selected for resume.');
            redirect('/member/dashboard#billing');
        }

        $user = current_user();
        try {
            $result = (new PaymentService())->resumeCheckout((int) $user['id'], (string) $user['email'], $paymentId);
            if (($result['checkout_url'] ?? '') === '') {
                throw new \RuntimeException('Unable to resume checkout.');
            }

            flash('success', !empty($result['reused']) ? 'Resuming your checkout.' : 'Created a fresh checkout session.');
            redirect((string) $result['checkout_url']);
        } catch (Throwable $e) {
            error_log('Checkout resume failed for user #' . (int) $user['id'] . ': ' . $e->getMessage());
            flash('error', 'Unable to resume checkout: ' . $e->getMessage());
            redirect('/member/dashboard#billing');
        }
    }

    public function stripeWebhook(): void
    {
        $payload = file_get_contents('php://input') ?: '';
        $signature = (string) ($_SERVER['HTTP_STRIPE_SIGNATURE'] ?? '');
        $service = new PaymentService();

        if (!$service->verifyStripeSignature($payload, $signature)) {
            http_response_code(400);
            echo 'Invalid webhook signature';
            return;
        }

        $event = json_decode($payload, true);
        if (!is_array($event)) {
            http_response_code(400);
            echo 'Invalid webhook payload';
            return;
        }

        $type = (string) ($event['type'] ?? '');
        $object = $event['data']['object'] ?? [];

        try {
            if ($type === 'checkout.session.completed' && is_array($object)) {
                $service->processCheckoutCompleted($object);
            } elseif ($type === 'payment_intent.payment_failed' && is_array($object)) {
                $intentId = is_string($object['id'] ?? null) ? (string) $object['id'] : null;
                $service->processPaymentFailed(null, $intentId);
            } elseif ($type === 'checkout.session.expired' && is_array($object)) {
                $sessionId = is_string($object['id'] ?? null) ? (string) $object['id'] : null;
                $intentId = is_string($object['payment_intent'] ?? null) ? (string) $object['payment_intent'] : null;
                $service->processPaymentFailed($sessionId, $intentId);
            }

            http_response_code(200);
            echo 'ok';
        } catch (Throwable $e) {
            error_log('Stripe webhook processing failed: ' . $e->getMessage());
            http_response_code(500);
            echo 'Webhook handling error';
        }
    }
}
