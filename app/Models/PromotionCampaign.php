<?php

namespace App\Models;

use App\Enums\PromotionCampaignType;
use App\Enums\PromotionDiscountType;
use App\Enums\PromotionPopupDesign;
use App\Enums\PromotionPublication;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Carbon;

class PromotionCampaign extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'name', 'slug', 'campaign_type', 'festival_name', 'headline', 'subheadline', 'description',
        'discount_type', 'discount_value',
        'starts_at', 'ends_at', 'publication_status', 'coupon_required',
        'show_popup', 'popup_design', 'show_announcement_bar', 'show_pricing_badge', 'show_countdown',
        'show_before_start', 'preview_days', 'popup_delay_seconds', 'popup_scroll_percent', 'popup_exit_intent',
        'popup_frequency', 'popup_cooldown_hours', 'popup_repeat_minutes', 'announcement_text', 'announcement_dismissible',
        'badge_text', 'cta_text', 'cta_url', 'terms', 'seo_title', 'seo_description', 'created_by', 'updated_by',
    ];

    protected function casts(): array
    {
        return [
            'campaign_type' => PromotionCampaignType::class,
            'discount_type' => PromotionDiscountType::class,
            'publication_status' => PromotionPublication::class,
            'discount_value' => 'decimal:2',
            'starts_at' => 'datetime',
            'ends_at' => 'datetime',
            'coupon_required' => 'boolean',
            'show_popup' => 'boolean',
            'popup_design' => PromotionPopupDesign::class,
            'show_announcement_bar' => 'boolean',
            'show_pricing_badge' => 'boolean',
            'show_countdown' => 'boolean',
            'show_before_start' => 'boolean',
            'preview_days' => 'integer',
            'popup_delay_seconds' => 'integer',
            'popup_scroll_percent' => 'integer',
            'popup_exit_intent' => 'boolean',
            'popup_cooldown_hours' => 'integer',
            'popup_repeat_minutes' => 'integer',
            'announcement_dismissible' => 'boolean',
        ];
    }

    public function targets(): HasMany
    {
        return $this->hasMany(PromotionTarget::class, 'promotion_id');
    }

    public function coupons(): HasMany
    {
        return $this->hasMany(PromotionCoupon::class, 'promotion_id');
    }

    public function redemptions(): HasMany
    {
        return $this->hasMany(PromotionRedemption::class, 'promotion_id');
    }

    public function events(): HasMany
    {
        return $this->hasMany(PromotionEvent::class, 'promotion_id');
    }

    public function author(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function state(?Carbon $at = null): string
    {
        $at ??= now();

        if ($this->publication_status === PromotionPublication::Draft) {
            return 'draft';
        }

        if ($this->publication_status === PromotionPublication::Disabled) {
            return 'disabled';
        }

        if ($this->starts_at && $at->lt($this->starts_at)) {
            return 'scheduled';
        }

        if ($this->ends_at && $at->gt($this->ends_at)) {
            return 'expired';
        }

        return 'active';
    }

    public function isLive(?Carbon $at = null): bool
    {
        return $this->state($at) === 'active';
    }

    public function discountLabel(): string
    {
        if ($this->discount_type === PromotionDiscountType::Percentage) {
            $value = rtrim(rtrim(number_format((float) $this->discount_value, 2, '.', ''), '0'), '.');

            return $value.'%';
        }

        return '₹'.number_format((float) $this->discount_value, 0, '.', ',');
    }
}
