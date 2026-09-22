<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PackagePlan extends Model
{
    protected $fillable = [
        'package_id',
        'key',
        'label',
        'duration',
        'billing_duration',
        'original_price',
        'price',
        'discount_percent',
        'period',
        'badge',
        'cta',
        'note',
        'features',
        'monthly_pace',
        'is_active',
        'sort_order',
    ];

    protected function casts(): array
    {
        return [
            'features' => 'array',
            'monthly_pace' => 'array',
            'is_active' => 'boolean',
            'original_price' => 'integer',
            'price' => 'integer',
            'discount_percent' => 'integer',
            'sort_order' => 'integer',
        ];
    }

    public function package(): BelongsTo
    {
        return $this->belongsTo(Package::class);
    }

    public function scopeActive(Builder $query): Builder
    {
        return $query->where('is_active', true);
    }
}
