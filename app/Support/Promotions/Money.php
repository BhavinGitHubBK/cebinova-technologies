<?php

namespace App\Support\Promotions;

/**
 * Rupee amounts stored as integer paise so discounts never use floating point.
 */
final class Money
{
    public static function paise(int|string|float|null $amount): int
    {
        if ($amount === null || $amount === '') {
            return 0;
        }

        $value = trim((string) $amount);
        $negative = str_starts_with($value, '-');
        $value = ltrim($value, '+-');
        if (! preg_match('/^\d+(\.\d+)?$/', $value)) {
            return 0;
        }

        [$whole, $fraction] = array_pad(explode('.', $value, 2), 2, '0');
        $fraction = substr(str_pad($fraction, 2, '0'), 0, 2);
        $paise = ((int) $whole * 100) + (int) $fraction;

        return $negative ? -$paise : $paise;
    }

    public static function format(int $paise): string
    {
        $negative = $paise < 0;
        $paise = abs($paise);
        $formatted = intdiv($paise, 100).'.'.str_pad((string) ($paise % 100), 2, '0', STR_PAD_LEFT);

        return $negative ? '-'.$formatted : $formatted;
    }

    public static function rupees(int $paise): string
    {
        return '₹'.number_format(intdiv(abs($paise), 100), 0, '.', ',').(abs($paise) % 100 ? '.'.str_pad((string) (abs($paise) % 100), 2, '0', STR_PAD_LEFT) : '');
    }

    /**
     * Half-up percentage of a paise amount. $percent is a decimal string such as "20" or "20.50".
     */
    public static function percent(int $paise, string $percent): int
    {
        if ($paise <= 0) {
            return 0;
        }

        $basisPoints = self::paise($percent);
        $product = $paise * $basisPoints;
        $divisor = 10000;

        return intdiv($product + intdiv($divisor, 2), $divisor);
    }
}
