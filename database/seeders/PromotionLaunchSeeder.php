<?php

namespace Database\Seeders;

use App\Models\PromotionCampaign;
use App\Models\PromotionCoupon;
use App\Services\Promotions\PromotionEngine;
use Illuminate\Database\Seeder;

/**
 * Creates the first two offers only when none exist.
 * Dates and amounts stay editable in Admin → Promotions.
 */
class PromotionLaunchSeeder extends Seeder
{
    public function run(): void
    {
        if (PromotionCampaign::query()->exists()) {
            return;
        }

        $festival = PromotionCampaign::query()->create([
            'name' => 'Diwali Special',
            'slug' => 'diwali-special',
            'campaign_type' => 'festival',
            'festival_name' => 'Diwali',
            'headline' => 'Diwali Special',
            'subheadline' => '20% off website pricing and marketing packages',
            'description' => 'Limited festival price for website packages and marketing packages. The regular price returns when this offer ends.',
            'discount_type' => 'percentage',
            'discount_value' => 20,
            'starts_at' => '2026-10-07 00:00:00',
            'ends_at' => '2026-11-15 23:59:00',
            'publication_status' => 'published',
            'coupon_required' => false,
            'show_popup' => true,
            'show_announcement_bar' => true,
            'show_pricing_badge' => true,
            'show_countdown' => true,
            'show_before_start' => true,
            'preview_days' => 14,
            'popup_delay_seconds' => 3,
            'popup_frequency' => 'session',
            'popup_cooldown_hours' => 24,
            'popup_repeat_minutes' => 1,
            'announcement_text' => 'Diwali Special — 20% off website pricing and marketing packages',
            'announcement_dismissible' => false,
            'badge_text' => 'Diwali Special',
            'cta_text' => 'Explore Offer',
        ]);
        $festival->targets()->create(['mode' => 'include', 'target_type' => 'all_website_packages']);
        $festival->targets()->create(['mode' => 'include', 'target_type' => 'all_marketing_packages']);
        PromotionCoupon::query()->create([
            'promotion_id' => $festival->id,
            'code' => 'DIWALI20',
            'name' => 'Diwali 20',
            'is_active' => true,
        ]);

        $strategic = PromotionCampaign::query()->create([
            'name' => 'New Business Offer',
            'slug' => 'new-business-offer',
            'campaign_type' => 'strategic',
            'headline' => 'New Business Offer',
            'subheadline' => '10% off website pricing and marketing packages',
            'description' => 'Discount for new business enquiries. It starts after the festival offer ends.',
            'discount_type' => 'percentage',
            'discount_value' => 10,
            'starts_at' => '2026-11-16 00:00:00',
            'ends_at' => '2026-12-31 23:59:00',
            'publication_status' => 'published',
            'coupon_required' => true,
            'show_popup' => true,
            'show_announcement_bar' => true,
            'show_pricing_badge' => true,
            'show_countdown' => true,
            'show_before_start' => true,
            'preview_days' => 45,
            'popup_delay_seconds' => 4,
            'popup_frequency' => 'session',
            'popup_repeat_minutes' => 1,
            'announcement_text' => 'New Business Offer — 10% off with code STARTUP10',
            'announcement_dismissible' => false,
            'badge_text' => 'New Business Offer',
            'cta_text' => 'View Offer',
        ]);
        $strategic->targets()->create(['mode' => 'include', 'target_type' => 'all_website_packages']);
        $strategic->targets()->create(['mode' => 'include', 'target_type' => 'all_marketing_packages']);
        PromotionCoupon::query()->create([
            'promotion_id' => $strategic->id,
            'code' => 'STARTUP10',
            'name' => 'New business 10',
            'is_active' => true,
        ]);

        app(PromotionEngine::class)->forgetCache();
    }
}
