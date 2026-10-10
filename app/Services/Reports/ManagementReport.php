<?php

namespace App\Services\Reports;

use App\Models\Lead;
use Carbon\CarbonImmutable;
use Illuminate\Support\Facades\DB;

class ManagementReport
{
    /** Open deals above the average valued open deal are treated as high value. */
    private const CLOSED = ['Won', 'Converted', 'Lost'];

    private const WON = ['Won', 'Converted'];

    public function build(ReportRange $range): array
    {
        $current = $this->period($range);
        $previous = $this->period($range->previous());
        $statuses = $this->statuses($range);
        $sources = $this->shares($range, 'source', $current['total']);
        $services = $this->shares($range, 'service', $current['total']);
        $cities = $this->cities($range);
        $pipeline = $this->pipeline();
        $losses = $this->losses($range);
        $attention = $this->attention($pipeline['high_line']);

        return [
            'range' => $range,
            'kpis' => $this->kpis($current, $previous, $pipeline),
            'growth' => $this->growth($range),
            'sources' => $sources,
            'funnel' => $this->funnel($current['total'], $statuses),
            'services' => $services,
            'cities' => $cities,
            'closing' => [
                'won' => $current['won'],
                'lost' => $current['lost'],
                'rate' => $this->rate($current['won'], $current['total']),
                'rate_label' => $this->rateLabel($this->rate($current['won'], $current['total'])),
                'reasons' => $losses,
            ],
            'pipeline' => $pipeline,
            'insights' => $this->insights($current, $previous, $services, $sources, $pipeline, $attention, $losses),
            'attention' => $attention,
            'records' => [
                'leads' => route('admin.leads.index', ['from' => $range->from->toDateString(), 'to' => $range->to->toDateString()]),
                'export' => route('admin.reports.export', ['type' => 'sales', ...$range->parameters()]),
            ],
        ];
    }

    /**
     * @return array{total: int, won: int, lost: int, won_value: int}
     */
    private function period(ReportRange $range): array
    {
        $row = $range->apply(Lead::query(), 'leads.created_at')
            ->selectRaw('COUNT(*) as total')
            ->selectRaw('SUM(CASE WHEN leads.status IN (?, ?) THEN 1 ELSE 0 END) as won', self::WON)
            ->selectRaw('SUM(CASE WHEN leads.status = ? THEN 1 ELSE 0 END) as lost', ['Lost'])
            ->selectRaw('COALESCE(SUM(CASE WHEN leads.status IN (?, ?) THEN leads.estimated_value ELSE 0 END), 0) as won_value', self::WON)
            ->first();

        return [
            'total' => (int) $row->total,
            'won' => (int) $row->won,
            'lost' => (int) $row->lost,
            'won_value' => (int) $row->won_value,
        ];
    }

    /**
     * @return array<string, int>
     */
    private function statuses(ReportRange $range): array
    {
        return $range->apply(Lead::query(), 'leads.created_at')
            ->select('status')
            ->selectRaw('COUNT(*) as total')
            ->groupBy('status')
            ->pluck('total', 'status')
            ->map(fn ($total) => (int) $total)
            ->all();
    }

    /**
     * @return array{labels: list<string>, enquiries: list<int>, won: list<int>, has_data: bool}
     */
    private function growth(ReportRange $range): array
    {
        $from = $range->from->startOfDay();
        $to = $range->to->startOfDay();
        $days = (int) $from->diffInDays($to) + 1;
        $bucket = $days <= 31 ? 'day' : ($days <= 120 ? 'week' : 'month');
        $rows = $range->apply(Lead::query(), 'leads.created_at')
            ->selectRaw('DATE(leads.created_at) as day')
            ->selectRaw('COUNT(*) as total')
            ->selectRaw('SUM(CASE WHEN leads.status IN (?, ?) THEN 1 ELSE 0 END) as won', self::WON)
            ->groupBy(DB::raw('DATE(leads.created_at)'))
            ->get();
        $counts = [];
        foreach ($rows as $row) {
            $date = CarbonImmutable::parse($row->day)->startOfDay();
            $key = match ($bucket) {
                'week' => $date->startOfWeek()->toDateString(),
                'month' => $date->startOfMonth()->toDateString(),
                default => $date->toDateString(),
            };
            $counts[$key]['enquiries'] = ($counts[$key]['enquiries'] ?? 0) + (int) $row->total;
            $counts[$key]['won'] = ($counts[$key]['won'] ?? 0) + (int) $row->won;
        }

        $labels = [];
        $enquiries = [];
        $won = [];
        $cursor = match ($bucket) {
            'week' => $from->startOfWeek(),
            'month' => $from->startOfMonth(),
            default => $from,
        };
        while ($cursor->lte($to)) {
            $key = $cursor->toDateString();
            $labels[] = $bucket === 'month' ? $cursor->format('M Y') : $cursor->format('d M');
            $enquiries[] = (int) ($counts[$key]['enquiries'] ?? 0);
            $won[] = (int) ($counts[$key]['won'] ?? 0);
            $cursor = match ($bucket) {
                'week' => $cursor->addWeek(),
                'month' => $cursor->addMonth(),
                default => $cursor->addDay(),
            };
        }

        return [
            'labels' => $labels,
            'enquiries' => $enquiries,
            'won' => $won,
            'has_data' => array_sum($enquiries) > 0,
        ];
    }

    /**
     * @return list<array{label: string, total: int, share: float}>
     */
    private function shares(ReportRange $range, string $column, int $total): array
    {
        return $range->apply(Lead::query(), 'leads.created_at')
            ->select('leads.'.$column)
            ->selectRaw('COUNT(*) as total')
            ->groupBy('leads.'.$column)
            ->orderByDesc('total')
            ->get()
            ->map(fn ($row) => [
                'label' => filled($row->{$column}) ? (string) $row->{$column} : 'Not recorded',
                'total' => (int) $row->total,
            ])
            ->groupBy('label')
            ->map(fn ($rows, string $label) => [
                'label' => $label,
                'total' => (int) $rows->sum('total'),
                'share' => $total > 0 ? round(((int) $rows->sum('total') / $total) * 100, 1) : 0.0,
            ])
            ->sortByDesc('total')
            ->values()
            ->take(8)
            ->all();
    }

    /**
     * @return list<array{label: string, total: int}>
     */
    private function cities(ReportRange $range): array
    {
        $rows = $range->apply(Lead::query(), 'leads.created_at')
            ->whereNotNull('city')
            ->where('city', '!=', '')
            ->select('city')
            ->selectRaw('COUNT(*) as total')
            ->groupBy('city')
            ->orderByDesc('total')
            ->limit(6)
            ->get()
            ->map(fn ($row) => ['label' => (string) $row->city, 'total' => (int) $row->total])
            ->all();

        return count($rows) >= 2 && array_sum(array_column($rows, 'total')) >= 3 ? $rows : [];
    }

    /**
     * @param  array<string, int>  $statuses
     * @return array{stages: list<array{label: string, total: int}>, rates: list<array{label: string, value: string}>, has_data: bool}
     */
    private function funnel(int $total, array $statuses): array
    {
        $qualified = ($statuses['Qualified'] ?? 0) + ($statuses['Proposal Sent'] ?? 0) + ($statuses['Won'] ?? 0) + ($statuses['Converted'] ?? 0);
        $proposal = ($statuses['Proposal Sent'] ?? 0) + ($statuses['Won'] ?? 0) + ($statuses['Converted'] ?? 0);
        $won = ($statuses['Won'] ?? 0) + ($statuses['Converted'] ?? 0);
        $rates = [];
        if ($total > 0 && $qualified > 0) {
            $rates[] = ['label' => 'Enquiry to qualified', 'value' => $this->rateLabel($this->rate($qualified, $total))];
        }
        if ($qualified > 0 && $proposal > 0) {
            $rates[] = ['label' => 'Qualified to proposal', 'value' => $this->rateLabel($this->rate($proposal, $qualified))];
        }
        if ($proposal > 0 && $won > 0) {
            $rates[] = ['label' => 'Proposal to won', 'value' => $this->rateLabel($this->rate($won, $proposal))];
        }

        return [
            'stages' => [
                ['label' => 'Enquiries', 'total' => $total],
                ['label' => 'Qualified', 'total' => $qualified],
                ['label' => 'Proposal', 'total' => $proposal],
                ['label' => 'Won', 'total' => $won],
            ],
            'rates' => $rates,
            'has_data' => $total > 0,
        ];
    }

    /**
     * @return array{count: int, value: int, average: int, high: int}
     */
    private function pipeline(): array
    {
        $open = Lead::query()->whereNotIn('status', self::CLOSED);
        $count = (clone $open)->count();
        $value = (int) (clone $open)->sum('estimated_value');
        $average = (int) round((float) ((clone $open)->where('estimated_value', '>', 0)->avg('estimated_value') ?? 0));
        $high = $average > 0
            ? (clone $open)->where('estimated_value', '>', $average)->count()
            : 0;

        $averageDeal = $count > 0 ? (int) round($value / $count) : 0;

        return [
            'count' => $count,
            'value' => $value,
            'value_label' => $this->money($value),
            'average' => $averageDeal,
            'average_label' => $this->money($averageDeal),
            'high' => $high,
            'high_line' => $average,
            'high_line_label' => $this->money($average),
        ];
    }

    /**
     * @return list<array{label: string, total: int}>
     */
    private function losses(ReportRange $range): array
    {
        return $range->apply(Lead::query(), 'leads.created_at')
            ->where('status', 'Lost')
            ->whereNotNull('lost_reason')
            ->where('lost_reason', '!=', '')
            ->select('lost_reason')
            ->selectRaw('COUNT(*) as total')
            ->groupBy('lost_reason')
            ->orderByDesc('total')
            ->limit(4)
            ->get()
            ->map(fn ($row) => ['label' => (string) $row->lost_reason, 'total' => (int) $row->total])
            ->all();
    }

    /**
     * @return list<array{title: string, detail: string, href: string}>
     */
    private function attention(int $averageOpenValue): array
    {
        $open = Lead::query()->whereNotIn('status', self::CLOSED);
        $items = [];
        $overdue = (clone $open)->whereNotNull('follow_up_at')->where('follow_up_at', '<', now()->startOfDay())->count();
        if ($overdue > 0) {
            $items[] = [
                'title' => $overdue.' overdue follow-up'.($overdue === 1 ? '' : 's'),
                'detail' => 'These open enquiries were due before today.',
                'href' => route('admin.leads.index'),
            ];
        }
        $unassigned = (clone $open)->whereNull('assigned_to')->count();
        if ($unassigned > 0) {
            $items[] = [
                'title' => $unassigned.' unassigned enquir'.($unassigned === 1 ? 'y' : 'ies'),
                'detail' => 'Nobody is currently responsible for these open enquiries.',
                'href' => route('admin.leads.index'),
            ];
        }
        if ($averageOpenValue > 0) {
            $inactive = (clone $open)
                ->where('estimated_value', '>', $averageOpenValue)
                ->where('updated_at', '<', now()->subDays(7))
                ->count();
            if ($inactive > 0) {
                $items[] = [
                    'title' => $inactive.' high-value lead'.($inactive === 1 ? '' : 's').' inactive for 7+ days',
                    'detail' => 'Above the average open deal value, with no update in the last 7 days.',
                    'href' => route('admin.leads.index'),
                ];
            }
        }
        $proposals = (clone $open)->where('status', 'Proposal Sent')->count();
        if ($proposals > 0) {
            $items[] = [
                'title' => $proposals.' proposal'.($proposals === 1 ? '' : 's').' waiting',
                'detail' => 'These enquiries are at Proposal Sent.',
                'href' => route('admin.leads.index', ['status' => 'Proposal Sent']),
            ];
        }

        return $items;
    }

    /**
     * @param  array{total: int, won: int, lost: int, won_value: int}  $current
     * @param  array{total: int, won: int, lost: int, won_value: int}  $previous
     * @param  list<array{label: string, total: int, share: float}>  $services
     * @param  list<array{label: string, total: int, share: float}>  $sources
     * @param  array{count: int, value: int, average: int, high: int, high_line: int}  $pipeline
     * @param  list<array{title: string, detail: string, href: string}>  $attention
     * @param  list<array{label: string, total: int}>  $losses
     * @return list<array{tone: string, title: string, detail: string}>
     */
    private function insights(array $current, array $previous, array $services, array $sources, array $pipeline, array $attention, array $losses): array
    {
        $items = [];
        $enquiryChange = BusinessMetrics::change($current['total'], $previous['total']);
        if ($enquiryChange !== null && $current['total'] > 0) {
            $items[] = [
                'tone' => $enquiryChange >= 10 ? 'positive' : ($enquiryChange <= -10 ? 'attention' : 'information'),
                'title' => $this->changeTitle($enquiryChange).' enquiries',
                'detail' => $current['total'].' this period, '.$previous['total'].' in the previous period.',
            ];
        }
        $currentRate = $this->rate($current['won'], $current['total']);
        $previousRate = $this->rate($previous['won'], $previous['total']);
        if ($currentRate !== null && $previousRate !== null) {
            $delta = round($currentRate - $previousRate, 1);
            if (abs($delta) >= 5) {
                $items[] = [
                    'tone' => $delta > 0 ? 'positive' : 'attention',
                    'title' => 'Conversion '.($delta > 0 ? 'improved' : 'fell'),
                    'detail' => $this->rateLabel($previousRate).' previously, '.$this->rateLabel($currentRate).' now.',
                ];
            }
        }
        $topService = $services[0] ?? null;
        if ($topService && $topService['label'] !== 'Not recorded' && $topService['share'] >= 30) {
            $items[] = [
                'tone' => 'opportunity',
                'title' => $topService['label'].' leads demand',
                'detail' => $topService['share'].'% of enquiries in this period.',
            ];
        }
        if ($pipeline['value'] > 0) {
            $items[] = [
                'tone' => 'information',
                'title' => $this->money($pipeline['value']).' is open',
                'detail' => $pipeline['count'].' open opportunities are still in play.',
            ];
        }
        $topSource = $sources[0] ?? null;
        if ($topSource && $topSource['label'] !== 'Not recorded' && $current['total'] >= 3) {
            $items[] = [
                'tone' => 'opportunity',
                'title' => $topSource['label'].' is the strongest source',
                'detail' => $topSource['total'].' enquiries, '.$topSource['share'].'% of this period.',
            ];
        }
        if ($attention !== [] && count($items) < 5) {
            $items[] = [
                'tone' => 'attention',
                'title' => $attention[0]['title'],
                'detail' => $attention[0]['detail'],
            ];
        }
        if ($losses !== [] && count($items) < 5) {
            $items[] = [
                'tone' => 'attention',
                'title' => $losses[0]['label'].' is the top lost reason',
                'detail' => $losses[0]['total'].' lost enquir'.($losses[0]['total'] === 1 ? 'y' : 'ies').' recorded this reason.',
            ];
        }

        return array_slice($items, 0, 5);
    }

    /**
     * @param  array{total: int, won: int, lost: int, won_value: int}  $current
     * @param  array{total: int, won: int, lost: int, won_value: int}  $previous
     * @param  array{count: int, value: int, average: int, high: int, high_line: int}  $pipeline
     * @return list<array{label: string, value: string, note: string, tone: string, exact: string}>
     */
    private function kpis(array $current, array $previous, array $pipeline): array
    {
        $enquiryChange = BusinessMetrics::change($current['total'], $previous['total']);
        $rate = $this->rate($current['won'], $current['total']);

        return [
            [
                'label' => 'Total enquiries',
                'value' => (string) $current['total'],
                'note' => $this->changeNote($enquiryChange, $previous['total']),
                'tone' => $this->changeTone($enquiryChange),
                'exact' => $current['total'].' new enquiries in this period.',
            ],
            [
                'label' => 'Conversion rate',
                'value' => $this->rateLabel($rate),
                'note' => $current['total'] === 0 ? 'No enquiries in this period.' : $current['won'].' won from '.$current['total'].' enquiries',
                'tone' => $rate !== null && $rate >= 30 ? 'positive' : ($current['total'] >= 3 && ($rate ?? 0) < 15 ? 'attention' : 'neutral'),
                'exact' => 'Won means status Won. Older converted records are included.',
            ],
            [
                'label' => 'Pipeline value',
                'value' => $this->money($pipeline['value']),
                'note' => $pipeline['count'].' open opportunities',
                'tone' => $pipeline['count'] > 0 ? 'positive' : 'neutral',
                'exact' => cebinova_inr($pipeline['value']).' estimated on open enquiries. Won and lost are excluded. This is the live pipeline, not limited to the selected dates.',
            ],
            [
                'label' => 'Won value',
                'value' => $this->money($current['won_value']),
                'note' => $current['won'].' deal'.($current['won'] === 1 ? '' : 's').' won',
                'tone' => $current['won_value'] > 0 ? 'positive' : 'neutral',
                'exact' => cebinova_inr($current['won_value']).' is the estimated value stored on enquiries marked won in this period. Invoiced revenue is not recorded.',
            ],
        ];
    }

    private function changeNote(?float $change, int $previous): string
    {
        if ($change === null) {
            return $previous === 0 ? 'No previous period to compare.' : 'No change to compare.';
        }
        if (abs($change) < 1) {
            return 'No change vs previous period';
        }

        return ($change > 0 ? '+' : '').$change.'% vs previous period';
    }

    private function changeTone(?float $change): string
    {
        if ($change === null || abs($change) < 1) {
            return 'neutral';
        }

        return $change > 0 ? 'positive' : 'attention';
    }

    private function changeTitle(float $change): string
    {
        if (abs($change) < 1) {
            return 'Enquiries were flat';
        }

        return 'Enquiries '.($change > 0 ? 'increased '.$change.'%' : 'decreased '.abs($change).'%');
    }

    private function rate(int $part, int $whole): ?float
    {
        return $whole > 0 ? round(($part / $whole) * 100, 1) : null;
    }

    private function rateLabel(?float $rate): string
    {
        return $rate === null ? '—' : rtrim(rtrim(number_format($rate, 1, '.', ''), '0'), '.').'%';
    }

    private function money(int $amount): string
    {
        $sign = $amount < 0 ? '-' : '';
        $abs = abs($amount);
        if ($abs >= 10000000) {
            return $sign.'₹'.rtrim(rtrim(number_format($abs / 10000000, 2, '.', ''), '0'), '.').'Cr';
        }
        if ($abs >= 100000) {
            return $sign.'₹'.rtrim(rtrim(number_format($abs / 100000, 2, '.', ''), '0'), '.').'L';
        }

        return $sign.cebinova_inr($abs);
    }
}
