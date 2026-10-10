<?php

namespace App\Enums;

enum LeadLostReason: string
{
    case Budget = 'Budget';
    case NoResponse = 'No Response';
    case Competitor = 'Competitor';
    case NotRequired = 'Not Required';
    case Timing = 'Timing';
    case Other = 'Other';

    public static function values(): array
    {
        return array_column(self::cases(), 'value');
    }
}
