<?php

namespace App\Enums;

enum PromotionCampaignType: string
{
    case Festival = 'festival';
    case Strategic = 'strategic';

    public function label(): string
    {
        return match ($this) {
            self::Festival => 'Festival',
            self::Strategic => 'Strategic',
        };
    }

    /**
     * @return array<string, string>
     */
    public static function options(): array
    {
        return [
            self::Festival->value => self::Festival->label(),
            self::Strategic->value => self::Strategic->label(),
        ];
    }
}
