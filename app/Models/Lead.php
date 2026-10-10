<?php

namespace App\Models;

use Database\Factories\LeadFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Lead extends Model
{
    /** @use HasFactory<LeadFactory> */
    use HasFactory, SoftDeletes;

    public const STATUS_NEW = 'New';

    public const STATUSES = [
        'New',
        'Contacted',
        'Qualified',
        'Proposal Sent',
        'Won',
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
        'source_page',
        'status',
        'follow_up_at',
        'assigned_to',
        'priority',
        'estimated_value',
        'lost_reason',
        'demo_id',
        'service_id',
        'solution_id',
        'industry_id',
        'solution_snapshot',
        'industry_snapshot',
        'service_snapshot',
        'marketing_package_id',
        'marketing_plan_id',
        'marketing_snapshot',
        'pricing_package_id',
        'pricing_option_ids',
        'estimated_total',
        'pricing_snapshot',
        'promotion_id',
        'coupon_id',
        'promotion_snapshot',
        'page_url',
    ];

    protected function casts(): array
    {
        return [
            'free_consultation' => 'boolean',
            'follow_up_at' => 'datetime',
            'estimated_value' => 'decimal:2',
            'solution_snapshot' => 'array',
            'industry_snapshot' => 'array',
            'service_snapshot' => 'array',
            'marketing_snapshot' => 'array',
            'pricing_snapshot' => 'array',
            'promotion_snapshot' => 'array',
            'pricing_option_ids' => 'array',
        ];
    }

    public function assignee(): BelongsTo
    {
        return $this->belongsTo(User::class, 'assigned_to');
    }

    public function demo(): BelongsTo
    {
        return $this->belongsTo(Demo::class)->withTrashed();
    }

    public function tasks(): HasMany
    {
        return $this->hasMany(LeadTask::class);
    }
}
