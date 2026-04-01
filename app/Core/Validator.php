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

    public static function max(?string $value, int $length): bool
    {
        return self::length($value) <= $length;
    }

    public static function min(?string $value, int $length): bool
    {
        return self::length($value) >= $length;
    }

    private static function length(?string $value): int
    {
        $normalized = trim((string) $value);

        if (function_exists('mb_strlen')) {
            return mb_strlen($normalized);
        }

        return strlen($normalized);
    }
}
