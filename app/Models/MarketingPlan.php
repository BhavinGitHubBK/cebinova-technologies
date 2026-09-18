<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class MarketingPlan extends Model
{
    protected $fillable = [
        'marketing_package_id',
        'key',
        'label',
        'duration',
        'price',
        'period',
        'badge',
        'cta',
        'includes',
        'monthly_pace',
        'sort_order',
        'is_active',
    ];

    protected function casts(): array
    {
        return [
            'includes' => 'array',
            'monthly_pace' => 'array',
            'is_active' => 'boolean',
            'price' => 'integer',
            'sort_order' => 'integer',
        ];
    }

    public function package(): BelongsTo
    {
        return $this->belongsTo(MarketingPackage::class, 'marketing_package_id');
    }

    public function scopeActive(Builder $query): Builder
    {
        return $query->where('is_active', true);
    }
}
