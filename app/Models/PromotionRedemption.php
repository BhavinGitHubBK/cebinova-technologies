<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PromotionRedemption extends Model
{
    protected $fillable = [
        'promotion_id', 'coupon_id', 'lead_id', 'quotation_id', 'invoice_id', 'visitor_uuid',
        'customer_key', 'original_amount', 'discount_amount', 'final_amount', 'snapshot', 'redeemed_at',
    ];

    protected function casts(): array
    {
        return [
            'original_amount' => 'decimal:2',
            'discount_amount' => 'decimal:2',
            'final_amount' => 'decimal:2',
            'snapshot' => 'array',
            'redeemed_at' => 'datetime',
        ];
    }

    public function campaign(): BelongsTo
    {
        return $this->belongsTo(PromotionCampaign::class, 'promotion_id');
    }

    public function coupon(): BelongsTo
    {
        return $this->belongsTo(PromotionCoupon::class, 'coupon_id');
    }

    public function lead(): BelongsTo
    {
        return $this->belongsTo(Lead::class);
    }
}
