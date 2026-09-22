<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Package extends Model
{
    use SoftDeletes;

    public const CATEGORIES = [
        'marketing_regular' => 'Regular Marketing',
        'marketing_festival' => 'Festival Marketing',
        'marketing_growth' => 'Complete Growth',
        'website' => 'Website Development',
        'app' => 'App Development',
        'other' => 'Other',
    ];

    protected $fillable = [
        'category',
        'name',
        'slug',
        'key',
        'heading',
        'short_description',
        'subheading',
        'teaser',
        'best_if',
        'service_label',
        'badge',
        'cta_label',
        'secondary_cta',
        'why',
        'includes',
        'is_highlighted',
        'is_active',
        'sort_order',
        'active_from',
        'active_until',
        'seo_title',
        'seo_description',
    ];

    protected function casts(): array
    {
        return [
            'includes' => 'array',
            'is_highlighted' => 'boolean',
            'is_active' => 'boolean',
            'sort_order' => 'integer',
            'active_from' => 'date',
            'active_until' => 'date',
        ];
    }

    public function plans(): HasMany
    {
        return $this->hasMany(PackagePlan::class)->orderBy('sort_order');
    }

    public function scopeActive(Builder $query): Builder
    {
        return $query->where('is_active', true)
            ->where(function (Builder $q) {
                $q->whereNull('active_from')->orWhereDate('active_from', '<=', now());
            })
            ->where(function (Builder $q) {
                $q->whereNull('active_until')->orWhereDate('active_until', '>=', now());
            });
    }

    public function scopeMarketing(Builder $query): Builder
    {
        return $query->whereIn('category', [
            'marketing_regular',
            'marketing_festival',
            'marketing_growth',
        ]);
    }

    public function scopeWebsite(Builder $query): Builder
    {
        return $query->where('category', 'website');
    }
}
