<?php

namespace Tests\Feature;

use App\Enums\UserRole;
use App\Models\Lead;
use App\Models\Package;
use App\Models\User;
use App\Services\MarketingPackageCalculator;
use App\Support\MarketingPackages;
use Database\Seeders\PackageSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Validation\ValidationException;
use Tests\TestCase;

class MarketingPackageSystemTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(PackageSeeder::class);
    }

    public function test_catalog_uses_active_database_packages_and_normalized_deliverables(): void
    {
        $regular = Package::query()->where('key', 'regular')->firstOrFail();
        $plan = $regular->plans()->where('key', 'monthly')->firstOrFail();
        $plan->deliverables()->delete();
        $plan->deliverables()->create(['group' => 'included', 'name' => 'Database-managed creative', 'sort_order' => 0]);

        $catalog = MarketingPackages::catalog();

        $this->assertSame('Database-managed creative', $catalog['regular']['plans']['monthly']['includes'][0]);

        $regular->update(['is_active' => false]);
        $this->assertArrayNotHasKey('regular', MarketingPackages::catalog());
    }

    public function test_frontend_reflects_changed_database_price(): void
    {
        $plan = Package::query()->where('key', 'growth')->firstOrFail()->plans()->where('key', 'yearly')->firstOrFail();
        $plan->update(['price' => 65432]);

        $this->get(route('marketing-packages'))->assertOk()->assertSee(cebinova_inr(65432));
    }

    public function test_calculator_rejects_a_plan_from_another_package(): void
    {
        $regular = Package::query()->where('key', 'regular')->firstOrFail();
        $foreignPlan = Package::query()->where('key', 'festival')->firstOrFail()->plans()->firstOrFail();

        $this->expectException(ValidationException::class);
        app(MarketingPackageCalculator::class)->calculate($regular->id, $foreignPlan->id);
    }

    public function test_marketing_enquiry_recalculates_price_and_preserves_snapshot(): void
    {
        $package = Package::query()->where('key', 'growth')->firstOrFail();
        $plan = $package->plans()->where('key', 'monthly')->firstOrFail();
        $plan->update(['price' => 7777]);

        $this->postJson(route('contact.store'), [
            'name' => 'Marketing Customer',
            'phone' => '9876501234',
            'service' => MarketingPackages::SERVICE,
            'package_category' => 'Tampered package',
            'plan_duration' => 'Tampered duration',
            'plan_price' => '1',
            'marketing_package_id' => $package->id,
            'marketing_plan_id' => $plan->id,
        ])->assertOk();

        $lead = Lead::query()->firstOrFail();
        $this->assertSame(cebinova_inr(7777), $lead->selected_price);
        $this->assertSame(7777, $lead->marketing_snapshot['price']);

        $plan->update(['price' => 9999]);
        $this->assertSame(7777, $lead->fresh()->marketing_snapshot['price']);
    }

    public function test_editor_can_manage_marketing_plan_deliverables(): void
    {
        $editor = User::factory()->create(['role' => UserRole::Editor, 'is_active' => true]);

        $this->actingAs($editor)->post(route('admin.packages.store'), [
            'category' => 'marketing_regular',
            'name' => 'Local Marketing',
            'key' => 'local-marketing',
            'service_label' => 'Local Marketing',
            'is_active' => 1,
            'plans' => [[
                'label' => 'Monthly',
                'key' => 'monthly',
                'price' => 5000,
                'features_text' => "Eight posts\nTwo reels",
                'monthly_pace_text' => 'Two posts each week',
                'is_active' => 1,
            ]],
        ])->assertRedirect(route('admin.packages.index'));

        $plan = Package::query()->where('key', 'local-marketing')->firstOrFail()->plans()->firstOrFail();
        $this->assertDatabaseHas('marketing_plan_deliverables', ['package_plan_id' => $plan->id, 'group' => 'included', 'name' => 'Eight posts']);
        $this->assertDatabaseHas('marketing_plan_deliverables', ['package_plan_id' => $plan->id, 'group' => 'monthly_pace', 'name' => 'Two posts each week']);
    }
}
