<?php

namespace App\Enums;

enum PromotionPopupDesign: string
{
    case Split = 'split';
    case Night = 'night';
    case Editorial = 'editorial';
    case Rail = 'rail';
    case Horizon = 'horizon';

    public function label(): string
    {
        return match ($this) {
            self::Split => 'Split',
            self::Night => 'Night',
            self::Editorial => 'Editorial',
            self::Rail => 'Gold rail',
            self::Horizon => 'Horizon',
        };
    }

    public function detail(): string
    {
        return match ($this) {
            self::Split => 'Light card. Offer on the left, timer on the right.',
            self::Night => 'Dark navy card with gold type.',
            self::Editorial => 'Centered card with a large discount.',
            self::Rail => 'Gold panel carries the discount beside the offer.',
            self::Horizon => 'Navy header over a white offer panel.',
        };
    }

    /**
     * @return array<string, array{label: string, detail: string}>
     */
    public static function options(): array
    {
        $options = [];
        foreach (self::cases() as $case) {
            $options[$case->value] = [
                'label' => $case->label(),
                'detail' => $case->detail(),
            ];
        }

        return $options;
    }
}
