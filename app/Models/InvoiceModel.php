<?php

namespace App\Models;

class InvoiceModel extends BaseModel
{
    public function findByPaymentId(int $paymentId): ?array
    {
        $stmt = $this->db->prepare('SELECT * FROM invoices WHERE payment_id = :payment_id LIMIT 1');
        $stmt->execute(['payment_id' => $paymentId]);
        return $stmt->fetch() ?: null;
    }

    public function createForPayment(array $data): int
    {
        $stmt = $this->db->prepare('INSERT INTO invoices
            (payment_id, user_id, invoice_no, subtotal, tax, total, currency, pdf_path, issued_at, created_at)
            VALUES
            (:payment_id, :user_id, :invoice_no, :subtotal, :tax, :total, :currency, :pdf_path, :issued_at, NOW())');
        $stmt->execute([
            'payment_id' => $data['payment_id'],
            'user_id' => $data['user_id'],
            'invoice_no' => $data['invoice_no'],
            'subtotal' => $data['subtotal'],
            'tax' => $data['tax'],
            'total' => $data['total'],
            'currency' => strtoupper((string) $data['currency']),
            'pdf_path' => $data['pdf_path'] ?? 'generated-on-demand',
            'issued_at' => $data['issued_at'],
        ]);

        return (int) $this->db->lastInsertId();
    }

    public function findDownloadDataByPaymentIdForUser(int $paymentId, int $userId): ?array
    {
        $stmt = $this->db->prepare('SELECT
                i.id,
                i.payment_id,
                i.user_id,
                i.invoice_no,
                i.subtotal,
                i.tax,
                i.total,
                i.currency,
                i.pdf_path,
                i.issued_at,
                i.created_at,
                p.status AS payment_status,
                p.payment_type,
                p.paid_at,
                p.created_at AS payment_created_at,
                mp.name AS plan_name,
                u.full_name,
                u.email
            FROM invoices i
            JOIN payments p ON p.id = i.payment_id
            JOIN membership_plans mp ON mp.id = p.plan_id
            JOIN users u ON u.id = i.user_id
            WHERE i.payment_id = :payment_id
              AND i.user_id = :user_id
            LIMIT 1');
        $stmt->execute([
            'payment_id' => $paymentId,
            'user_id' => $userId,
        ]);

        return $stmt->fetch() ?: null;
    }
}
