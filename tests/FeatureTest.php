<?php

declare(strict_types=1);

require_once __DIR__ . '/../bootstrap.php';

$routes = config('routes');

$requiredRoutes = [
    ['POST', '/member/bookings/waitlist/cancel', 'MemberController', 'cancelWaitlist', 'member'],
    ['POST', '/member/bookings/book', 'MemberController', 'bookClass', 'member'],
    ['POST', '/member/bookings/cancel', 'MemberController', 'cancelBooking', 'member'],
    ['POST', '/member/payments/checkout', 'PaymentController', 'checkout', 'member'],
    ['POST', '/member/payments/resume', 'PaymentController', 'resumeCheckout', 'member'],
    ['GET', '/member/invoices/download', 'MemberController', 'downloadInvoice', 'member'],
    ['POST', '/webhooks/stripe', 'PaymentController', 'stripeWebhook', 'public'],
];

$assert = function (bool $condition, string $message): void {
    if (!$condition) {
        fwrite(STDERR, "[FAIL] {$message}\n");
        exit(1);
    }
    echo "[PASS] {$message}\n";
};

foreach ($requiredRoutes as $expected) {
    $assert(in_array($expected, $routes, true), 'Route exists: ' . implode(' ', [$expected[0], $expected[1]]) . ' [' . $expected[4] . ']');
}

echo "Feature checks completed.\n";
