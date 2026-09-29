<?php

namespace Tests\Feature;

use App\Enums\UserRole;
use App\Models\Lead;
use App\Models\Package;
use App\Models\PricingOption;
use App\Models\User;
use App\Services\PricingCalculator;
use Database\Seeders\PackageSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;
use Tests\TestCase;

class PricingSystemTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(PackageSeeder::class);
    }

    public function test_pricing_page_uses_active_ordered_database_packages(): void
    {
        $starter = Package::query()->where('key', 'starter')->firstOrFail();
        $business = Package::query()->where('key', 'business')->firstOrFail();
        $professional = Package::query()->where('key', 'professional')->firstOrFail();
        $starter->update(['sort_order' => 30]);
        $business->update(['sort_order' => 10]);
        $professional->update(['is_active' => false]);

        $plans = \App\Support\WebsitePackages::plans();

        $this->assertSame('business', $plans[0]['key']);
        $this->assertSame('starter', $plans[1]['key']);
        $this->assertCount(2, $plans);
        $this->get(route('pricing'))
            ->assertOk()
            ->assertSee('name="website" value="business"', false)
            ->assertDontSee('name="website" value="professional"', false);
    }

    public function test_package_features_are_database_driven(): void
    {
        $package = Package::query()->where('key', 'business')->firstOrFail();
        $package->features()->delete();
        $package->features()->create(['name' => 'Admin-managed feature', 'display_value' => '100 products']);

        $plan = collect(\App\Support\WebsitePackages::plans())->firstWhere('key', 'business');

        $this->assertSame('Admin-managed feature', $plan['features'][0]['name']);
        $this->assertSame('100 products', $plan['features'][0]['value']);
    }

    public function test_calculator_uses_database_prices_and_deduplicates_addons(): void
    {
        $package = Package::query()->where('key', 'business')->firstOrFail();
        $addon = PricingOption::query()->where('slug', 'android-app')->firstOrFail();
        $package->plans()->firstOrFail()->update(['price' => 27999]);
        $addon->update(['price' => 25001]);

        $result = app(PricingCalculator::class)->calculate($package->id, [$addon->id, $addon->id]);

        $this->assertSame(53000.0, $result['total']);
        $this->assertSame([$addon->id], $result['option_ids']);
    }

    public function test_calculator_rejects_inactive_or_invalid_options(): void
    {
        $package = Package::query()->where('key', 'business')->firstOrFail();
        $option = PricingOption::query()->where('slug', 'android-app')->firstOrFail();
        $option->update(['is_active' => false]);

        $this->expectException(ValidationException::class);
        app(PricingCalculator::class)->calculate($package->id, [$option->id]);
    }

    public function test_pricing_enquiry_recalculates_and_stores_snapshot(): void
    {
        $package = Package::query()->where('key', 'business')->firstOrFail();
        $option = PricingOption::query()->where('slug', 'basic-hosting')->firstOrFail();
        $package->plans()->firstOrFail()->update(['price' => 27999]);

        $this->postJson(route('contact.store'), [
            'name' => 'Pricing Customer',
            'phone' => '9876501234',
            'service' => 'Business Solution',
            'source' => 'CEBINOVA Pricing',
            'pricing_package_id' => $package->id,
            'pricing_option_ids' => [$option->id],
            'plan_price' => '1',
        ])->assertOk();

        $lead = Lead::query()->firstOrFail();
        $this->assertSame($package->id, $lead->pricing_package_id);
        $this->assertSame('30999.00', $lead->estimated_total);
        $this->assertEquals(27999.0, $lead->pricing_snapshot['package']['price']);
    }

    public function test_pricing_admin_requires_authorization_and_can_create_option(): void
    {
        $this->get(route('admin.pricing-options.index'))->assertRedirect(route('admin.login'));

        $viewer = User::factory()->create(['role' => UserRole::Viewer, 'is_active' => true]);
        $this->actingAs($viewer)->post(route('admin.pricing-options.store'), [])->assertForbidden();

        $editor = User::factory()->create([
            'role' => UserRole::Editor,
            'is_active' => true,
            'password' => Hash::make('password'),
        ]);
        $this->actingAs($editor)->post(route('admin.pricing-options.store'), [
            'type' => 'addon',
            'name' => 'Custom Integration',
            'price' => '12500.50',
            'billing_period' => 'one_time',
            'sort_order' => 5,
            'is_active' => 1,
        ])->assertRedirect(route('admin.pricing-options.index'));

        $this->assertDatabaseHas('pricing_options', ['slug' => 'custom-integration', 'price' => 12500.50]);
    }

    public function test_admin_can_create_update_and_disable_package_with_features(): void
    {
        $editor = User::factory()->create(['role' => UserRole::Editor, 'is_active' => true]);

        $this->actingAs($editor)->post(route('admin.packages.store'), [
            'category' => 'website',
            'name' => 'Enterprise',
            'key' => 'enterprise',
            'sort_order' => 40,
            'is_active' => 1,
            'package_features_text' => "Custom dashboard | Included\nPriority launch",
            'plans' => [[
                'label' => 'One Time',
                'key' => 'one-time',
                'price' => 75000,
                'period' => 'One Time',
                'is_active' => 1,
            ]],
        ])->assertRedirect(route('admin.packages.index'));

        $package = Package::query()->where('key', 'enterprise')->firstOrFail();
        $this->assertDatabaseHas('package_features', ['package_id' => $package->id, 'name' => 'Custom dashboard', 'display_value' => 'Included']);

        $this->actingAs($editor)->put(route('admin.packages.update', $package), [
            'category' => 'website',
            'name' => 'Enterprise',
            'slug' => $package->slug,
            'key' => 'enterprise',
            'sort_order' => 40,
            'is_active' => 0,
            'package_features_text' => 'Updated feature',
            'plans' => [[
                'label' => 'One Time',
                'key' => 'one-time',
                'price' => 80000,
                'period' => 'One Time',
                'is_active' => 1,
            ]],
        ])->assertRedirect(route('admin.packages.index'));

        $this->assertFalse($package->fresh()->is_active);
        $this->assertDatabaseHas('package_features', ['package_id' => $package->id, 'name' => 'Updated feature']);
    }
}
