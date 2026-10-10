<?php

namespace Tests\Feature\Admin;

use App\Enums\UserRole;
use App\Models\Lead;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class LeadIndexTest extends TestCase
{
    use RefreshDatabase;

    public function test_status_service_and_source_filters_use_choices(): void
    {
        $admin = User::factory()->create([
            'role' => UserRole::SuperAdmin,
            'is_active' => true,
            'password' => Hash::make('password'),
        ]);
        Lead::factory()->create(['name' => 'Open Website Lead', 'status' => 'New', 'service' => 'Website Development', 'source' => 'Website Contact']);
        Lead::factory()->create(['name' => 'Lost Software Lead', 'status' => 'Lost', 'service' => 'Custom Software', 'source' => 'WhatsApp']);

        $this->actingAs($admin)
            ->get(route('admin.leads.index'))
            ->assertOk()
            ->assertSee('Website Development')
            ->assertSee('WhatsApp')
            ->assertSee('name="service"', false)
            ->assertSee('name="source"', false);

        $this->actingAs($admin)
            ->get(route('admin.leads.index', ['status' => 'Lost']))
            ->assertOk()
            ->assertViewHas('leads', function ($leads) {
                return $leads->contains('name', 'Lost Software Lead')
                    && $leads->where('name', 'Open Website Lead')->isEmpty();
            });
    }

    public function test_bulk_status_updates_without_a_page_and_rejects_the_same_status(): void
    {
        $admin = User::factory()->create([
            'role' => UserRole::SuperAdmin,
            'is_active' => true,
            'password' => Hash::make('password'),
        ]);
        $lead = Lead::factory()->create(['status' => 'New']);
        $this->actingAs($admin);

        $this->postJson(route('admin.leads.bulk-status'), [
            'ids' => [$lead->id],
            'status' => 'New',
        ], ['X-Admin-Ajax' => '1'])
            ->assertStatus(422)
            ->assertJsonPath('message', 'Selected leads are already New.');

        $this->postJson(route('admin.leads.bulk-status'), [
            'ids' => [$lead->id],
            'status' => 'Contacted',
        ], ['X-Admin-Ajax' => '1'])
            ->assertOk()
            ->assertJsonPath('status', 'Contacted')
            ->assertJsonPath('ids.0', $lead->id);

        $this->assertSame('Contacted', $lead->fresh()->status);
    }

    public function test_lead_list_shows_the_latest_lead_first_and_can_sort_by_name(): void
    {
        $admin = User::factory()->create([
            'role' => UserRole::SuperAdmin,
            'is_active' => true,
            'password' => Hash::make('password'),
        ]);
        Lead::factory()->create(['name' => 'Alpha Older', 'created_at' => '2026-10-01 09:00:00']);
        Lead::factory()->create(['name' => 'Zebra Newer', 'created_at' => '2026-10-06 09:00:00']);
        $this->actingAs($admin);

        $this->get(route('admin.leads.index'))
            ->assertOk()
            ->assertViewHas('leads', fn ($leads) => $leads->first()->name === 'Zebra Newer');

        $this->get(route('admin.leads.index', ['sort' => 'name', 'dir' => 'asc']))
            ->assertOk()
            ->assertViewHas('leads', fn ($leads) => $leads->first()->name === 'Alpha Older');
    }

    public function test_lead_detail_shows_contact_actions_and_enquiry_fields(): void
    {
        $admin = User::factory()->create([
            'role' => UserRole::SuperAdmin,
            'is_active' => true,
            'password' => Hash::make('password'),
        ]);
        $lead = Lead::factory()->create([
            'name' => 'Bhavin Khadela',
            'business_name' => 'AAAA',
            'phone' => '9876501111',
            'email' => 'bapatel183@gmail.com',
            'service' => 'Custom Software',
            'city' => 'Surat',
            'budget' => '₹1.5 Lakh – ₹5 Lakh',
            'source' => 'Website Contact',
            'message' => 'Need a billing app',
            'status' => 'Contacted',
        ]);

        $this->actingAs($admin)
            ->get(route('admin.leads.show', $lead))
            ->assertOk()
            ->assertSee('Bhavin Khadela')
            ->assertSee('AAAA')
            ->assertSee('Call')
            ->assertSee('WhatsApp')
            ->assertSee('mailto:bapatel183@gmail.com', false)
            ->assertSee('Need a billing app')
            ->assertSee('name="status"', false)
            ->assertSee('Move this lead to trash?', false);
    }

    public function test_saving_an_unchanged_lead_reports_the_current_status(): void
    {
        $admin = User::factory()->create([
            'role' => UserRole::SuperAdmin,
            'is_active' => true,
            'password' => Hash::make('password'),
        ]);
        $lead = Lead::factory()->create([
            'status' => 'Contacted',
            'notes' => 'Called the owner',
        ]);

        $this->actingAs($admin)
            ->put(route('admin.leads.update', $lead), [
                'status' => 'Contacted',
                'notes' => 'Called the owner',
            ], ['X-Admin-Ajax' => '1', 'Accept' => 'application/json'])
            ->assertStatus(422)
            ->assertJsonPath('message', 'This lead is already Contacted.');

        $this->assertSame('Contacted', $lead->fresh()->status);

        $this->actingAs($admin)
            ->put(route('admin.leads.update', $lead), [
                'status' => 'Contacted',
                'notes' => 'Left a message',
            ], ['X-Admin-Ajax' => '1', 'Accept' => 'application/json'])
            ->assertOk()
            ->assertJsonPath('ok', true);

        $this->assertSame('Left a message', $lead->fresh()->notes);
    }
}
