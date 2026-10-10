<?php

namespace App\Enums;

enum PromotionDiscountType: string
{
    case Percentage = 'percentage';
    case Flat = 'flat';

    public function label(): string
    {
        return match ($this) {
            self::Percentage => 'Percentage',
            self::Flat => 'Flat amount',
        };
    }

    /**
     * @return array<string, string>
     */
    public static function options(): array
    {
        return [
            self::Percentage->value => self::Percentage->label(),
            self::Flat->value => self::Flat->label(),
        ];
    }
}
