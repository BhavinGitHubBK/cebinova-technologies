<?php

namespace App\Models;

use App\Enums\LeadStatus;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class Lead extends Model
{
    /** @use HasFactory<\Database\Factories\LeadFactory> */
    use HasFactory, SoftDeletes;

    public const STATUS_NEW = LeadStatus::New->value;

    public const STATUSES = [
        LeadStatus::New->value,
        LeadStatus::Contacted->value,
        LeadStatus::Qualified->value,
        LeadStatus::ProposalSent->value,
        LeadStatus::Won->value,
        LeadStatus::Lost->value,
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
        'service_id',
        'service_snapshot',
        'package_category',
        'marketing_package_id',
        'plan_duration',
        'marketing_plan_id',
        'selected_price',
        'marketing_snapshot',
        'pricing_package_id',
        'pricing_option_ids',
        'estimated_total',
        'pricing_snapshot',
        'city',
        'budget',
        'message',
        'notes',
        'free_consultation',
        'source',
        'page_url',
        'status',
        'follow_up_at',
        'contacted_at',
        'qualified_at',
        'proposal_sent_at',
        'won_at',
        'lost_at',
        'assigned_to',
    ];

    protected function casts(): array
    {
        return [
            'free_consultation' => 'boolean',
            'pricing_option_ids' => 'array',
            'estimated_total' => 'decimal:2',
            'pricing_snapshot' => 'array',
            'marketing_snapshot' => 'array',
            'service_snapshot' => 'array',
            'follow_up_at' => 'datetime',
            'contacted_at' => 'datetime',
            'qualified_at' => 'datetime',
            'proposal_sent_at' => 'datetime',
            'won_at' => 'datetime',
            'lost_at' => 'datetime',
        ];
    }

    public function assignee(): BelongsTo
    {
        return $this->belongsTo(User::class, 'assigned_to');
    }

    public function interestedService(): BelongsTo
    {
        return $this->belongsTo(Service::class, 'service_id');
    }
}
