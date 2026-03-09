<?php

namespace App\Core;

class Validator
{
    public static function email(string $email): bool
    {
        return (bool) filter_var($email, FILTER_VALIDATE_EMAIL);
    }

    public static function required(?string $value): bool
    {
        return trim((string) $value) !== '';
    }

    public static function max(string $value, int $length): bool
    {
        return mb_strlen(trim($value)) <= $length;
    }

    public static function min(string $value, int $length): bool
    {
        return mb_strlen(trim($value)) >= $length;
    }
}
