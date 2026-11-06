<?php

namespace App\Support;

final class OldInput
{
    public static function string(string $name, mixed $fallback = ''): string
    {
        $value = old($name, $fallback);

        return is_scalar($value) ? (string) $value : (is_scalar($fallback) ? (string) $fallback : '');
    }
}
