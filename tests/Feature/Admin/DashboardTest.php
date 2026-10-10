<?php

namespace Tests\Feature\Admin;

use App\Enums\UserRole;
use App\Models\Lead;
use App\Models\User;
use App\Services\Admin\ActivityLogger;
use App\Services\Admin\DashboardMetrics;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class DashboardTest extends TestCase
{
    use RefreshDatabase;

    private function admin(array $overrides = []): User
    {
        return User::factory()->create(array_merge([
            'role' => UserRole::SuperAdmin,
            'is_active' => true,
            'password' => Hash::make('password'),
        ], $overrides));
    }

    public function test_guest_is_redirected_from_dashboard(): void
    {
        $this->get(route('admin.dashboard'))->assertRedirect(route('admin.login'));
    }

    public function test_dashboard_renders_real_lead_metrics_and_empty_states(): void
    {
        $this->actingAs($this->admin())
            ->get(route('admin.dashboard'))
            ->assertOk()
            ->assertSee('Needs attention')
            ->assertSee('Sales pipeline')
            ->assertSee('0 overdue · 0 due today')
            ->assertSee('Nothing needs action right now.')
            ->assertDontSee('Leads in this view')
            ->assertDontSee('Revenue')
            ->assertDontSee('Outstanding invoices')
            ->assertDontSee('Open support tickets');
    }

    public function test_dashboard_uses_lead_status_counts_and_conversion(): void
    {
        $this->actingAs($this->admin());

        Lead::factory()->count(4)->create(['status' => 'New', 'service' => 'Website Development']);
        Lead::factory()->count(2)->create(['status' => 'Contacted', 'service' => 'Custom Software']);
        Lead::factory()->create(['status' => 'Converted', 'service' => 'Website Development']);
        Lead::factory()->create(['status' => 'Lost', 'service' => 'eCommerce Solutions']);

        $response = $this->get(route('admin.dashboard'))
            ->assertOk()
            ->assertSee('8')
            ->assertSee('12.5%')
            ->assertSee('Website Development')
            ->assertSee('Needs attention');

        $metrics = app(DashboardMetrics::class)->build(request());
        $this->assertSame(8, $metrics['totalLeads']);
        $this->assertSame(4, $metrics['newLeadsCount']);
        $this->assertSame(1, $metrics['wonCount']);
        $this->assertSame(12.5, $metrics['conversionRate']);
        $this->assertSame(6, $metrics['pipelineCount']);
    }

    public function test_overdue_follow_ups_appear_in_action_center(): void
    {
        $this->actingAs($this->admin());
        $lead = Lead::factory()->create([
            'name' => 'Urgent Followup Person',
            'status' => 'Follow-up',
            'follow_up_at' => now()->subDay(),
        ]);

        $this->get(route('admin.dashboard'))
            ->assertOk()
            ->assertSee('Needs attention')
            ->assertSee('1 overdue · 0 due today')
            ->assertSee('Urgent Followup Person')
            ->assertSee('href="#needs-attention"', false);
    }

    public function test_notification_bell_lists_a_new_lead_until_it_is_seen(): void
    {
        $this->actingAs($this->admin());
        Lead::factory()->create(['name' => 'Fresh Enquiry Person']);

        $this->get(route('admin.dashboard'))
            ->assertOk()
            ->assertSee('Fresh Enquiry Person')
            ->assertSee('data-notification-count', false);

        $this->post(route('admin.notifications.seen'))->assertOk();

        $this->get(route('admin.dashboard'))
            ->assertOk()
            ->assertSee('Fresh Enquiry Person')
            ->assertDontSee('data-notification-count', false);
    }

    public function test_login_noise_is_hidden_from_dashboard_activity(): void
    {
        $admin = $this->admin();
        $this->actingAs($admin);
        ActivityLogger::log('login', 'auth', $admin, 'Admin logged in');
        ActivityLogger::log('update', 'leads', null, 'Lead updated');

        $this->get(route('admin.dashboard'))
            ->assertOk()
            ->assertSee('Lead updated')
            ->assertDontSee('Admin logged in');
    }

    public function test_period_filter_does_not_invent_trend_data(): void
    {
        $this->actingAs($this->admin());
        Lead::factory()->create(['created_at' => now()->subDays(2)]);

        $this->get(route('admin.dashboard', ['period' => '30']))
            ->assertOk()
            ->assertSee('Last 30 days');
    }

    public function test_filters_limit_the_dashboard_to_the_selected_period_and_service(): void
    {
        $this->actingAs($this->admin());
        Lead::factory()->create([
            'name' => 'Old Website Lead',
            'service' => 'Website Development',
            'status' => 'New',
            'created_at' => now()->subDays(40),
        ]);
        Lead::factory()->create([
            'name' => 'Fresh Software Lead',
            'service' => 'Custom Software',
            'status' => 'Contacted',
            'created_at' => now()->subDay(),
        ]);

        $this->get(route('admin.dashboard', ['range' => '7', 'service' => 'Custom Software']))
            ->assertOk()
            ->assertDontSee('Leads in this view')
            ->assertDontSee('Old Website Lead');

        $section = $this->getJson(route('admin.dashboard.section', 'leads').'?range=today')
            ->assertOk();
        $this->assertStringNotContainsString('Fresh Software Lead', $section->json('html'));
        $this->assertStringNotContainsString('Old Website Lead', $section->json('html'));
    }
}
