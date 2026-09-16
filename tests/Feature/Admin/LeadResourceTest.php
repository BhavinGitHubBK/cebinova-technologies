<?php

namespace Tests\Feature\Admin;

use App\Filament\Resources\Leads\Pages\EditLead;
use App\Filament\Resources\Leads\Pages\ListLeads;
use App\Filament\Resources\Leads\Pages\ViewLead;
use App\Models\Lead;
use App\Models\User;
use Filament\Facades\Filament;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class LeadResourceTest extends TestCase
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

    public function test_admin_can_list_leads(): void
    {
        $leads = Lead::factory()->count(3)->create();

        Livewire::test(ListLeads::class)
            ->assertOk()
            ->assertCanSeeTableRecords($leads);
    }

    public function test_admin_can_view_a_lead(): void
    {
        $lead = Lead::factory()->create([
            'name' => 'Asha Patel',
        ]);

        Livewire::test(ViewLead::class, [
            'record' => $lead->getRouteKey(),
        ])
            ->assertOk()
            ->assertSchemaStateSet([
                'name' => 'Asha Patel',
            ]);
    }

    public function test_admin_can_update_lead_status(): void
    {
        $lead = Lead::factory()->create([
            'status' => Lead::STATUS_NEW,
        ]);

        Livewire::test(EditLead::class, [
            'record' => $lead->getRouteKey(),
        ])
            ->fillForm([
                'status' => 'Contacted',
            ])
            ->call('save')
            ->assertHasNoFormErrors()
            ->assertNotified();

        $this->assertDatabaseHas('leads', [
            'id' => $lead->id,
            'status' => 'Contacted',
        ]);
    }

    public function test_admin_can_filter_leads_by_status(): void
    {
        $newLeads = Lead::factory()->count(2)->create(['status' => 'New']);
        $lostLeads = Lead::factory()->count(2)->create(['status' => 'Lost']);

        Livewire::test(ListLeads::class)
            ->assertCanSeeTableRecords($newLeads->merge($lostLeads))
            ->filterTable('status', 'Lost')
            ->assertCanSeeTableRecords($lostLeads)
            ->assertCanNotSeeTableRecords($newLeads);
    }
}
