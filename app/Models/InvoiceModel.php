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

    public function create(array $data): int
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
            'pdf_path' => $data['pdf_path'],
            'issued_at' => $data['issued_at'],
        ]);

        return (int) $this->db->lastInsertId();
    }

    public function findOwnedById(int $invoiceId, int $userId): ?array
    {
        $stmt = $this->db->prepare('SELECT * FROM invoices WHERE id = :id AND user_id = :user_id LIMIT 1');
        $stmt->execute([
            'id' => $invoiceId,
            'user_id' => $userId,
        ]);
        return $stmt->fetch() ?: null;
    }
}
