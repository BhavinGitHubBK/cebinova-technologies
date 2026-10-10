<?php

namespace App\Models;

use App\Enums\PromotionDiscountType;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class PromotionCoupon extends Model
{
    protected $fillable = [
        'promotion_id', 'code', 'name', 'discount_type', 'discount_value', 'starts_at', 'ends_at',
        'minimum_amount', 'maximum_discount_amount', 'usage_limit', 'usage_limit_per_customer',
        'is_active', 'created_by',
    ];

    protected function casts(): array
    {
        return [
            'discount_type' => PromotionDiscountType::class,
            'discount_value' => 'decimal:2',
            'starts_at' => 'datetime',
            'ends_at' => 'datetime',
            'minimum_amount' => 'decimal:2',
            'maximum_discount_amount' => 'decimal:2',
            'usage_limit' => 'integer',
            'usage_limit_per_customer' => 'integer',
            'is_active' => 'boolean',
        ];
    }

    public function campaign(): BelongsTo
    {
        return $this->belongsTo(PromotionCampaign::class, 'promotion_id');
    }

    public function redemptions(): HasMany
    {
        return $this->hasMany(PromotionRedemption::class, 'coupon_id');
    }

    public static function normalize(string $code): string
    {
        return strtoupper(preg_replace('/\s+/', '', trim($code)) ?? '');
    }
}
