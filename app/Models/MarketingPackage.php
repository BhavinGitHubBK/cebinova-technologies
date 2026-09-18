<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class MarketingPackage extends Model
{
    protected $fillable = [
        'key',
        'title',
        'heading',
        'subheading',
        'teaser',
        'best_if',
        'service',
        'badge',
        'cta',
        'secondary_cta',
        'why',
        'includes',
        'sort_order',
        'is_active',
    ];

    protected function casts(): array
    {
        return [
            'includes' => 'array',
            'is_active' => 'boolean',
            'sort_order' => 'integer',
        ];
    }

    public function plans(): HasMany
    {
        return $this->hasMany(MarketingPlan::class)->orderBy('sort_order');
    }

    public function scopeActive(Builder $query): Builder
    {
        return $query->where('is_active', true);
    }
}
