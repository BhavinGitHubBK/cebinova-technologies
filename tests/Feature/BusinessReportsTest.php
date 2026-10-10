<?php

namespace Tests\Feature;

use App\Enums\UserRole;
use App\Models\Lead;
use App\Models\User;
use App\Services\Reports\BusinessMetrics;
use App\Services\Reports\CompanyReports;
use App\Services\Reports\ManagementReport;
use App\Services\Reports\ReportQuery;
use App\Services\Reports\ReportRange;
use Carbon\CarbonImmutable;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\Request;
use Tests\TestCase;

class BusinessReportsTest extends TestCase
{
    use RefreshDatabase;

    private User $owner;

    protected function setUp(): void
    {
        parent::setUp();
        $this->travelTo(CarbonImmutable::parse('2026-10-03 12:00:00', 'UTC'));
        $this->owner = User::factory()->create(['role' => UserRole::SuperAdmin, 'is_active' => true]);
        $this->actingAs($this->owner);
    }

    public function test_owner_dashboard_and_empty_charts_render_without_fake_growth(): void
    {
        $this->get(route('admin.reports.dashboard'))->assertOk()->assertSee('Business command center')
            ->assertSee('No data available for selected period')->assertSee('No comparable previous-period data');
        $this->get(route('admin.reports.index'))->assertOk()
            ->assertSee('Business growth')
            ->assertSee('No enquiries recorded for this period.')
            ->assertSee('Track business growth');
        self::assertNull(BusinessMetrics::change(12, 0));
        self::assertSame(-50.0, BusinessMetrics::change(5, 10));
    }

    public function test_removed_operational_report_types_are_not_available(): void
    {
        foreach (['quotations', 'billing', 'payments', 'outstanding', 'clients', 'projects'] as $type) {
            $this->get(route('admin.reports.show', $type))->assertNotFound();
        }
    }

    public function test_date_ranges_inclusive_boundaries_previous_period_and_limits(): void
    {
        $range = ReportRange::fromRequest(Request::create('/', 'GET', ['range' => 'custom', 'from' => '2026-10-01', 'to' => '2026-10-03']));
        self::assertSame('2026-09-28', $range->previous()->from->toDateString());
        self::assertSame('2026-09-30', $range->previous()->to->toDateString());
        foreach (['today', 'last7', 'month', 'last_month', 'quarter', 'year'] as $preset) {
            $this->get(route('admin.reports.dashboard', ['range' => $preset]))->assertOk();
        }
        foreach ([['from' => '2020-01-01', 'to' => '2026-10-03'], ['from' => '2026-10-04', 'to' => '2026-10-03'], ['from' => 'bad', 'to' => '2026-10-03'], ['from' => '2026-10-01']] as $dates) {
            $this->get(route('admin.reports.dashboard', ['range' => 'custom', ...$dates]))->assertRedirect()->assertSessionHasErrors();
        }
        Lead::factory()->create(['created_at' => '2026-10-03 23:59:59']);
        Lead::factory()->create(['created_at' => '2026-10-04 00:00:00']);
        self::assertSame(1, $range->apply(Lead::query(), 'created_at')->count());
        config(['app.timezone' => 'Asia/Kolkata']);
        self::assertSame('Asia/Kolkata', ReportRange::fromRequest(Request::create('/', 'GET', ['range' => 'today']))->from->timezoneName);
    }

    public function test_lead_cohorts_sources_current_pipeline_and_estimated_value_reconcile(): void
    {
        Lead::factory()->create(['status' => 'Won', 'source' => 'Pricing', 'created_at' => '2026-10-01', 'estimated_value' => 100]);
        Lead::factory()->create(['status' => 'Qualified', 'source' => 'Pricing', 'created_at' => '2026-10-02', 'estimated_value' => 200]);
        Lead::factory()->create(['status' => 'Lost', 'source' => 'Contact', 'created_at' => '2026-10-02', 'estimated_value' => 300]);
        Lead::factory()->create(['status' => 'Won', 'created_at' => '2026-09-30', 'estimated_value' => 400]);
        $removed = Lead::factory()->create(['status' => 'Won', 'created_at' => '2026-10-01']);
        $removed->delete();
        $data = $this->metrics();
        self::assertSame(3, $data['leadTotal']);
        self::assertSame(1, $data['leadCounts']['Won']);
        self::assertSame(1, $data['leadCounts']['Lost']);
        self::assertSame(33.3, $data['wonRate']);
        self::assertSame(2, $data['sources']->firstWhere('source', 'Pricing')->total);
        self::assertSame(2, $data['pipeline']->firstWhere('status', 'Won')->total);
        self::assertEquals(500, $data['pipeline']->firstWhere('status', 'Won')->estimated_value);
        self::assertSame(1, $data['previousLeads']);
    }

    public function test_all_report_types_and_exports_use_same_filters(): void
    {
        $inside = Lead::factory()->create(['name' => 'Selected record', 'status' => 'Won', 'created_at' => '2026-10-02']);
        Lead::factory()->create(['name' => 'Excluded record', 'status' => 'Lost', 'created_at' => '2026-10-02']);
        foreach (ReportQuery::TYPES as $type) {
            $this->get(route('admin.reports.show', $type))->assertOk();
        }
        $params = ['type' => 'sales', 'range' => 'custom', 'from' => '2026-10-01', 'to' => '2026-10-03', 'status' => 'Won'];
        $this->get(route('admin.reports.show', $params))
            ->assertOk()
            ->assertViewHas('rows', function ($rows) use ($inside) {
                return $rows->contains('id', $inside->id)
                    && $rows->where('name', 'Excluded record')->isEmpty();
            });
        $csv = $this->get(route('admin.reports.export', $params))->assertOk()->streamedContent();
        self::assertStringContainsString('Selected record', $csv);
        self::assertStringNotContainsString('Excluded record', $csv);
        $this->assertDatabaseHas('activity_logs', ['action' => 'report_export']);
    }

    public function test_csv_formula_injection_protection_handles_whitespace_and_controls(): void
    {
        foreach (['=SUM(1,1)', '+cmd', '-10+20', '@SUM(A1)', "\t=HYPERLINK(1)", "\r\n+payload", "\xEF\xBB\xBF=payload"] as $value) {
            self::assertStringStartsWith("'", ReportQuery::csvSafe($value));
        }
        Lead::factory()->create(['name' => '=SUM(1,1)', 'created_at' => '2026-10-02']);
        $csv = $this->get(route('admin.reports.export', 'sales'))->assertOk()->streamedContent();
        self::assertStringContainsString("'=SUM(1,1)", $csv);
    }

    public function test_company_reports_cover_growth_closing_and_capacity(): void
    {
        Lead::factory()->create([
            'name' => 'Website Winner',
            'status' => 'Won',
            'service' => 'Website Development',
            'source' => 'Pricing',
            'city' => 'Ahmedabad',
            'business_type' => 'Retail',
            'estimated_value' => 1000,
            'created_at' => '2026-10-02',
            'assigned_to' => $this->owner->id,
        ]);
        Lead::factory()->create([
            'name' => 'Budget Loss',
            'status' => 'Lost',
            'service' => 'Website Development',
            'source' => 'Contact',
            'lost_reason' => 'Budget',
            'estimated_value' => 500,
            'created_at' => '2026-10-02',
            'follow_up_at' => '2026-10-01 09:00:00',
        ]);
        Lead::factory()->create([
            'name' => 'Open Software',
            'status' => 'New',
            'service' => 'Custom Software',
            'created_at' => '2026-10-02',
            'estimated_value' => 800,
        ]);

        $this->get(route('admin.reports.index', ['range' => 'custom', 'from' => '2026-10-01', 'to' => '2026-10-03']))
            ->assertOk()
            ->assertSee('Total enquiries')
            ->assertSee('Conversion rate')
            ->assertSee('Website Development')
            ->assertSee('Budget')
            ->assertSee('Export report')
            ->assertDontSee('See whether new enquiries are rising');
        $report = app(ManagementReport::class)->build(new ReportRange(CarbonImmutable::parse('2026-10-01'), CarbonImmutable::parse('2026-10-03')));
        self::assertSame('3', $report['kpis'][0]['value']);
        self::assertSame('33.3%', $report['kpis'][1]['value']);
        self::assertSame('₹1,000', $report['kpis'][3]['value']);
        self::assertSame(800, $report['pipeline']['value']);
        self::assertSame(3, array_sum(array_column($report['services'], 'total')));
        self::assertSame(3, array_sum(array_column($report['sources'], 'total')));

        $this->get(route('admin.reports.review', ['report' => 'services', 'range' => 'custom', 'from' => '2026-10-01', 'to' => '2026-10-03']))
            ->assertOk()
            ->assertSee('Website Development')
            ->assertSee('50%')
            ->assertSee('Compare its win rate with the others before you invest in it.');
        $this->get(route('admin.reports.review', ['report' => 'losses', 'range' => 'custom', 'from' => '2026-10-01', 'to' => '2026-10-03']))
            ->assertOk()
            ->assertSee('Budget');
        $this->get(route('admin.reports.review', ['report' => 'pipeline', 'range' => 'month']))
            ->assertOk()
            ->assertSee('₹800')
            ->assertSee('This is the live pipeline');
        $this->get(route('admin.reports.review', 'missing'))->assertNotFound();
        self::assertTrue(app(CompanyReports::class)->exists('growth'));
    }

    private function metrics(): array
    {
        return app(BusinessMetrics::class)->dashboard(new ReportRange(CarbonImmutable::parse('2026-10-01'), CarbonImmutable::parse('2026-10-03')), $this->owner);
    }
}
