<?php

namespace App\Models;

use App\Enums\LeadTaskStatus;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class LeadTask extends Model
{
    protected $fillable = [
        'lead_id',
        'assigned_to',
        'created_by',
        'title',
        'description',
        'due_at',
        'priority',
        'status',
        'completed_at',
    ];

    protected function casts(): array
    {
        return [
            'due_at' => 'datetime',
            'completed_at' => 'datetime',
        ];
    }

    public function lead(): BelongsTo
    {
        return $this->belongsTo(Lead::class);
    }

    public function assignee(): BelongsTo
    {
        return $this->belongsTo(User::class, 'assigned_to');
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function isOverdue(): bool
    {
        return $this->status === LeadTaskStatus::Pending->value && $this->due_at?->isPast();
    }
}
