<?php

namespace App\Services\Admin;

use App\Models\ActivityLog;
use App\Models\Demo;
use App\Models\Industry;
use App\Models\Lead;
use App\Models\Package;
use App\Models\PageSection;
use App\Models\Service;
use App\Models\Solution;
use Carbon\CarbonImmutable;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Schema;

class DashboardMetrics
{
    public const WON_STATUSES = ['Converted', 'Won'];

    public const CLOSED_STATUSES = ['Converted', 'Won', 'Lost'];

    public const PERIODS = [
        '30' => '30 Days',
        '90' => '3 Months',
        '180' => '6 Months',
        '365' => '12 Months',
    ];

    public const RANGES = [
        'all' => 'All time',
        'today' => 'Today',
        '7' => 'Last 7 days',
        '30' => 'Last 30 days',
        '90' => 'Last 3 months',
        '180' => 'Last 6 months',
        '365' => 'Last 12 months',
    ];

    public function build(Request $request): array
    {
        $range = $this->range($request);
        $status = in_array($request->query('status'), Lead::STATUSES, true) ? (string) $request->query('status') : '';
        $service = trim((string) $request->query('service', ''));
        if (mb_strlen($service) > 120) {
            $service = '';
        }

        $services = Lead::query()
            ->whereNotNull('service')
            ->where('service', '!=', '')
            ->distinct()
            ->orderBy('service')
            ->pluck('service');

        if ($service !== '' && ! $services->contains($service)) {
            $service = '';
        }

        $cohort = fn ($query) => $this->applyCohort($query, $range, $service);

        $statusCounts = Lead::query()
            ->tap($cohort)
            ->select('status', DB::raw('count(*) as total'))
            ->groupBy('status')
            ->pluck('total', 'status');

        $totalLeads = (int) $statusCounts->sum();
        $wonCount = (int) collect(self::WON_STATUSES)->sum(fn ($status) => (int) ($statusCounts[$status] ?? 0));
        $lostCount = (int) ($statusCounts['Lost'] ?? 0);
        $newCount = (int) ($statusCounts['New'] ?? 0);
        $pipelineCount = max(0, $totalLeads - $wonCount - $lostCount);
        $conversionRate = $totalLeads > 0 ? round(($wonCount / $totalLeads) * 100, 1) : 0;

        $openQuery = fn ($query) => $query->whereNotIn('status', self::CLOSED_STATUSES);

        $followUpsOverdue = (int) Lead::query()->whereNotNull('follow_up_at')
            ->where('follow_up_at', '<', now()->startOfDay())
            ->tap($openQuery)
            ->count();

        $followUpsTodayCount = (int) Lead::query()->whereNotNull('follow_up_at')
            ->whereDate('follow_up_at', today())
            ->tap($openQuery)
            ->count();

        $followUpsOverdueList = Lead::query()->with('assignee')->whereNotNull('follow_up_at')
            ->where('follow_up_at', '<', now()->startOfDay())
            ->tap($openQuery)
            ->orderBy('follow_up_at')
            ->limit(6)
            ->get();

        $followUpsToday = Lead::query()->with('assignee')->whereNotNull('follow_up_at')
            ->whereDate('follow_up_at', today())
            ->tap($openQuery)
            ->orderBy('follow_up_at')
            ->limit(6)
            ->get();

        $followUpsUpcoming = Lead::query()->with('assignee')->whereNotNull('follow_up_at')
            ->where('follow_up_at', '>', now()->endOfDay())
            ->where('follow_up_at', '<=', now()->addDays(7)->endOfDay())
            ->tap($openQuery)
            ->orderBy('follow_up_at')
            ->limit(5)
            ->get();

        $leadsToday = (int) Lead::query()->tap($cohort)->whereDate('created_at', today())->count();

        $trend = $this->trend($range === 'all' ? '365' : ($range === 'today' ? '1' : $range));

        $byService = Lead::query()
            ->tap($cohort)
            ->when($status !== '', fn ($query) => $query->where('status', $status))
            ->select('service', DB::raw('count(*) as total'))
            ->whereNotNull('service')
            ->where('service', '!=', '')
            ->groupBy('service')
            ->orderByDesc('total')
            ->limit(6)
            ->pluck('total', 'service');

        $pipeline = collect(Lead::STATUSES)->map(function (string $status) use ($statusCounts, $totalLeads) {
            $count = (int) ($statusCounts[$status] ?? 0);

            return [
                'status' => $status,
                'count' => $count,
                'percent' => $totalLeads > 0 ? round(($count / $totalLeads) * 100) : 0,
            ];
        })->all();

        $attentionItems = $this->attentionItems($followUpsOverdueList, $followUpsToday);
        $listedIds = collect($attentionItems)->pluck('id')->filter()->unique()->values()->all();
        $rangeLabel = self::RANGES[$range];
        $filterSummary = $totalLeads.' '.($totalLeads === 1 ? 'lead' : 'leads').' · '.$rangeLabel;
        if ($service !== '') {
            $filterSummary .= ' · '.$service;
        }
        if ($status !== '') {
            $filterSummary .= ' · the list below is limited to '.$status;
        }

        return [
            'range' => $range,
            'ranges' => self::RANGES,
            'rangeLabel' => $rangeLabel,
            'status' => $status,
            'service' => $service,
            'services' => $services,
            'filterSummary' => $filterSummary,
            'period' => $range === 'all' ? '365' : $range,
            'periods' => self::PERIODS,
            'totalLeads' => $totalLeads,
            'statusCounts' => $statusCounts,
            'newLeadsCount' => $newCount,
            'pipelineCount' => $pipelineCount,
            'wonCount' => $wonCount,
            'convertedCount' => $wonCount,
            'lostCount' => $lostCount,
            'contactedCount' => (int) ($statusCounts['Contacted'] ?? 0),
            'followUpCount' => (int) (($statusCounts['Qualified'] ?? 0) + ($statusCounts['Follow-up'] ?? 0)),
            'qualifiedCount' => (int) (($statusCounts['Qualified'] ?? 0) + ($statusCounts['Follow-up'] ?? 0)),
            'proposalSentCount' => (int) ($statusCounts['Proposal Sent'] ?? 0),
            'conversionRate' => $conversionRate,
            'leadsToday' => $leadsToday,
            'followUpsOverdue' => $followUpsOverdue,
            'followUpsTodayCount' => $followUpsTodayCount,
            'attentionCount' => count($attentionItems),
            'pipeline' => $pipeline,
            'trendLabels' => $trend['labels'],
            'trendValues' => $trend['values'],
            'monthlyLabels' => $trend['labels'],
            'monthlyValues' => $trend['values'],
            'serviceLabels' => $byService->keys()->values(),
            'serviceValues' => $byService->values(),
            'attentionItems' => $attentionItems,
            'followUpsOverdueList' => $followUpsOverdueList,
            'followUpsToday' => $followUpsToday,
            'followUpsUpcoming' => $followUpsUpcoming,
            'followUpsDue' => $followUpsOverdueList->concat($followUpsToday)->take(8),
            'recentLeads' => Lead::query()
                ->tap($cohort)
                ->when($status !== '', fn ($query) => $query->where('status', $status))
                ->when($listedIds !== [], fn ($query) => $query->whereNotIn('id', $listedIds))
                ->latest()
                ->limit(5)
                ->get(),
            'recentActivities' => ActivityLog::query()
                ->with('user')
                ->whereNotIn('action', ['login', 'logout'])
                ->where(function ($query) {
                    $query->whereNull('module')->orWhere('module', '!=', 'auth');
                })
                ->latest()
                ->limit(6)
                ->get(),
            'contentOverview' => $this->contentOverview(),
        ];
    }

    /**
     * @param  Collection<int, Lead>  $overdue
     * @param  Collection<int, Lead>  $today
     * @return list<array<string, mixed>>
     */
    private function attentionItems(Collection $overdue, Collection $today): array
    {
        $items = [];
        $seen = [];

        foreach ($overdue as $lead) {
            $seen[$lead->id] = true;
            $items[] = $this->attentionRow($lead, 'overdue', 'Overdue follow-up', 'danger');
        }

        foreach ($today as $lead) {
            if (isset($seen[$lead->id])) {
                continue;
            }
            $items[] = $this->attentionRow($lead, 'today', 'Follow-up due today', 'warning');
        }

        return $items;
    }

    /**
     * @return array<string, mixed>
     */
    private function attentionRow(Lead $lead, string $kind, string $label, string $tone): array
    {
        return [
            'id' => $lead->id,
            'kind' => $kind,
            'tone' => $tone,
            'label' => $label,
            'title' => $lead->name,
            'context' => $lead->service ?: 'General enquiry',
            'when' => $kind === 'new'
                ? $lead->created_at?->diffForHumans()
                : $lead->follow_up_at?->format('d M, H:i'),
            'status' => $lead->status,
            'url' => route('admin.leads.show', $lead),
        ];
    }

    private function range(Request $request): string
    {
        $range = (string) $request->query('range', '');
        if (array_key_exists($range, self::RANGES)) {
            return $range;
        }

        $legacy = (string) $request->query('period', '');

        return array_key_exists($legacy, self::PERIODS) ? $legacy : 'all';
    }

    private function applyCohort($query, string $range, string $service)
    {
        if ($range === 'today') {
            $query->whereDate('created_at', today());
        } elseif ($range !== 'all') {
            $query->where('created_at', '>=', now()->subDays(max(0, ((int) $range) - 1))->startOfDay());
        }

        if ($service !== '') {
            $query->where('service', $service);
        }

        return $query;
    }

    /**
     * @return array{labels: list<string>, values: list<int>}
     */
    private function trend(string $period): array
    {
        $days = (int) $period;
        $from = now()->subDays($days - 1)->startOfDay();
        $useMonths = $days > 45;
        $driver = Schema::getConnection()->getDriverName();
        $bucket = $useMonths
            ? ($driver === 'sqlite' ? "strftime('%Y-%m', created_at)" : "DATE_FORMAT(created_at, '%Y-%m')")
            : 'DATE(created_at)';

        $counts = Lead::query()
            ->where('created_at', '>=', $from)
            ->selectRaw($bucket.' as bucket, count(*) as total')
            ->groupByRaw($bucket)
            ->pluck('total', 'bucket');

        $labels = [];
        $values = [];

        if ($useMonths) {
            $cursor = CarbonImmutable::parse($from)->startOfMonth();
            $end = now()->startOfMonth();
            while ($cursor <= $end) {
                $key = $cursor->format('Y-m');
                $labels[] = $cursor->format('M Y');
                $values[] = (int) ($counts[$key] ?? 0);
                $cursor = $cursor->addMonth();
            }
        } else {
            $cursor = CarbonImmutable::parse($from)->startOfDay();
            $end = now()->startOfDay();
            while ($cursor <= $end) {
                $key = $cursor->toDateString();
                $labels[] = $cursor->format('d M');
                $values[] = (int) ($counts[$key] ?? $counts[$key.' 00:00:00'] ?? 0);
                $cursor = $cursor->addDay();
            }
        }

        return ['labels' => $labels, 'values' => $values];
    }

    /**
     * @return array<string, array{label: string, count: int, url: string}>
     */
    private function contentOverview(): array
    {
        $items = [];

        if (Schema::hasTable('services')) {
            $items['services'] = ['label' => 'Services', 'count' => (int) Service::query()->count(), 'url' => route('admin.services.index')];
        }
        if (Schema::hasTable('solutions')) {
            $items['solutions'] = ['label' => 'Solutions', 'count' => (int) Solution::query()->count(), 'url' => route('admin.solutions.index')];
        }
        if (Schema::hasTable('industries')) {
            $items['industries'] = ['label' => 'Industries', 'count' => (int) Industry::query()->count(), 'url' => route('admin.industries.index')];
        }
        if (Schema::hasTable('packages')) {
            $items['packages'] = ['label' => 'Packages', 'count' => (int) Package::query()->count(), 'url' => route('admin.packages.index')];
        }
        if (Schema::hasTable('demos') && Route::has('admin.demos.index')) {
            $items['demos'] = ['label' => 'Demos', 'count' => (int) Demo::query()->count(), 'url' => route('admin.demos.index')];
        }
        if (Schema::hasTable('page_sections')) {
            $items['sections'] = ['label' => 'Page sections', 'count' => (int) PageSection::query()->count(), 'url' => route('admin.page-sections.index')];
        }

        return $items;
    }
}
