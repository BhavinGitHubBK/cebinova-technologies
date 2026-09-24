<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\ActivityLog;
use App\Models\Faq;
use App\Models\Lead;
use App\Models\Media;
use App\Models\Package;
use App\Models\Service;
use App\Models\Testimonial;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\View\View;

class DashboardController extends Controller
{
    public function index(): View
    {
        $statusCounts = Lead::query()
            ->select('status', DB::raw('count(*) as total'))
            ->groupBy('status')
            ->pluck('total', 'status');

        $totalLeads = (int) $statusCounts->sum();
        $converted = (int) ($statusCounts['Converted'] ?? 0);
        $conversionRate = $totalLeads > 0 ? round(($converted / $totalLeads) * 100, 1) : 0;

        $monthlyMap = [];
        for ($i = 11; $i >= 0; $i--) {
            $month = now()->subMonths($i)->format('Y-m');
            $monthlyMap[$month] = 0;
        }

        Lead::query()
            ->where('created_at', '>=', now()->subMonths(11)->startOfMonth())
            ->get(['created_at'])
            ->each(function (Lead $lead) use (&$monthlyMap) {
                $key = optional($lead->created_at)->format('Y-m');
                if ($key && array_key_exists($key, $monthlyMap)) {
                    $monthlyMap[$key]++;
                }
            });

        $byService = Lead::query()
            ->select('service', DB::raw('count(*) as total'))
            ->whereNotNull('service')
            ->groupBy('service')
            ->orderByDesc('total')
            ->limit(8)
            ->pluck('total', 'service');

        $followUpsDue = Lead::query()
            ->whereNotNull('follow_up_at')
            ->where('follow_up_at', '<=', now()->endOfDay())
            ->whereNotIn('status', ['Converted', 'Lost'])
            ->orderBy('follow_up_at')
            ->limit(8)
            ->get();

        $followUpsOverdue = (int) Lead::query()
            ->whereNotNull('follow_up_at')
            ->where('follow_up_at', '<', now()->startOfDay())
            ->whereNotIn('status', ['Converted', 'Lost'])
            ->count();

        $leadsToday = (int) Lead::query()
            ->whereDate('created_at', today())
            ->count();

        return view('admin.dashboard.index', [
            'totalLeads' => $totalLeads,
            'statusCounts' => $statusCounts,
            'conversionRate' => $conversionRate,
            'servicesCount' => Service::query()->count(),
            'activePackages' => Package::query()->active()->count(),
            'recentLeads' => Lead::query()->latest()->limit(8)->get(),
            'recentActivities' => ActivityLog::query()->with('user')->latest()->limit(10)->get(),
            'newLeadsCount' => (int) ($statusCounts['New'] ?? 0),
            'contactedCount' => (int) ($statusCounts['Contacted'] ?? 0),
            'followUpCount' => (int) ($statusCounts['Follow-up'] ?? 0),
            'lostCount' => (int) ($statusCounts['Lost'] ?? 0),
            'convertedCount' => $converted,
            'monthlyLabels' => array_keys($monthlyMap),
            'monthlyValues' => array_values($monthlyMap),
            'serviceLabels' => $byService->keys()->values(),
            'serviceValues' => $byService->values(),
            'followUpsDue' => $followUpsDue,
            'followUpsOverdue' => $followUpsOverdue,
            'leadsToday' => $leadsToday,
            'testimonialsCount' => $this->safeCount(Testimonial::class),
            'faqsCount' => $this->safeCount(Faq::class),
            'mediaCount' => $this->safeCount(Media::class),
        ]);
    }

    /**
     * @param  class-string  $model
     */
    private function safeCount(string $model): int
    {
        try {
            $table = (new $model)->getTable();
            if (! Schema::hasTable($table)) {
                return 0;
            }

            return (int) $model::query()->count();
        } catch (\Throwable) {
            return 0;
        }
    }
}
