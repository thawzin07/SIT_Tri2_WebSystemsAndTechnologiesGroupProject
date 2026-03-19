<?php

namespace App\Models;

class PaymentModel extends BaseModel
{
    public function createPending(array $data): int
    {
        $stmt = $this->db->prepare('INSERT INTO payments
            (user_id, membership_id, plan_id, provider, provider_session_id, provider_payment_intent_id, amount, currency, payment_type, status, paid_at, created_at, updated_at)
            VALUES
            (:user_id, :membership_id, :plan_id, :provider, :provider_session_id, :provider_payment_intent_id, :amount, :currency, :payment_type, :status, NULL, NOW(), NOW())');
        $stmt->execute([
            'user_id' => $data['user_id'],
            'membership_id' => $data['membership_id'],
            'plan_id' => $data['plan_id'],
            'provider' => $data['provider'] ?? 'stripe',
            'provider_session_id' => $data['provider_session_id'],
            'provider_payment_intent_id' => $data['provider_payment_intent_id'] ?? null,
            'amount' => $data['amount'],
            'currency' => strtoupper((string) $data['currency']),
            'payment_type' => $data['payment_type'],
            'status' => $data['status'] ?? 'pending',
        ]);

        return (int) $this->db->lastInsertId();
    }

    public function findByIdForUser(int $id, int $userId): ?array
    {
        $stmt = $this->db->prepare('SELECT p.*, mp.name AS plan_name
            FROM payments p
            JOIN membership_plans mp ON mp.id = p.plan_id
            WHERE p.id = :id AND p.user_id = :user_id
            LIMIT 1');
        $stmt->execute(['id' => $id, 'user_id' => $userId]);
        return $stmt->fetch() ?: null;
    }

    public function findBySessionOrIntent(?string $sessionId, ?string $paymentIntentId): ?array
    {
        if ($sessionId) {
            $stmt = $this->db->prepare('SELECT * FROM payments WHERE provider_session_id = :provider_session_id LIMIT 1');
            $stmt->execute(['provider_session_id' => $sessionId]);
            $row = $stmt->fetch();
            if ($row) {
                return $row;
            }
        }

        if ($paymentIntentId) {
            $stmt = $this->db->prepare('SELECT * FROM payments WHERE provider_payment_intent_id = :provider_payment_intent_id LIMIT 1');
            $stmt->execute(['provider_payment_intent_id' => $paymentIntentId]);
            $row = $stmt->fetch();
            if ($row) {
                return $row;
            }
        }

        return null;
    }

    public function findBySessionOrIntentForUpdate(?string $sessionId, ?string $paymentIntentId): ?array
    {
        if ($sessionId) {
            $stmt = $this->db->prepare('SELECT * FROM payments WHERE provider_session_id = :provider_session_id LIMIT 1 FOR UPDATE');
            $stmt->execute(['provider_session_id' => $sessionId]);
            $row = $stmt->fetch();
            if ($row) {
                return $row;
            }
        }

        if ($paymentIntentId) {
            $stmt = $this->db->prepare('SELECT * FROM payments WHERE provider_payment_intent_id = :provider_payment_intent_id LIMIT 1 FOR UPDATE');
            $stmt->execute(['provider_payment_intent_id' => $paymentIntentId]);
            $row = $stmt->fetch();
            if ($row) {
                return $row;
            }
        }

        return null;
    }

    public function findLatestPendingForUser(int $userId): ?array
    {
        $stmt = $this->db->prepare('SELECT p.*, mp.name AS plan_name
            FROM payments p
            JOIN membership_plans mp ON mp.id = p.plan_id
            WHERE p.user_id = :user_id AND p.status = :status
            ORDER BY p.id DESC
            LIMIT 1');
        $stmt->execute(['user_id' => $userId, 'status' => 'pending']);
        return $stmt->fetch() ?: null;
    }

    public function findRecentFailedForUser(int $userId): ?array
    {
        $stmt = $this->db->prepare('SELECT p.*, mp.name AS plan_name
            FROM payments p
            JOIN membership_plans mp ON mp.id = p.plan_id
            WHERE p.user_id = :user_id AND p.status = :status
            ORDER BY p.id DESC
            LIMIT 1');
        $stmt->execute(['user_id' => $userId, 'status' => 'failed']);
        return $stmt->fetch() ?: null;
    }

    public function findPendingByUserPlanAndType(int $userId, int $planId, string $paymentType): ?array
    {
        $stmt = $this->db->prepare('SELECT p.*, mp.name AS plan_name
            FROM payments p
            JOIN membership_plans mp ON mp.id = p.plan_id
            WHERE p.user_id = :user_id
              AND p.plan_id = :plan_id
              AND p.payment_type = :payment_type
              AND p.status = :status
            ORDER BY p.id DESC
            LIMIT 1');
        $stmt->execute([
            'user_id' => $userId,
            'plan_id' => $planId,
            'payment_type' => $paymentType,
            'status' => 'pending',
        ]);
        return $stmt->fetch() ?: null;
    }

    public function billingHistoryForUser(int $userId): array
    {
        $stmt = $this->db->prepare('SELECT p.*, mp.name AS plan_name
            FROM payments p
            JOIN membership_plans mp ON mp.id = p.plan_id
            WHERE p.user_id = :user_id
            ORDER BY p.id DESC');
        $stmt->execute(['user_id' => $userId]);
        return $stmt->fetchAll();
    }

    public function updateSessionForRetry(int $paymentId, string $sessionId, ?string $intentId = null): void
    {
        $stmt = $this->db->prepare('UPDATE payments
            SET provider_session_id = :provider_session_id,
                provider_payment_intent_id = :provider_payment_intent_id,
                status = :status,
                updated_at = NOW()
            WHERE id = :id');
        $stmt->execute([
            'provider_session_id' => $sessionId,
            'provider_payment_intent_id' => $intentId,
            'status' => 'pending',
            'id' => $paymentId,
        ]);
    }

    public function markPaid(int $paymentId, ?string $intentId = null): void
    {
        $stmt = $this->db->prepare('UPDATE payments
            SET status = :status,
                provider_payment_intent_id = COALESCE(:provider_payment_intent_id, provider_payment_intent_id),
                paid_at = NOW(),
                updated_at = NOW()
            WHERE id = :id');
        $stmt->execute([
            'status' => 'paid',
            'provider_payment_intent_id' => $intentId,
            'id' => $paymentId,
        ]);
    }

    public function markFailed(int $paymentId, ?string $intentId = null): void
    {
        $stmt = $this->db->prepare('UPDATE payments
            SET status = :status,
                provider_payment_intent_id = COALESCE(:provider_payment_intent_id, provider_payment_intent_id),
                updated_at = NOW()
            WHERE id = :id AND status <> :already_paid');
        $stmt->execute([
            'status' => 'failed',
            'provider_payment_intent_id' => $intentId,
            'id' => $paymentId,
            'already_paid' => 'paid',
        ]);
    }

    public function attachMembership(int $paymentId, int $membershipId): void
    {
        $stmt = $this->db->prepare('UPDATE payments SET membership_id = :membership_id, updated_at = NOW() WHERE id = :id');
        $stmt->execute(['membership_id' => $membershipId, 'id' => $paymentId]);
    }
}
