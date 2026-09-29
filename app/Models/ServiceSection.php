<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class ServiceSection extends Model
{
    public const TYPES = ['features', 'benefits', 'process', 'technologies', 'use_cases', 'custom'];

    protected $fillable = ['service_id', 'type', 'title', 'subtitle', 'content', 'settings', 'is_active', 'sort_order'];

    protected function casts(): array
    {
        return ['settings' => 'array', 'is_active' => 'boolean', 'sort_order' => 'integer'];
    }

    public function service(): BelongsTo
    {
        return $this->belongsTo(Service::class);
    }

    public function items(): HasMany
    {
        return $this->hasMany(ServiceSectionItem::class)->orderBy('sort_order')->orderBy('id');
    }

    public function scopeActive(Builder $query): Builder
    {
        return $query->where('is_active', true);
    }
}
