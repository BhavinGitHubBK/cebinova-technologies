<?php

namespace Tests\Feature;

use App\Models\MarketingPackage;
use App\Models\MarketingPlan;
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
}
