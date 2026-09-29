<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PackageFeature extends Model
{
    protected $fillable = ['name', 'display_value', 'is_included', 'is_active', 'sort_order'];

    protected function casts(): array
    {
        return ['is_included' => 'boolean', 'is_active' => 'boolean', 'sort_order' => 'integer'];
    }

    public function package(): BelongsTo
    {
        return $this->belongsTo(Package::class);
    }

    public function scopeActive(Builder $query): Builder
    {
        return $query->where('is_active', true)->orderBy('sort_order')->orderBy('id');
    }
}
