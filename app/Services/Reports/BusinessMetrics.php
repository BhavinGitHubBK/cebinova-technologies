<?php

namespace App\Services\Reports;

use App\Models\ActivityLog;
use App\Models\Lead;
use App\Models\LeadTask;
use App\Models\User;
use Illuminate\Support\Facades\DB;

class BusinessMetrics
{
    public function dashboard(ReportRange $range, User $user): array
    {
        $leads = $range->apply(Lead::query(), 'created_at');
        $leadCounts = $this->counts(clone $leads);
        $leadTotal = array_sum($leadCounts);
        $previousLeads = $range->previous()->apply(Lead::query(), 'created_at')->count();
        $pipeline = Lead::query()->select('status')->selectRaw('COUNT(*) as total, COALESCE(SUM(estimated_value),0) as estimated_value')->groupBy('status')->get();

        return [
            'range' => $range,
            'leadCounts' => $leadCounts,
            'leadTotal' => $leadTotal,
            'previousLeads' => $previousLeads,
            'leadChange' => self::change($leadTotal, $previousLeads),
            'wonRate' => $leadTotal ? round(100 * (($leadCounts['Won'] ?? $leadCounts['Converted'] ?? 0) / $leadTotal), 1) : null,
            'qualifiedRate' => $leadTotal ? round(100 * (($leadCounts['Qualified'] ?? 0) / $leadTotal), 1) : null,
            'pipeline' => $pipeline,
            'sources' => $this->breakdown(clone $leads, 'source'),
            'services' => $this->breakdown(clone $leads, 'service'),
            'leadTrend' => $this->trend(clone $leads, 'created_at', $range),
            'attention' => [
                'followups' => LeadTask::query()->with('lead')->where('status', 'Pending')->where('due_at', '<', now())->orderBy('due_at')->limit(6)->get(),
            ],
            'activities' => ActivityLog::query()->with('user')->where('module', 'leads')->latest()->limit(12)->get(),
        ];
    }

    public static function change(int|float $current, int|float $previous): ?float
    {
        return $previous > 0 ? round(($current - $previous) / $previous * 100, 1) : null;
    }

    public function moneyTotals($query, string $expression, string $currency = 'currency_code'): array
    {
        return $query->reorder()->select($currency)->selectRaw('COALESCE(SUM('.$expression.'),0) as amount')->groupBy($currency)
            ->get()->mapWithKeys(fn ($row) => [$row->$currency => (string) $row->amount])->all();
    }

    private function counts($query): array
    {
        return $query->select('status')->selectRaw('COUNT(*) as total')->groupBy('status')->pluck('total', 'status')->all();
    }

    private function breakdown($query, string $column)
    {
        return $query->select($column)->selectRaw('COUNT(*) as total')->groupBy($column)->orderByDesc('total')->limit(20)->get();
    }

    private function trend($query, string $column, ReportRange $range, ?string $amount = null)
    {
        $monthly = $range->from->diffInDays($range->to) > 62;
        $bucket = $monthly ? (DB::connection()->getDriverName() === 'sqlite' ? "strftime('%Y-%m', $column)" : "DATE_FORMAT($column, '%Y-%m')") : "DATE($column)";
        $query->selectRaw($bucket.' as period, '.($amount ? "SUM($amount)" : 'COUNT(*)').' as total')->groupByRaw($bucket)->orderBy('period');
        if ($amount) {
            $query->addSelect('currency_code')->groupBy('currency_code');
        }

        return $query->get();
    }
}
