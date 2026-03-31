<?php

namespace App\Services;

use App\Models\InvoiceModel;
use RuntimeException;

class InvoiceService
{
    private InvoiceModel $invoiceModel;

    public function __construct()
    {
        $this->invoiceModel = new InvoiceModel();
    }

    public function ensureInvoiceForPayment(array $payment): array
    {
        $paymentId = (int) ($payment['id'] ?? 0);
        if ($paymentId < 1) {
            throw new RuntimeException('Invalid payment for invoice generation.');
        }

        $existing = $this->invoiceModel->findByPaymentId($paymentId);
        if ($existing) {
            return $existing;
        }

        $issuedAt = date('Y-m-d H:i:s');
        $invoiceNo = $this->buildInvoiceNo($paymentId);
        $pdfRelativePath = 'invoices/' . $invoiceNo . '.pdf';

        $this->writeInvoicePdf($pdfRelativePath, $invoiceNo, $payment, $issuedAt);

        $invoiceId = $this->invoiceModel->create([
            'payment_id' => $paymentId,
            'user_id' => (int) $payment['user_id'],
            'invoice_no' => $invoiceNo,
            'subtotal' => (float) $payment['amount'],
            'tax' => 0.00,
            'total' => (float) $payment['amount'],
            'currency' => (string) $payment['currency'],
            'pdf_path' => $pdfRelativePath,
            'issued_at' => $issuedAt,
        ]);

        return $this->invoiceModel->findOwnedById($invoiceId, (int) $payment['user_id']) ?? [];
    }

    private function buildInvoiceNo(int $paymentId): string
    {
        return 'INV-' . date('Ymd') . '-' . str_pad((string) $paymentId, 6, '0', STR_PAD_LEFT);
    }

    private function writeInvoicePdf(string $relativePath, string $invoiceNo, array $payment, string $issuedAt): void
    {
        $absolutePath = dirname(__DIR__, 2) . '/public/' . ltrim($relativePath, '/');
        $directory = dirname($absolutePath);
        if (!is_dir($directory) && !mkdir($directory, 0775, true) && !is_dir($directory)) {
            throw new RuntimeException('Unable to create invoice directory.');
        }

        $amount = number_format((float) $payment['amount'], 2, '.', '');
        $currency = strtoupper((string) $payment['currency']);
        $lines = [
            'PulsePoint Fitness Invoice',
            'Invoice No: ' . $invoiceNo,
            'Payment ID: #' . (int) $payment['id'],
            'Type: ' . ucfirst((string) $payment['payment_type']),
            'Amount: ' . $currency . ' ' . $amount,
            'Issued At: ' . $issuedAt,
        ];

        $stream = "BT /F1 12 Tf 50 780 Td ";
        foreach ($lines as $index => $line) {
            if ($index > 0) {
                $stream .= "T* ";
            }
            $stream .= '(' . $this->pdfEscape($line) . ') Tj ';
        }
        $stream .= "ET\n";

        $objects = [
            1 => '<< /Type /Catalog /Pages 2 0 R >>',
            2 => '<< /Type /Pages /Kids [3 0 R] /Count 1 >>',
            3 => '<< /Type /Page /Parent 2 0 R /MediaBox [0 0 595 842] /Resources << /Font << /F1 5 0 R >> >> /Contents 4 0 R >>',
            4 => "<< /Length " . strlen($stream) . " >>\nstream\n{$stream}endstream",
            5 => '<< /Type /Font /Subtype /Type1 /BaseFont /Helvetica >>',
        ];

        $pdf = "%PDF-1.4\n";
        $offsets = [0];
        foreach ($objects as $id => $body) {
            $offsets[$id] = strlen($pdf);
            $pdf .= "{$id} 0 obj\n{$body}\nendobj\n";
        }
        $xrefPos = strlen($pdf);
        $count = count($objects) + 1;
        $pdf .= "xref\n0 {$count}\n";
        $pdf .= "0000000000 65535 f \n";
        for ($i = 1; $i < $count; $i++) {
            $pdf .= sprintf("%010d 00000 n \n", $offsets[$i] ?? 0);
        }
        $pdf .= "trailer\n<< /Size {$count} /Root 1 0 R >>\nstartxref\n{$xrefPos}\n%%EOF";

        if (file_put_contents($absolutePath, $pdf) === false) {
            throw new RuntimeException('Unable to write invoice PDF.');
        }
    }

    private function pdfEscape(string $value): string
    {
        return str_replace(['\\', '(', ')'], ['\\\\', '\(', '\)'], $value);
    }
}
