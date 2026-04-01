<?php

namespace App\Services;

class InvoicePdfService
{
    public function streamInvoice(array $invoice): void
    {
        $pdf = $this->generateInvoicePdf($invoice);
        $fileName = preg_replace('/[^A-Za-z0-9._-]/', '-', (string) ($invoice['invoice_no'] ?? 'invoice')) ?: 'invoice';

        header('Content-Type: application/pdf');
        header('Content-Length: ' . strlen($pdf));
        header('Content-Disposition: attachment; filename="' . $fileName . '.pdf"');
        header('Cache-Control: private, max-age=0, must-revalidate');

        echo $pdf;
        exit;
    }

    public function generateInvoicePdf(array $invoice): string
    {
        $content = $this->buildInvoiceTemplate($invoice);

        $objects = [
            '<< /Type /Catalog /Pages 2 0 R >>',
            '<< /Type /Pages /Count 1 /Kids [3 0 R] >>',
            '<< /Type /Page /Parent 2 0 R /MediaBox [0 0 595 842] /Resources << /Font << /F1 5 0 R /F2 6 0 R >> >> /Contents 4 0 R >>',
            sprintf("<< /Length %d >>\nstream\n%s\nendstream", strlen($content), $content),
            '<< /Type /Font /Subtype /Type1 /BaseFont /Helvetica >>',
            '<< /Type /Font /Subtype /Type1 /BaseFont /Helvetica-Bold >>',
        ];

        $pdf = "%PDF-1.4\n";
        $offsets = [0];

        foreach ($objects as $index => $object) {
            $offsets[] = strlen($pdf);
            $pdf .= ($index + 1) . " 0 obj\n" . $object . "\nendobj\n";
        }

        $xrefOffset = strlen($pdf);
        $pdf .= "xref\n0 " . (count($objects) + 1) . "\n";
        $pdf .= "0000000000 65535 f \n";

        foreach ($offsets as $index => $offset) {
            if ($index === 0) {
                continue;
            }

            $pdf .= sprintf("%010d 00000 n \n", $offset);
        }

        $pdf .= "trailer\n<< /Size " . (count($objects) + 1) . " /Root 1 0 R >>\n";
        $pdf .= "startxref\n" . $xrefOffset . "\n%%EOF";

        return $pdf;
    }

    private function buildInvoiceTemplate(array $invoice): string
    {
        $issuedAt = $this->formatDateTime((string) ($invoice['issued_at'] ?? ''));
        $currency = strtoupper((string) ($invoice['currency'] ?? 'USD'));
        $summaryXLabel = 360.0;
        $summaryXValue = 520.0;

        $commands = [];

        $this->fillRect($commands, 40, 744, 515, 58, 0.96, 0.97, 0.99);
        $this->drawText($commands, 56, 783, 22, 'F2', config('app')['name'] ?? 'PulsePoint Fitness');
        $this->drawText($commands, 56, 763, 10, 'F1', 'Membership Invoice');

        $this->drawText($commands, 410, 783, 10, 'F2', 'Plan Bought');
        $this->drawText($commands, 410, 767, 10, 'F1', $issuedAt);
        $this->drawText($commands, 410, 749, 10, 'F2', 'Invoice No');
        $this->drawText($commands, 410, 733, 10, 'F1', (string) ($invoice['invoice_no'] ?? '-'));

        $this->drawText($commands, 56, 710, 11, 'F2', 'Billed To');
        $this->drawText($commands, 56, 693, 10, 'F1', (string) ($invoice['full_name'] ?? 'Member'));
        $this->drawText($commands, 56, 677, 10, 'F1', (string) ($invoice['email'] ?? ''));

        $this->fillRect($commands, 40, 612, 515, 26, 0.18, 0.22, 0.29);
        $this->drawText($commands, 56, 621, 10, 'F2', 'Plan', 1, 1, 1);
        $this->drawText($commands, 520, 621, 10, 'F2', 'Cost', 1, 1, 1, 'right');

        $this->drawLine($commands, 40, 612, 555, 612, 0.82, 0.84, 0.88);
        $this->drawLine($commands, 40, 576, 555, 576, 0.82, 0.84, 0.88);
        $this->drawText($commands, 56, 590, 11, 'F1', (string) ($invoice['plan_name'] ?? 'Membership Plan'));
        $this->drawText($commands, 520, 590, 11, 'F1', $this->formatMoney((float) ($invoice['subtotal'] ?? 0), $currency), 0.15, 0.17, 0.22, 'right');

        $this->drawLine($commands, 330, 520, 555, 520, 0.82, 0.84, 0.88);
        $this->drawLine($commands, 330, 490, 555, 490, 0.82, 0.84, 0.88);
        $this->drawLine($commands, 330, 460, 555, 460, 0.82, 0.84, 0.88);
        $this->drawLine($commands, 330, 430, 555, 430, 0.82, 0.84, 0.88);

        $this->drawText($commands, $summaryXLabel, 500, 10, 'F1', 'Subtotal');
        $this->drawText($commands, $summaryXValue, 500, 10, 'F1', $this->formatMoney((float) ($invoice['subtotal'] ?? 0), $currency), 0.15, 0.17, 0.22, 'right');
        $this->drawText($commands, $summaryXLabel, 470, 10, 'F1', 'Tax');
        $this->drawText($commands, $summaryXValue, 470, 10, 'F1', $this->formatMoney((float) ($invoice['tax'] ?? 0), $currency), 0.15, 0.17, 0.22, 'right');
        $this->drawText($commands, $summaryXLabel, 440, 10, 'F2', 'Total');
        $this->drawText($commands, $summaryXValue, 440, 10, 'F2', $this->formatMoney((float) ($invoice['total'] ?? 0), $currency), 0.15, 0.17, 0.22, 'right');

        $this->drawText($commands, 56, 390, 10, 'F2', 'Invoice Details');
        $this->drawText($commands, 56, 372, 10, 'F1', 'Currency: ' . $currency);
        $this->drawText($commands, 56, 356, 10, 'F1', 'Payment Type: ' . ucfirst((string) ($invoice['payment_type'] ?? 'purchase')));
        $this->drawText($commands, 56, 340, 10, 'F1', 'Payment Status: ' . ucfirst((string) ($invoice['payment_status'] ?? 'paid')));

        $this->drawText($commands, 56, 300, 9, 'F1', 'Thank you for training with PulsePoint Fitness.');
        $this->drawText($commands, 56, 286, 9, 'F1', 'This PDF is generated on demand from your billing record.');

        return implode("\n", $commands);
    }

    private function drawText(
        array &$commands,
        float $x,
        float $y,
        float $fontSize,
        string $font,
        string $text,
        float $red = 0.1,
        float $green = 0.1,
        float $blue = 0.1,
        string $align = 'left'
    ): void {
        $safeText = $this->escapePdfText($text);
        $xPosition = $x;

        if ($align === 'right') {
            $xPosition = max(40.0, $x - $this->estimateTextWidth($text, $fontSize));
        }

        $commands[] = sprintf(
            'BT /%s %.2F Tf %.3F %.3F %.3F rg 1 0 0 1 %.2F %.2F Tm (%s) Tj ET',
            $font,
            $fontSize,
            $red,
            $green,
            $blue,
            $xPosition,
            $y,
            $safeText
        );
    }

    private function drawLine(
        array &$commands,
        float $x1,
        float $y1,
        float $x2,
        float $y2,
        float $red = 0.75,
        float $green = 0.75,
        float $blue = 0.75
    ): void {
        $commands[] = sprintf('q %.3F %.3F %.3F RG %.2F %.2F m %.2F %.2F l S Q', $red, $green, $blue, $x1, $y1, $x2, $y2);
    }

    private function fillRect(array &$commands, float $x, float $y, float $width, float $height, float $red, float $green, float $blue): void
    {
        $commands[] = sprintf('q %.3F %.3F %.3F rg %.2F %.2F %.2F %.2F re f Q', $red, $green, $blue, $x, $y, $width, $height);
    }

    private function formatMoney(float $amount, string $currency): string
    {
        return $currency . ' ' . number_format($amount, 2);
    }

    private function formatDateTime(string $value): string
    {
        if ($value === '') {
            return '-';
        }

        $timestamp = strtotime($value);
        if ($timestamp === false) {
            return $value;
        }

        return date('d M Y, h:i A', $timestamp);
    }

    private function estimateTextWidth(string $text, float $fontSize): float
    {
        return strlen($this->encodePdfText($text)) * ($fontSize * 0.5);
    }

    private function escapePdfText(string $text): string
    {
        $encoded = $this->encodePdfText($text);
        return str_replace(
            ['\\', '(', ')', "\r", "\n", "\t"],
            ['\\\\', '\(', '\)', ' ', ' ', ' '],
            $encoded
        );
    }

    private function encodePdfText(string $text): string
    {
        $converted = @iconv('UTF-8', 'Windows-1252//TRANSLIT//IGNORE', $text);
        if ($converted === false || $converted === '') {
            return preg_replace('/[^\x20-\x7E]/', '', $text) ?? '';
        }

        return $converted;
    }
}
