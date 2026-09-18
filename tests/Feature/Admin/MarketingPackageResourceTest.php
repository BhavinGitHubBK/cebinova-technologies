<?php

namespace Tests\Feature\Admin;

use App\Filament\Resources\MarketingPackages\Pages\CreateMarketingPackage;
use App\Filament\Resources\MarketingPackages\Pages\EditMarketingPackage;
use App\Filament\Resources\MarketingPackages\Pages\ListMarketingPackages;
use App\Filament\Resources\MarketingPackages\RelationManagers\PlansRelationManager;
use App\Models\MarketingPackage;
use App\Models\MarketingPlan;
use App\Models\User;
use Filament\Facades\Filament;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class MarketingPackageResourceTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        Filament::setCurrentPanel('admin');

        $this->actingAs(User::factory()->create([
            'email' => 'admin@cebinova.test',
        ]));
    }

    public function test_admin_can_list_and_create_package(): void
    {
        Livewire::test(ListMarketingPackages::class)
            ->assertOk();

        Livewire::test(CreateMarketingPackage::class)
            ->fillForm([
                'key' => 'custom',
                'title' => 'Custom Package',
                'service' => 'Custom Package',
                'heading' => 'Custom heading',
                'is_active' => true,
                'sort_order' => 10,
            ])
            ->call('create')
            ->assertHasNoFormErrors()
            ->assertNotified();

        $this->assertDatabaseHas('marketing_packages', [
            'key' => 'custom',
            'title' => 'Custom Package',
        ]);
    }

    public function test_admin_can_add_plan_via_relation_manager(): void
    {
        $package = MarketingPackage::query()->create([
            'key' => 'custom',
            'title' => 'Custom Package',
            'service' => 'Custom Package',
            'is_active' => true,
            'sort_order' => 1,
        ]);

        Livewire::test(PlansRelationManager::class, [
            'ownerRecord' => $package,
            'pageClass' => EditMarketingPackage::class,
        ])
            ->callTableAction('create', data: [
                'key' => 'monthly',
                'label' => 'Monthly',
                'duration' => '1 Month',
                'price' => 1234,
                'period' => '/ month',
                'sort_order' => 0,
                'is_active' => true,
            ])
            ->assertHasNoTableActionErrors();

        $this->assertDatabaseHas('marketing_plans', [
            'marketing_package_id' => $package->id,
            'key' => 'monthly',
            'label' => 'Monthly',
            'price' => 1234,
        ]);

        $this->assertSame(1, MarketingPlan::query()->where('marketing_package_id', $package->id)->count());
    }
}
