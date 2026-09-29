<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class MarketingPlanDeliverable extends Model
{
    protected $fillable = ['group', 'name', 'quantity', 'unit', 'is_active', 'sort_order'];

    protected function casts(): array
    {
        return ['quantity' => 'decimal:2', 'is_active' => 'boolean', 'sort_order' => 'integer'];
    }

    public function plan(): BelongsTo
    {
        return $this->belongsTo(PackagePlan::class, 'package_plan_id');
    }

    public function scopeActive(Builder $query): Builder
    {
        return $query->where('is_active', true)->orderBy('sort_order')->orderBy('id');
    }
}
