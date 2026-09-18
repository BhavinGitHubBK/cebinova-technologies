<?php

namespace Tests\Feature;

use App\Models\MarketingPackage;
use App\Models\MarketingPlan;
use App\Support\MarketingPackages;
use Database\Seeders\MarketingPackageSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class MarketingPackagesHelperTest extends TestCase
{
    use RefreshDatabase;

    public function test_seeder_imports_three_packages_and_regular_monthly_price(): void
    {
        $this->seed(MarketingPackageSeeder::class);

        $this->assertSame(3, MarketingPackage::query()->count());
        $this->assertGreaterThanOrEqual(12, MarketingPlan::query()->count());

        $plan = MarketingPlan::query()
            ->whereHas('package', fn ($q) => $q->where('title', 'Regular Marketing'))
            ->where('label', 'Monthly')
            ->first();

        $this->assertSame(4999, $plan?->price);
    }

    public function test_helper_reads_prices_and_rejects_inactive_plan(): void
    {
        $this->seed(MarketingPackageSeeder::class);

        $this->assertSame(4999, MarketingPackages::priceAmount('Regular Marketing', 'Monthly'));
        $this->assertContains('Regular Marketing', MarketingPackages::categories());

        $plan = MarketingPlan::query()
            ->where('label', 'Monthly')
            ->whereHas('package', fn ($q) => $q->where('key', 'regular'))
            ->first();
        $plan->update(['is_active' => false]);

        $this->assertFalse(MarketingPackages::isValid('Regular Marketing', 'Monthly'));
        $this->assertNull(MarketingPackages::priceAmount('Regular Marketing', 'Monthly'));
    }

    public function test_package_array_matches_catalog_keys(): void
    {
        $this->seed(MarketingPackageSeeder::class);
        $regular = MarketingPackages::packageArray('regular');
        $this->assertSame('Regular Marketing', $regular['title']);
        $this->assertArrayHasKey('monthly', $regular['plans']);
    }
}
