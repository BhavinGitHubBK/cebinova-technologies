<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class AnalyticsVisit extends Model
{
    protected $fillable = [
        'visitor_id',
        'started_at',
        'last_seen_at',
        'landing_path',
        'exit_path',
        'referrer_domain',
        'referrer_url',
    ];

    protected function casts(): array
    {
        return [
            'started_at' => 'datetime',
            'last_seen_at' => 'datetime',
        ];
    }

    public function visitor(): BelongsTo
    {
        return $this->belongsTo(AnalyticsVisitor::class, 'visitor_id');
    }

    public function pageviews(): HasMany
    {
        return $this->hasMany(AnalyticsPageview::class, 'visit_id');
    }

    public function cameFrom(): string
    {
        return $this->referrer_domain ?: 'Direct';
    }
}
