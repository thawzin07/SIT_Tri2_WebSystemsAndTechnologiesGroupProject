<?php

declare(strict_types=1);

return [
    'provider' => 'stripe',
    'stripe' => [
        'secret_key' => getenv('STRIPE_SECRET_KEY') ?: '',
        'publishable_key' => getenv('STRIPE_PUBLISHABLE_KEY') ?: '',
        'webhook_secret' => getenv('STRIPE_WEBHOOK_SECRET') ?: '',
        'currency' => strtolower((string) (getenv('STRIPE_CURRENCY') ?: 'usd')),
        'promo_codes' => (string) (getenv('STRIPE_PROMO_CODES') ?: ''),
    ],
];
