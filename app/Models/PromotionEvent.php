<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PromotionEvent extends Model
{
    public const UPDATED_AT = null;

    public const EVENTS = [
        'promotion_impression',
        'promotion_popup_view',
        'promotion_popup_close',
        'promotion_cta_click',
        'coupon_attempt',
        'coupon_applied',
        'coupon_rejected',
        'offer_page_view',
        'promotion_conversion',
    ];

    protected $fillable = ['event', 'promotion_id', 'coupon_id', 'lead_id', 'visitor_uuid', 'path', 'meta'];

    protected function casts(): array
    {
        return [
            'meta' => 'array',
            'created_at' => 'datetime',
        ];
    }

    public function campaign(): BelongsTo
    {
        return $this->belongsTo(PromotionCampaign::class, 'promotion_id');
    }
}
