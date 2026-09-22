<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class Lead extends Model
{
    /** @use HasFactory<\Database\Factories\LeadFactory> */
    use HasFactory, SoftDeletes;

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
        'notes',
        'free_consultation',
        'source',
        'status',
        'follow_up_at',
        'assigned_to',
    ];

    protected function casts(): array
    {
        return [
            'free_consultation' => 'boolean',
            'follow_up_at' => 'datetime',
        ];
    }

    public function assignee(): BelongsTo
    {
        return $this->belongsTo(User::class, 'assigned_to');
    }
}
