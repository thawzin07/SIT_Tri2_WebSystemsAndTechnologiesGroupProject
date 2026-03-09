<?php

declare(strict_types=1);

return [
    'name' => getenv('APP_NAME') ?: 'PulsePoint Fitness',
    'url' => getenv('APP_URL') ?: 'http://localhost:8000',
    'env' => getenv('APP_ENV') ?: 'development',
    'debug' => filter_var(getenv('APP_DEBUG') ?: 'true', FILTER_VALIDATE_BOOL),
];
