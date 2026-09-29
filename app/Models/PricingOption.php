<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class PricingOption extends Model
{
    use SoftDeletes;

    public const TYPES = ['addon' => 'Add-ons', 'domain' => 'Domain Plans', 'hosting' => 'Hosting Plans'];
    public const BILLING_PERIODS = ['one_time' => 'One time', 'monthly' => 'Monthly', 'yearly' => 'Yearly', 'custom' => 'Custom'];

    protected $fillable = ['type', 'name', 'slug', 'description', 'price', 'billing_period', 'metadata', 'is_recommended', 'is_active', 'sort_order'];

    protected function casts(): array
    {
        return ['price' => 'decimal:2', 'metadata' => 'array', 'is_recommended' => 'boolean', 'is_active' => 'boolean', 'sort_order' => 'integer'];
    }

    public function scopeActive(Builder $query): Builder
    {
        return $query->where('is_active', true);
    }

    public function scopeOrdered(Builder $query): Builder
    {
        return $query->orderBy('sort_order')->orderBy('id');
    }
}
