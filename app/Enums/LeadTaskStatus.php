<?php

namespace App\Enums;

enum LeadTaskStatus: string
{
    case Pending = 'Pending';
    case Completed = 'Completed';
    case Cancelled = 'Cancelled';

    public static function values(): array
    {
        return array_column(self::cases(), 'value');
    }
}
