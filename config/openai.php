<?php

declare(strict_types=1);

return [
    'api_key' => getenv('OPENAI_API_KEY') ?: '',
    'model' => getenv('OPENAI_MODEL') ?: 'gpt-4.1-mini',
    'temperature' => (float) (getenv('OPENAI_TEMPERATURE') ?: '0.2'),
    'max_output_tokens' => (int) (getenv('OPENAI_MAX_OUTPUT_TOKENS') ?: '240'),
    'timeout_seconds' => (int) (getenv('OPENAI_TIMEOUT_SECONDS') ?: '20'),
];
