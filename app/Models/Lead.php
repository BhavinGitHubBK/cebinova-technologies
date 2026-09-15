<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Lead extends Model
{
    public const STATUS_NEW = 'New';

    public const STATUSES = [
        'New',
        'Contacted',
        'Follow-up',
        'Converted',
        'Lost',
    ];

    protected $attributes = [
        'status' => self::STATUS_NEW,
    ];

    protected $fillable = [
        'name',
        'business_name',
        'phone',
        'whatsapp',
        'email',
        'business_type',
        'service',
        'package_category',
        'plan_duration',
        'selected_price',
        'city',
        'budget',
        'message',
        'free_consultation',
        'source',
        'status',
    ];

    protected function casts(): array
    {
        return [
            'free_consultation' => 'boolean',
        ];
    }
}
