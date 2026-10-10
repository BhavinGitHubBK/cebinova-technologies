<?php

namespace App\Enums;

enum LeadPriority: string
{
    case Low = 'Low';
    case Medium = 'Medium';
    case High = 'High';

    public static function values(): array
    {
        return array_column(self::cases(), 'value');
    }
}
