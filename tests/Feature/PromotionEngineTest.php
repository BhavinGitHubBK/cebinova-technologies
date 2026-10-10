<?php

namespace Tests\Feature;

use App\Enums\UserRole;
use App\Models\ActivityLog;
use App\Models\Lead;
use App\Models\Package;
use App\Models\PricingOption;
use App\Models\PromotionCampaign;
use App\Models\PromotionCoupon;
use App\Models\PromotionEvent;
use App\Models\PromotionRedemption;
use App\Models\User;
use App\Services\PricingCalculator;
use App\Services\Promotions\PopupSchedule;
use App\Services\Promotions\PromotionEngine;
use App\Support\Promotions\Money;
use App\Support\WebsitePackages;
use Database\Seeders\PackageSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Cache;
use Illuminate\Validation\ValidationException;
use Tests\TestCase;

class PromotionEngineTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(PackageSeeder::class);
    }

    protected function tearDown(): void
    {
        Carbon::setTestNow();
        parent::tearDown();
    }

    public function test_draft_scheduled_expired_and_disabled_campaigns_do_not_apply(): void
    {
        $package = $this->website('business');
        $this->offer(['publication_status' => 'draft']);
        $this->assertSame(0, $this->discountPaise($package->id));

        $this->offer(['publication_status' => 'published', 'starts_at' => now()->addDay(), 'ends_at' => now()->addDays(2), 'slug' => 'future']);
        $this->assertSame(0, $this->discountPaise($package->id));

        Carbon::setTestNow(now()->addDays(3));
        $this->offer(['publication_status' => 'published', 'starts_at' => now()->subDays(3), 'ends_at' => now()->subMinute(), 'slug' => 'old']);
        $this->assertSame(0, $this->discountPaise($package->id));

        $this->offer(['publication_status' => 'disabled', 'slug' => 'off']);
        $this->assertSame(0, $this->discountPaise($package->id));
    }

    public function test_campaign_activates_at_start_and_expires_at_end(): void
    {
        $package = $this->website('business');
        $price = (int) $package->plans->first()->price;
        $campaign = $this->offer([
            'starts_at' => Carbon::parse('2026-10-20 00:00:00'),
            'ends_at' => Carbon::parse('2026-10-31 23:59:00'),
        ]);

        Carbon::setTestNow('2026-10-19 23:59:00');
        $this->assertSame('scheduled', $campaign->fresh()->state());
        $this->assertSame(0, $this->discountPaise($package->id));

        Carbon::setTestNow('2026-10-20 00:00:00');
        $this->assertSame('active', $campaign->fresh()->state());
        $this->assertSame(Money::percent(Money::paise($price), '20'), $this->discountPaise($package->id));

        Carbon::setTestNow('2026-10-31 23:59:01');
        $this->assertSame('expired', $campaign->fresh()->state());
        $this->assertSame(0, $this->discountPaise($package->id));
    }

    public function test_percentage_flat_cap_minimum_and_non_negative_prices(): void
    {
        $package = $this->website('business');
        $price = (int) $package->plans->first()->price;
        $this->offer(['discount_type' => 'percentage', 'discount_value' => 20]);
        $this->assertSame(Money::percent(Money::paise($price), '20'), $this->discountPaise($package->id));

        PromotionCampaign::query()->delete();
        $this->offer(['discount_type' => 'flat', 'discount_value' => 5000, 'slug' => 'flat']);
        $this->assertSame(500000, $this->discountPaise($package->id));

        PromotionCampaign::query()->forceDelete();
        $this->offer(['discount_type' => 'flat', 'discount_value' => 5000, 'slug' => 'clamp']);
        $quote = app(PromotionEngine::class)->quote([['type' => 'website_package', 'id' => $package->id, 'amount' => 1000]]);
        $this->assertSame('0.00', $quote['discounted_subtotal']);
        $this->assertSame('1000.00', $quote['discount_total']);
        $this->assertSame((string) $package->fresh()->plans->first()->price, (string) $price);
    }

    public function test_targeting_covers_all_website_or_all_marketing_packages(): void
    {
        $business = $this->website('business');
        $this->offer([], [['mode' => 'include', 'target_type' => 'all_website_packages']]);
        $this->assertGreaterThan(0, $this->discountPaise($business->id));

        $growth = Package::query()->where('key', 'growth')->firstOrFail();
        $yearly = $growth->plans()->where('label', 'Yearly')->firstOrFail();
        $engine = app(PromotionEngine::class);
        $this->assertSame('0.00', $engine->quote([['type' => 'marketing_plan', 'id' => $yearly->id, 'package_id' => $growth->id, 'amount' => $yearly->price]])['discount_total']);

        PromotionCampaign::query()->delete();
        $this->offer(['slug' => 'market'], [['mode' => 'include', 'target_type' => 'all_marketing_packages']]);
        $this->assertSame(0, $this->discountPaise($business->id));
        $this->assertGreaterThan(0, Money::paise($engine->quote([['type' => 'marketing_plan', 'id' => $yearly->id, 'package_id' => $growth->id, 'amount' => $yearly->price]])['discount_total']));
    }

    public function test_coupon_rules_case_limits_and_stacking(): void
    {
        $package = $this->website('business');
        $price = (int) $package->plans->first()->price;
        $campaign = $this->offer(['coupon_required' => true]);
        $coupon = PromotionCoupon::query()->create(['promotion_id' => $campaign->id, 'code' => 'DIWALI20', 'is_active' => true]);

        $this->assertSame(0, $this->discountPaise($package->id));
        $applied = app(PromotionEngine::class)->quote($this->line($package), 'diwali20');
        $this->assertSame('Coupon applied successfully.', $applied['message']);
        $this->assertSame(Money::percent(Money::paise($price), '20'), Money::paise($applied['discount_total']));

        $this->expectException(ValidationException::class);
        app(PromotionEngine::class)->quote($this->line($package), 'NOPE');
    }

    public function test_expired_and_disabled_coupons_are_rejected(): void
    {
        $package = $this->website('business');
        $campaign = $this->offer();
        PromotionCoupon::query()->create([
            'promotion_id' => $campaign->id,
            'code' => 'OLD20',
            'is_active' => true,
            'ends_at' => now()->subMinute(),
        ]);
        try {
            app(PromotionEngine::class)->quote($this->line($package), 'old20');
            $this->fail('Expired coupon was accepted.');
        } catch (ValidationException $exception) {
            $this->assertSame('Coupon has expired.', $exception->errors()['coupon_code'][0]);
        }

        PromotionCoupon::query()->create(['promotion_id' => $campaign->id, 'code' => 'OFF20', 'is_active' => false]);
        try {
            app(PromotionEngine::class)->quote($this->line($package), 'off20');
            $this->fail('Disabled coupon was accepted.');
        } catch (ValidationException $exception) {
            $this->assertSame('Invalid coupon code.', $exception->errors()['coupon_code'][0]);
        }
    }

    public function test_usage_limit_is_enforced_for_a_second_redemption(): void
    {
        $package = $this->website('business');
        $campaign = $this->offer(['coupon_required' => true]);
        PromotionCoupon::query()->create([
            'promotion_id' => $campaign->id,
            'code' => 'ONCE',
            'is_active' => true,
            'usage_limit' => 1,
        ]);

        $this->enquiry($package->id, 'once');
        $this->assertSame(1, PromotionRedemption::query()->count());

        $this->postJson(route('contact.store'), $this->enquiryPayload($package->id, 'once', '9888888888'))
            ->assertStatus(422)
            ->assertJsonValidationErrors('coupon_code');
        $this->assertSame(1, PromotionRedemption::query()->count());
    }

    public function test_a_second_coupon_cannot_combine_with_the_live_offer(): void
    {
        $package = $this->website('business');
        $campaign = $this->offer();
        PromotionCoupon::query()->create([
            'promotion_id' => $campaign->id,
            'code' => 'EXTRA10',
            'discount_type' => 'percentage',
            'discount_value' => 10,
            'is_active' => true,
        ]);

        try {
            app(PromotionEngine::class)->quote($this->line($package), 'EXTRA10');
            $this->fail('Stacked coupon was accepted.');
        } catch (ValidationException $exception) {
            $this->assertStringContainsString('cannot be combined', $exception->errors()['coupon_code'][0]);
        }
    }

    public function test_multiple_campaigns_resolve_by_savings_then_id(): void
    {
        $package = $this->website('business');
        $low = $this->offer(['name' => 'Low', 'slug' => 'low', 'discount_value' => 5]);
        $high = $this->offer(['name' => 'High', 'slug' => 'high', 'discount_value' => 9]);
        $quote = app(PromotionEngine::class)->quote($this->line($package));
        $this->assertSame($high->id, $quote['promotion_id']);

        $high->update(['discount_value' => 5]);
        $quote = app(PromotionEngine::class)->quote($this->line($package));
        $this->assertSame($low->id, $quote['promotion_id']);
    }

    public function test_shorter_festival_inside_a_longer_one_is_featured_until_it_ends(): void
    {
        $package = $this->website('business');
        $diwali = $this->offer(['name' => 'Diwali Special', 'slug' => 'diwali', 'show_popup' => true, 'ends_at' => now()->addDays(30)]);
        $navratri = $this->offer(['name' => 'Navratri Special', 'slug' => 'navratri', 'show_popup' => true, 'ends_at' => now()->addDays(5)]);
        $engine = app(PromotionEngine::class);

        $engine->forgetCache();
        $this->assertSame($navratri->id, $engine->surface()['campaign']['id']);
        $this->assertSame($navratri->id, $engine->quote($this->line($package))['promotion_id']);

        Carbon::setTestNow(now()->addDays(6));
        $engine->forgetCache();
        $this->assertSame($diwali->id, $engine->surface()['campaign']['id']);
        $this->assertSame($diwali->id, $engine->quote($this->line($package))['promotion_id']);
    }

    public function test_overlapping_festivals_are_all_offered_with_their_own_theme(): void
    {
        $diwali = $this->offer(['name' => 'Diwali Special', 'slug' => 'diwali', 'campaign_type' => 'festival', 'festival_name' => 'Diwali', 'show_popup' => true, 'ends_at' => now()->addDays(30)]);
        $navratri = $this->offer(['name' => 'Navratri Special', 'slug' => 'navratri', 'campaign_type' => 'festival', 'festival_name' => 'Navratri', 'show_popup' => true, 'ends_at' => now()->addDays(5)]);
        $this->offer(['name' => 'Badge only', 'slug' => 'badge-only', 'campaign_type' => 'strategic', 'ends_at' => now()->addDays(2)]);
        $engine = app(PromotionEngine::class);
        $engine->forgetCache();

        $offers = collect($engine->surface()['live_offers']);

        $this->assertSame([$navratri->id, $diwali->id], $offers->pluck('id')->all());
        $this->assertSame(['garba', 'crackers'], $offers->pluck('theme')->all());
    }

    public function test_pricing_calculator_applies_engine_and_rejects_tampered_prices(): void
    {
        $package = $this->website('business');
        $option = PricingOption::query()->where('slug', 'basic-hosting')->firstOrFail();
        $original = (int) $package->plans->first()->price;
        $this->offer();
        $result = app(PricingCalculator::class)->calculate($package->id, [$option->id]);
        $this->assertNotSame((float) ($original + (int) $option->price), $result['total']);
        $this->assertSame($original, (int) $package->fresh()->plans->first()->price);

        $this->postJson(route('contact.store'), [
            'name' => 'Offer Customer',
            'phone' => '9876501234',
            'service' => 'Business Solution',
            'source' => 'Website Contact',
            'pricing_package_id' => $package->id,
            'pricing_option_ids' => [$option->id],
            'plan_price' => '1',
            'campaign_id' => 999,
            'discount_amount' => '1',
        ])->assertOk();

        $lead = Lead::query()->firstOrFail();
        $this->assertSame($result['promotion']['discounted_subtotal'], Money::format(Money::paise($lead->estimated_total)));
        $this->assertSame($lead->promotion_snapshot['discount_total'], $lead->promotion_snapshot['discount_total']);
        $this->assertNotSame('1.00', $lead->estimated_total);

        $saved = $lead->promotion_snapshot;
        PromotionCampaign::query()->update(['discount_value' => 50]);
        $lead->refresh();
        $this->assertSame($saved, $lead->promotion_snapshot);
        $this->assertSame(1, PromotionEvent::query()->where('event', 'promotion_conversion')->count());
    }

    public function test_quote_endpoint_ignores_client_prices_and_uses_server_end_time(): void
    {
        $package = $this->website('professional');
        $campaign = $this->offer([], [['mode' => 'include', 'target_type' => 'all_website_packages']]);

        $this->postJson(route('promotions.quote'), [
            'pricing_package_id' => $package->id,
            'amount' => 1,
            'discount' => 99999,
            'ends_at' => now()->addYear()->toIso8601String(),
        ])->assertOk()
            ->assertJsonPath('ends_at', $campaign->ends_at->toIso8601String())
            ->assertJsonMissing(['amount' => 1]);

        $this->postJson(route('promotions.quote'), [
            'pricing_package_id' => 999999,
        ])->assertStatus(422);
    }

    public function test_surface_popup_upcoming_and_cooldown_rules(): void
    {
        $live = $this->offer(['show_popup' => true, 'show_announcement_bar' => true, 'slug' => 'live']);
        $soon = $this->offer([
            'slug' => 'soon',
            'starts_at' => now()->addDays(4),
            'ends_at' => now()->addDays(10),
        ]);

        $surface = app(PromotionEngine::class)->surface();
        $this->assertSame('active', $surface['mode']);
        $this->assertSame($live->id, $surface['campaign']['id']);
        $this->assertTrue($surface['campaign']['show_popup']);

        $live->update(['publication_status' => 'disabled']);
        Cache::forget(PromotionEngine::CACHE_KEY);
        $surface = app(PromotionEngine::class)->surface();
        $this->assertSame('none', $surface['mode']);
        $this->assertNull($surface['campaign']);
        $this->assertSame(0, $this->discountPaise($this->website('business')->id));

        $live->popup_repeat_minutes = 1;
        $this->assertFalse(PopupSchedule::shouldShow($live, ['at' => now()->toIso8601String()]));
        $this->assertTrue(PopupSchedule::shouldShow($live, ['at' => now()->subMinutes(2)->toIso8601String()]));
        $live->popup_repeat_minutes = 0;
        $this->assertFalse(PopupSchedule::shouldShow($live, ['at' => now()->subMinutes(2)->toIso8601String()]));

        $this->postJson(route('promotions.events'), ['event' => 'promotion_popup_close', 'promotion_id' => $soon->id])->assertOk();
        $this->assertDatabaseHas('promotion_events', ['event' => 'promotion_popup_close', 'promotion_id' => $soon->id]);
    }

    public function test_admin_permissions_activity_brochure_and_cache(): void
    {
        $viewer = User::factory()->create(['role' => UserRole::Viewer]);
        $editor = User::factory()->create(['role' => UserRole::Editor]);
        $campaign = $this->offer(['publication_status' => 'draft', 'slug' => 'draft-offer']);

        $this->actingAs($viewer)->post(route('admin.promotions.campaigns.publish', $campaign))->assertForbidden();
        $this->actingAs($editor)->post(route('admin.promotions.campaigns.publish', $campaign))->assertRedirect();
        $this->assertSame('published', $campaign->fresh()->publication_status->value);

        Cache::put(PromotionEngine::CACHE_KEY, ['valid_until' => now()->addHour()->toIso8601String(), 'payload' => ['mode' => 'none']], 60);
        $this->actingAs($editor)->put(route('admin.promotions.campaigns.update', $campaign), $this->formPayload($campaign, ['discount_value' => 15, 'popup_design' => 'night']))->assertRedirect();
        $this->assertSame('night', $campaign->fresh()->popup_design->value);
        $this->assertTrue(ActivityLog::query()->where('module', 'promotions')->where('description', 'Campaign updated')->exists());
        $this->assertFalse(Cache::has(PromotionEngine::CACHE_KEY));
    }

    public function test_offers_page_and_empty_state_keep_normal_pricing(): void
    {
        $this->get(route('pricing'))->assertOk();
        $this->get(route('offers.index'))->assertOk()->assertSee('no active offer', false);

        $campaign = $this->offer(['show_pricing_badge' => true, 'headline' => 'Diwali Special']);
        $this->get(route('offers.show', $campaign->slug))->assertOk()->assertSee('Diwali Special');
        $plans = WebsitePackages::plans();
        $this->assertNotNull(collect($plans)->firstWhere('key', 'business')['promotion'] ?? null);

        $campaign->update(['ends_at' => now()->subMinute()]);
        Cache::forget(PromotionEngine::CACHE_KEY);
        $this->get(route('offers.show', $campaign->slug))->assertOk()->assertSee('noindex', false)->assertSee('has ended');
        $this->assertNull(collect(WebsitePackages::plans())->firstWhere('key', 'business')['promotion'] ?? null);
    }

    public function test_offers_page_separates_running_and_upcoming_offers(): void
    {
        $this->offer(['name' => 'Navratri Special', 'slug' => 'navratri', 'festival_name' => 'Navratri', 'campaign_type' => 'festival']);
        $this->offer([
            'name' => 'New Business Offer', 'slug' => 'new-business', 'campaign_type' => 'strategic', 'discount_value' => 10,
            'starts_at' => now()->addDays(3), 'ends_at' => now()->addDays(20), 'show_before_start' => true, 'preview_days' => 7,
        ]);
        Cache::forget(PromotionEngine::CACHE_KEY);

        $this->get(route('offers.index'))
            ->assertOk()
            ->assertSeeInOrder(['1 offer is running now', 'Current offer', 'Navratri', 'Navratri Special', 'Website packages', 'Coming soon', 'Special offer', 'New Business Offer'])
            ->assertDontSee('no active offer');
    }

    /**
     * @param  array<string, mixed>  $overrides
     * @param  list<array<string, mixed>>  $targets
     */
    private function offer(array $overrides = [], array $targets = []): PromotionCampaign
    {
        $campaign = PromotionCampaign::query()->create(array_merge([
            'name' => 'Diwali Special',
            'slug' => 'diwali-'.str()->lower(str()->random(6)),
            'discount_type' => 'percentage',
            'discount_value' => 20,
            'starts_at' => now()->subHour(),
            'ends_at' => now()->addDay(),
            'publication_status' => 'published',
            'coupon_required' => false,
            'show_pricing_badge' => true,
        ], $overrides));

        $targets = $targets !== [] ? $targets : [['mode' => 'include', 'target_type' => 'all_website_packages']];
        foreach ($targets as $target) {
            $campaign->targets()->create($target);
        }

        return $campaign->fresh('targets');
    }

    private function website(string $key): Package
    {
        return Package::query()->where('key', $key)->with('plans')->firstOrFail();
    }

    /**
     * @return list<array<string, mixed>>
     */
    private function line(Package $package): array
    {
        return [[
            'type' => 'website_package',
            'id' => $package->id,
            'label' => $package->name,
            'amount' => $package->plans->first()->price,
        ]];
    }

    private function discountPaise(int $packageId): int
    {
        $package = Package::query()->with('plans')->findOrFail($packageId);
        $quote = app(PromotionEngine::class)->quote($this->line($package));

        return Money::paise($quote['discount_total']);
    }

    private function enquiry(int $packageId, string $coupon): void
    {
        $this->postJson(route('contact.store'), $this->enquiryPayload($packageId, $coupon, '9876501234'))->assertOk();
    }

    /**
     * @return array<string, mixed>
     */
    private function enquiryPayload(int $packageId, string $coupon, string $phone): array
    {
        return [
            'name' => 'Coupon Customer',
            'phone' => $phone,
            'email' => 'offer@example.com',
            'service' => 'Business Solution',
            'source' => 'Website Contact',
            'pricing_package_id' => $packageId,
            'coupon_code' => $coupon,
        ];
    }

    /**
     * @param  array<string, mixed>  $extra
     * @return array<string, mixed>
     */
    private function formPayload(PromotionCampaign $campaign, array $extra = []): array
    {
        return array_merge([
            'name' => $campaign->name,
            'slug' => $campaign->slug,
            'campaign_type' => 'festival',
            'discount_type' => 'percentage',
            'discount_value' => 20,
            'starts_at' => now()->subHour()->format('Y-m-d\TH:i'),
            'ends_at' => now()->addDay()->format('Y-m-d\TH:i'),
            'publication_status' => 'draft',
            'popup_frequency' => 'session',
            'include_all_website' => '1',
        ], $extra);
    }
}
