<?php

namespace App\Support;

final class Money
{
    public const INPUT_RULE = 'regex:/\A\d{1,8}(?:\.\d{1,2})?\z/';

    /** Convert a validated decimal amount to integer poisha. */
    public static function minor(string $amount): int
    {
        [$whole, $fraction] = array_pad(explode('.', $amount, 2), 2, '');

        return (int) $whole * 100 + (int) str_pad($fraction, 2, '0', STR_PAD_RIGHT);
    }

    public static function decimal(int $minor): string
    {
        return sprintf('%d.%02d', intdiv($minor, 100), $minor % 100);
    }

    public static function display(string $amount): string
    {
        $minor = self::minor($amount);

        return 'Tk '.number_format(intdiv($minor, 100), 0, '.', ',').sprintf('.%02d', $minor % 100);
    }
}
