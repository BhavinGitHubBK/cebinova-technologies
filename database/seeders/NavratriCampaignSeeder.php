<?php

namespace Database\Seeders;

use App\Models\PromotionCampaign;
use App\Models\PromotionCoupon;
use App\Services\Promotions\PromotionEngine;
use Illuminate\Database\Seeder;

/**
 * Adds the Navratri 2026 festival offer once. Safe to run again.
 * Dates and amounts stay editable in Admin → Promotions.
 */
class NavratriCampaignSeeder extends Seeder
{
    public function run(): void
    {
        if (PromotionCampaign::withTrashed()->where('slug', 'navratri-special')->exists()) {
            return;
        }

        $campaign = PromotionCampaign::query()->create([
            'name' => 'Navratri Special',
            'slug' => 'navratri-special',
            'campaign_type' => 'festival',
            'festival_name' => 'Navratri',
            'headline' => 'Navratri Special',
            'subheadline' => '20% off website pricing and marketing packages',
            'description' => 'Nine nights of festival savings on website packages and marketing packages. The regular price returns when this offer ends.',
            'discount_type' => 'percentage',
            'discount_value' => 20,
            'starts_at' => '2026-10-11 00:00:00',
            'ends_at' => '2026-10-20 23:59:00',
            'publication_status' => 'published',
            'coupon_required' => false,
            'show_popup' => true,
            'popup_design' => 'split',
            'show_announcement_bar' => true,
            'show_pricing_badge' => true,
            'show_countdown' => true,
            'show_before_start' => true,
            'preview_days' => 7,
            'popup_delay_seconds' => 3,
            'popup_frequency' => 'session',
            'popup_cooldown_hours' => 24,
            'popup_repeat_minutes' => 1,
            'announcement_text' => 'Navratri Special — 20% off website pricing and marketing packages',
            'announcement_dismissible' => false,
            'badge_text' => 'Navratri Special',
            'cta_text' => 'Explore Offer',
        ]);
        $campaign->targets()->create(['mode' => 'include', 'target_type' => 'all_website_packages']);
        $campaign->targets()->create(['mode' => 'include', 'target_type' => 'all_marketing_packages']);
        PromotionCoupon::query()->create([
            'promotion_id' => $campaign->id,
            'code' => 'NAVRATRI20',
            'name' => 'Navratri 20',
            'is_active' => true,
        ]);

        app(PromotionEngine::class)->forgetCache();
    }
}
