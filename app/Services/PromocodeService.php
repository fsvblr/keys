<?php

declare(strict_types=1);

namespace App\Services;

final class PromocodeService
{
    public static function calculateDiscount(array $promo, float $price, string $currency): float
    {
        $value = (float) $promo['value'];

        if ($promo['type'] === 'percent') {
            return round($price * $value / 100, 2);
        }

        if ($promo['type'] === 'amount') {
            if (($promo['currency'] ?? 'RUB') === $currency) {
                return min($value, $price);
            }
        }

        return 0.0;
    }
}
