<?php

namespace App\Services;

use RuntimeException;

class StripeGateway
{
    private string $apiBase = 'https://api.stripe.com/v1';
    private string $secretKey;
    private string $webhookSecret;

    public function __construct(string $secretKey, string $webhookSecret)
    {
        $this->secretKey = $secretKey;
        $this->webhookSecret = $webhookSecret;
    }

    public function createCheckoutSession(array $params): array
    {
        return $this->request('POST', '/checkout/sessions', $params);
    }

    public function retrieveCheckoutSession(string $sessionId): array
    {
        return $this->request('GET', '/checkout/sessions/' . rawurlencode($sessionId));
    }

    public function verifyWebhookSignature(string $payload, string $signatureHeader, int $tolerance = 300): bool
    {
        if ($this->webhookSecret === '' || $signatureHeader === '') {
            return false;
        }

        $parts = [];
        foreach (explode(',', $signatureHeader) as $part) {
            $kv = explode('=', trim($part), 2);
            if (count($kv) === 2) {
                $parts[$kv[0]][] = $kv[1];
            }
        }

        $timestamp = isset($parts['t'][0]) ? (int) $parts['t'][0] : 0;
        $signatures = $parts['v1'] ?? [];
        if ($timestamp <= 0 || empty($signatures)) {
            return false;
        }

        if (abs(time() - $timestamp) > $tolerance) {
            return false;
        }

        $signedPayload = $timestamp . '.' . $payload;
        $expected = hash_hmac('sha256', $signedPayload, $this->webhookSecret);
        foreach ($signatures as $signature) {
            if (hash_equals($expected, $signature)) {
                return true;
            }
        }

        return false;
    }

    private function request(string $method, string $path, array $params = []): array
    {
        if ($this->secretKey === '') {
            throw new RuntimeException('Stripe secret key is not configured.');
        }
        if (!function_exists('curl_init')) {
            throw new RuntimeException('cURL extension is required for Stripe integration.');
        }

        $url = $this->apiBase . $path;
        $ch = curl_init();

        if ($method === 'GET' && !empty($params)) {
            $url .= '?' . http_build_query($params);
        }

        $options = [
            CURLOPT_URL => $url,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_TIMEOUT => 30,
            CURLOPT_HTTPHEADER => [
                'Authorization: Bearer ' . $this->secretKey,
                'Content-Type: application/x-www-form-urlencoded',
            ],
        ];

        if ($method === 'POST') {
            $options[CURLOPT_POST] = true;
            $options[CURLOPT_POSTFIELDS] = http_build_query($params);
        }

        curl_setopt_array($ch, $options);
        $raw = curl_exec($ch);
        $httpCode = (int) curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $error = curl_error($ch);
        curl_close($ch);

        if ($raw === false) {
            throw new RuntimeException('Stripe request failed: ' . $error);
        }

        $json = json_decode($raw, true);
        if (!is_array($json)) {
            throw new RuntimeException('Stripe returned an invalid JSON response.');
        }

        if ($httpCode < 200 || $httpCode >= 300 || isset($json['error'])) {
            $message = $json['error']['message'] ?? ('Stripe HTTP error ' . $httpCode);
            throw new RuntimeException('Stripe error: ' . $message);
        }

        return $json;
    }
}
