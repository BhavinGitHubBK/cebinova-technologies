<?php

namespace App\Services\Reports;

use App\Models\Lead;
use App\Models\User;
use Illuminate\Support\Collection;

class CompanyReports
{
    public const CLOSED = ['Won', 'Converted', 'Lost'];

    public const WON = ['Won', 'Converted'];

    /** @var array<string, array{group: string, title: string, purpose: string}> */
    public const REPORTS = [
        'growth' => [
            'group' => 'Demand',
            'title' => 'Enquiry growth',
            'purpose' => 'See whether new enquiries are rising before you hire or add a service.',
        ],
        'services' => [
            'group' => 'Demand',
            'title' => 'Service demand',
            'purpose' => 'See which offers people ask for, and which of those actually close.',
        ],
        'sources' => [
            'group' => 'Demand',
            'title' => 'Lead sources',
            'purpose' => 'See which channels bring enquiries that turn into work.',
        ],
        'markets' => [
            'group' => 'Demand',
            'title' => 'Markets',
            'purpose' => 'See which cities and business types are asking, before you spend on a new market.',
        ],
        'wins' => [
            'group' => 'Closing',
            'title' => 'Win rate',
            'purpose' => 'See how much of this period’s demand became won work.',
        ],
        'pipeline' => [
            'group' => 'Closing',
            'title' => 'Pipeline value',
            'purpose' => 'See the estimated value still open, so you know the work already in play.',
        ],
        'losses' => [
            'group' => 'Closing',
            'title' => 'Lost deals',
            'purpose' => 'See why deals were lost before you change price, packaging, or the offer.',
        ],
        'followups' => [
            'group' => 'Capacity',
            'title' => 'Follow-up load',
            'purpose' => 'See overdue follow-ups before you spend more to bring in new enquiries.',
        ],
        'team' => [
            'group' => 'Capacity',
            'title' => 'Team workload',
            'purpose' => 'See who is carrying new enquiries and open leads before you move work around.',
        ],
    ];

    public function exists(string $report): bool
    {
        return isset(self::REPORTS[$report]);
    }

    public function catalog(ReportRange $range): array
    {
        $data = $this->bundle($range);

        return [
            'range' => $range,
            'groups' => collect(['Demand', 'Closing', 'Capacity'])
                ->map(fn (string $group) => [
                    'title' => $group,
                    'cards' => collect(self::REPORTS)
                        ->filter(fn (array $meta) => $meta['group'] === $group)
                        ->map(fn (array $meta, string $key) => $this->card($key, $meta, $data, $range))
                        ->values()
                        ->all(),
                ])
                ->push([
                    'title' => 'Records',
                    'cards' => [[
                        'href' => route('admin.reports.show', ['type' => 'sales', ...$range->parameters()]),
                        'title' => 'Lead register',
                        'purpose' => 'Open the enquiries behind these reports and export the same list.',
                        'figure' => (string) $data['total'],
                        'figureLabel' => 'records in this period',
                        'reading' => 'Use this when you need the names, not only the totals.',
                        'tone' => '',
                    ]],
                ])
                ->all(),
        ];
    }

    public function review(string $report, ReportRange $range): array
    {
        $data = $this->bundle($range);
        $meta = self::REPORTS[$report];
        $built = match ($report) {
            'growth' => $this->growthReview($data),
            'services' => $this->breakdownReview($data['services'], 'Service', 'No service-tagged enquiries in this period.', $this->serviceReading($data['services'])),
            'sources' => $this->breakdownReview($data['sources'], 'Source', 'No sources recorded in this period.', $this->sourceReading($data['sources'])),
            'markets' => $this->marketsReview($data),
            'wins' => $this->winsReview($data),
            'pipeline' => $this->pipelineReview($data),
            'losses' => $this->lossesReview($data),
            'followups' => $this->followupsReview(),
            'team' => $this->teamReview($data['team']),
        };

        return array_merge($built, [
            'key' => $report,
            'range' => $range,
            'title' => $meta['title'],
            'purpose' => $meta['purpose'],
        ]);
    }

    private function card(string $key, array $meta, array $data, ReportRange $range): array
    {
        $summary = match ($key) {
            'growth' => [
                'figure' => (string) $data['total'],
                'figureLabel' => 'enquiries',
                'reading' => $this->changeLabel($data['change']),
                'tone' => $data['change'] !== null && $data['change'] <= -15 ? 'alert' : ($data['change'] !== null && $data['change'] >= 15 ? 'good' : ''),
            ],
            'services' => $this->leaderCard($data['services'], 'enquiries', 'No service demand in this period.'),
            'sources' => $this->leaderCard($data['sources'], 'enquiries', 'No source data in this period.'),
            'markets' => $this->leaderCard($data['cities'], 'enquiries', 'No city data in this period.'),
            'wins' => [
                'figure' => $this->rateLabel($data['winRate']),
                'figureLabel' => 'of enquiries won',
                'reading' => $data['total'] === 0 ? 'No enquiries in this period.' : $data['won'].' won · '.$data['lost'].' lost',
                'tone' => $data['total'] >= 3 && ($data['winRate'] ?? 0) < 15 ? 'alert' : (($data['winRate'] ?? 0) >= 30 ? 'good' : ''),
            ],
            'pipeline' => [
                'figure' => cebinova_inr($data['openValue']),
                'figureLabel' => 'open estimate',
                'reading' => $data['openCount'].' open leads still in play',
                'tone' => $data['openCount'] > 0 ? 'good' : '',
            ],
            'losses' => [
                'figure' => (string) $data['lost'],
                'figureLabel' => 'lost in this period',
                'reading' => $data['losses']->isEmpty() ? 'No lost deals in this period.' : $data['losses']->first()['label'].' is the top reason',
                'tone' => $data['lost'] > 0 ? 'alert' : '',
            ],
            'followups' => [
                'figure' => (string) $data['overdue'],
                'figureLabel' => 'overdue follow-ups',
                'reading' => $data['dueToday'].' due today',
                'tone' => $data['overdue'] > 0 ? 'alert' : '',
            ],
            'team' => $this->teamCard($data['team']),
        };

        return array_merge($summary, [
            'href' => route('admin.reports.review', ['report' => $key, ...$range->parameters()]),
            'title' => $meta['title'],
            'purpose' => $meta['purpose'],
        ]);
    }

    private function bundle(ReportRange $range): array
    {
        $total = $this->cohort($range)->count();
        $won = $this->cohort($range)->whereIn('status', self::WON)->count();
        $lost = $this->cohort($range)->where('status', 'Lost')->count();
        $previous = $range->previous();
        $previousTotal = $this->cohort($previous)->count();
        $pipeline = Lead::query()
            ->select('status')
            ->selectRaw('COUNT(*) as total, COALESCE(SUM(estimated_value), 0) as value')
            ->groupBy('status')
            ->get();
        $open = $pipeline->reject(fn ($row) => in_array($row->status, self::CLOSED, true));

        return [
            'total' => $total,
            'won' => $won,
            'lost' => $lost,
            'openInPeriod' => $total - $won - $lost,
            'winRate' => $this->rate($won, $total),
            'previousTotal' => $previousTotal,
            'change' => BusinessMetrics::change($total, $previousTotal),
            'wonValue' => (int) $this->cohort($range)->whereIn('status', self::WON)->sum('estimated_value'),
            'services' => $this->grouped($range, 'service'),
            'sources' => $this->grouped($range, 'source'),
            'cities' => $this->grouped($range, 'city'),
            'businessTypes' => $this->grouped($range, 'business_type'),
            'statuses' => $this->statuses($range),
            'pipeline' => $pipeline,
            'openCount' => (int) $open->sum('total'),
            'openValue' => (int) $open->sum('value'),
            'losses' => $this->losses($range),
            'overdue' => $this->followUpQuery()->where('follow_up_at', '<', now()->startOfDay())->count(),
            'dueToday' => $this->followUpQuery()->whereBetween('follow_up_at', [now()->startOfDay(), now()->endOfDay()])->count(),
            'team' => $this->team($range),
        ];
    }

    private function cohort(ReportRange $range)
    {
        return $range->apply(Lead::query(), 'leads.created_at');
    }

    private function grouped(ReportRange $range, string $column): Collection
    {
        abort_unless(in_array($column, ['service', 'source', 'city', 'business_type'], true), 404);

        return $this->cohort($range)
            ->select('leads.'.$column)
            ->selectRaw('COUNT(*) as total')
            ->selectRaw('SUM(CASE WHEN leads.status IN (?, ?) THEN 1 ELSE 0 END) as won', self::WON)
            ->selectRaw('SUM(CASE WHEN leads.status = ? THEN 1 ELSE 0 END) as lost', ['Lost'])
            ->selectRaw('COALESCE(SUM(CASE WHEN leads.status IN (?, ?) THEN leads.estimated_value ELSE 0 END), 0) as won_value', self::WON)
            ->groupBy('leads.'.$column)
            ->orderByDesc('total')
            ->get()
            ->map(fn ($row) => [
                'label' => filled($row->{$column}) ? (string) $row->{$column} : 'Not recorded',
                'total' => (int) $row->total,
                'won' => (int) $row->won,
                'lost' => (int) $row->lost,
                'won_value' => (int) $row->won_value,
            ])
            ->groupBy('label')
            ->map(function (Collection $rows, string $label) {
                $total = (int) $rows->sum('total');
                $won = (int) $rows->sum('won');

                return [
                    'label' => $label,
                    'total' => $total,
                    'won' => $won,
                    'lost' => (int) $rows->sum('lost'),
                    'won_value' => (int) $rows->sum('won_value'),
                    'rate' => $this->rate($won, $total),
                ];
            })
            ->sortByDesc('total')
            ->values();
    }

    private function statuses(ReportRange $range): Collection
    {
        return $this->cohort($range)
            ->select('status')
            ->selectRaw('COUNT(*) as total, COALESCE(SUM(estimated_value), 0) as value')
            ->groupBy('status')
            ->orderByDesc('total')
            ->get()
            ->map(fn ($row) => [
                'label' => (string) $row->status,
                'total' => (int) $row->total,
                'value' => (int) $row->value,
            ]);
    }

    private function losses(ReportRange $range): Collection
    {
        return $this->cohort($range)
            ->where('leads.status', 'Lost')
            ->select('leads.lost_reason')
            ->selectRaw('COUNT(*) as total, COALESCE(SUM(leads.estimated_value), 0) as value')
            ->groupBy('leads.lost_reason')
            ->orderByDesc('total')
            ->get()
            ->map(fn ($row) => [
                'label' => filled($row->lost_reason) ? (string) $row->lost_reason : 'Not recorded',
                'total' => (int) $row->total,
                'value' => (int) $row->value,
            ])
            ->groupBy('label')
            ->map(fn (Collection $rows, string $label) => [
                'label' => $label,
                'total' => (int) $rows->sum('total'),
                'value' => (int) $rows->sum('value'),
            ])
            ->sortByDesc('total')
            ->values();
    }

    private function followUpQuery()
    {
        return Lead::query()
            ->whereNotNull('follow_up_at')
            ->whereNotIn('status', self::CLOSED);
    }

    private function team(ReportRange $range): Collection
    {
        $period = $this->cohort($range)
            ->leftJoin('users', 'users.id', '=', 'leads.assigned_to')
            ->select('leads.assigned_to')
            ->selectRaw('COALESCE(MAX(users.name), ?) as person', ['Unassigned'])
            ->selectRaw('COUNT(*) as total')
            ->selectRaw('SUM(CASE WHEN leads.status IN (?, ?) THEN 1 ELSE 0 END) as won', self::WON)
            ->groupBy('leads.assigned_to')
            ->get()
            ->keyBy(fn ($row) => (string) $row->assigned_to);

        $open = Lead::query()
            ->whereNotIn('status', self::CLOSED)
            ->select('assigned_to')
            ->selectRaw('COUNT(*) as total')
            ->groupBy('assigned_to')
            ->pluck('total', 'assigned_to');

        $overdue = $this->followUpQuery()
            ->where('follow_up_at', '<', now()->startOfDay())
            ->select('assigned_to')
            ->selectRaw('COUNT(*) as total')
            ->groupBy('assigned_to')
            ->pluck('total', 'assigned_to');

        $keys = $period->keys()->merge($open->keys()->map(fn ($id) => (string) $id))->unique();
        $names = User::query()->whereIn('id', $keys->filter(fn ($id) => $id !== '')->all())->pluck('name', 'id');

        return $keys->map(function ($key) use ($period, $open, $overdue, $names) {
            $row = $period->get($key);
            $id = $key === '' ? null : (int) $key;

            return [
                'person' => $row->person ?? ($id ? ($names[$id] ?? 'Former user') : 'Unassigned'),
                'total' => (int) ($row->total ?? 0),
                'won' => (int) ($row->won ?? 0),
                'open' => (int) ($open[$id] ?? $open[$key] ?? 0),
                'overdue' => (int) ($overdue[$id] ?? $overdue[$key] ?? 0),
            ];
        })->sortByDesc(fn (array $row) => ($row['total'] * 100000) + $row['open'])->values();
    }

    private function growthReview(array $data): array
    {
        $reading = match (true) {
            $data['total'] === 0 => 'No enquiries in this period, so there is nothing yet to judge growth on.',
            $data['change'] === null => 'This period has enquiries, but the previous one does not, so a growth rate would be misleading.',
            $data['change'] >= 15 && ($data['winRate'] ?? 0) < 20 => 'Enquiries are up, and few of them are closing. Clear follow-ups and review the offer before you add more demand.',
            $data['change'] >= 15 => 'Enquiries are up on the previous period. Check team capacity before you promote further.',
            $data['change'] <= -15 => 'Enquiries are down on the previous period. Review the sources that usually bring work before you spend on a new offer.',
            default => 'Enquiry volume is close to the previous period. Use service and source reports to decide what to push.',
        };

        return [
            'reading' => $reading,
            'stats' => [
                ['label' => 'Enquiries', 'value' => (string) $data['total']],
                ['label' => 'Previous period', 'value' => (string) $data['previousTotal']],
                ['label' => 'Won', 'value' => (string) $data['won']],
                ['label' => 'Win rate', 'value' => $this->rateLabel($data['winRate'])],
            ],
            'tables' => [$this->statusTable($data, 'Where this period’s enquiries sit')],
        ];
    }

    private function winsReview(array $data): array
    {
        $reading = match (true) {
            $data['total'] === 0 => 'No enquiries in this period.',
            ($data['winRate'] ?? 0) >= 30 => 'A solid share of this period’s enquiries closed. The pipeline report shows whether enough new work is still coming.',
            $data['total'] >= 3 && ($data['winRate'] ?? 0) < 15 => 'Most enquiries are not closing. Read lost reasons and overdue follow-ups before you change prices or hire.',
            default => 'Win rate is the share of this period’s enquiries marked won. Lost reasons explain the ones that did not close.',
        };

        return [
            'reading' => $reading,
            'stats' => [
                ['label' => 'Win rate', 'value' => $this->rateLabel($data['winRate'])],
                ['label' => 'Won', 'value' => (string) $data['won']],
                ['label' => 'Lost', 'value' => (string) $data['lost']],
                ['label' => 'Won estimate', 'value' => cebinova_inr($data['wonValue'])],
            ],
            'tables' => [$this->statusTable($data, 'Outcome of enquiries created in this period')],
        ];
    }

    private function pipelineReview(array $data): array
    {
        $rows = $data['pipeline']->map(fn ($row) => [
            $row->status,
            (string) (int) $row->total,
            cebinova_inr((int) $row->value),
        ])->all();

        return [
            'reading' => $data['openCount'] === 0
                ? 'Nothing is open in the pipeline.'
                : $data['openCount'].' leads are still open, estimated at '.cebinova_inr($data['openValue']).'. That figure is not collected revenue. Move stalled stages before you take on a bigger offer.',
            'note' => 'This is the live pipeline, not only leads created in the selected period.',
            'stats' => [
                ['label' => 'Open leads', 'value' => (string) $data['openCount']],
                ['label' => 'Open estimate', 'value' => cebinova_inr($data['openValue'])],
            ],
            'tables' => [[
                'title' => 'Every current stage',
                'columns' => ['Stage', 'Leads', 'Estimated value'],
                'rows' => $rows,
                'empty' => 'No leads on record.',
            ]],
        ];
    }

    private function lossesReview(array $data): array
    {
        $top = $data['losses']->first();

        return [
            'reading' => $top === null
                ? 'No lost deals in this period.'
                : $top['label'].' is the most common loss. If budget or timing dominates, adjust the package before you add a new service.',
            'stats' => [
                ['label' => 'Lost deals', 'value' => (string) $data['lost']],
                ['label' => 'Top reason', 'value' => $top['label'] ?? '—'],
            ],
            'tables' => [[
                'title' => 'Why deals were lost',
                'columns' => ['Reason', 'Deals', 'Estimated value'],
                'rows' => $data['losses']->map(fn (array $row) => [$row['label'], (string) $row['total'], cebinova_inr($row['value'])])->all(),
                'empty' => 'No lost deals in this period.',
            ]],
        ];
    }

    private function followupsReview(): array
    {
        $rows = $this->followUpQuery()
            ->where('follow_up_at', '<', now()->startOfDay())
            ->orderBy('follow_up_at')
            ->limit(30)
            ->get(['id', 'name', 'service', 'status', 'follow_up_at']);
        $overdue = $this->followUpQuery()->where('follow_up_at', '<', now()->startOfDay())->count();
        $dueToday = $this->followUpQuery()->whereBetween('follow_up_at', [now()->startOfDay(), now()->endOfDay()])->count();

        return [
            'reading' => $overdue > 0
                ? $overdue.' follow-ups are overdue. Work these before you spend more to bring in new enquiries.'
                : ($dueToday > 0 ? 'Nothing is overdue. '.$dueToday.' are due today.' : 'Follow-ups are clear.'),
            'note' => 'Overdue and due-today counts are live. They are not limited to the selected period.',
            'stats' => [
                ['label' => 'Overdue', 'value' => (string) $overdue],
                ['label' => 'Due today', 'value' => (string) $dueToday],
            ],
            'tables' => [[
                'title' => 'Oldest overdue follow-ups',
                'columns' => ['Lead', 'Service', 'Status', 'Follow-up'],
                'rows' => $rows->map(fn (Lead $lead) => [
                    ['text' => $lead->name, 'url' => route('admin.leads.show', $lead)],
                    $lead->service ?: 'Not recorded',
                    $lead->status,
                    $lead->follow_up_at?->format('d M Y H:i') ?? '—',
                ])->all(),
                'empty' => 'No overdue follow-ups.',
            ]],
        ];
    }

    private function teamReview(Collection $team): array
    {
        $busiest = $team->sortByDesc('total')->first();
        $unassigned = $team->firstWhere('person', 'Unassigned');
        $reading = match (true) {
            $team->isEmpty() => 'No enquiries or open leads to assign yet.',
            ($unassigned['total'] ?? 0) > 0 => 'Some enquiries have no owner. Assign them before you judge team capacity.',
            default => $busiest['person'].' has the most new enquiries in this period. Compare that with open leads before you move more work to them.',
        };

        return [
            'reading' => $reading,
            'stats' => [
                ['label' => 'People in view', 'value' => (string) $team->count()],
                ['label' => 'Unassigned enquiries', 'value' => (string) ($unassigned['total'] ?? 0)],
            ],
            'tables' => [[
                'title' => 'New work in this period, with the live open load',
                'columns' => ['Person', 'New enquiries', 'Won', 'Still open', 'Overdue follow-ups'],
                'rows' => $team->map(fn (array $row) => [$row['person'], (string) $row['total'], (string) $row['won'], (string) $row['open'], (string) $row['overdue']])->all(),
                'empty' => 'No assigned work to show.',
            ]],
        ];
    }

    private function marketsReview(array $data): array
    {
        $top = $data['cities']->first();

        return [
            'reading' => $top === null
                ? 'No city data in this period.'
                : $top['label'].' accounts for the most enquiries. Use that when you decide which city to visit or advertise in.',
            'stats' => [
                ['label' => 'Cities', 'value' => (string) $data['cities']->count()],
                ['label' => 'Top city', 'value' => $top['label'] ?? '—'],
            ],
            'tables' => [
                $this->simpleTable($data['cities'], 'City', 'Enquiries by city'),
                $this->simpleTable($data['businessTypes'], 'Business type', 'Enquiries by business type'),
            ],
        ];
    }

    private function breakdownReview(Collection $rows, string $heading, string $empty, string $reading): array
    {
        $top = $rows->first();

        return [
            'reading' => $reading,
            'stats' => [
                ['label' => $heading.'s', 'value' => (string) $rows->count()],
                ['label' => 'Leading', 'value' => $top['label'] ?? '—'],
                ['label' => 'Its win rate', 'value' => $this->rateLabel($top['rate'] ?? null)],
            ],
            'tables' => [[
                'title' => $heading.'s in this period',
                'columns' => [$heading, 'Enquiries', 'Won', 'Lost', 'Win rate', 'Won estimate'],
                'rows' => $rows->map(fn (array $row) => [
                    $row['label'],
                    (string) $row['total'],
                    (string) $row['won'],
                    (string) $row['lost'],
                    $this->rateLabel($row['rate']),
                    cebinova_inr($row['won_value']),
                ])->all(),
                'empty' => $empty,
            ]],
        ];
    }

    private function statusTable(array $data, string $title): array
    {
        return [
            'title' => $title,
            'columns' => ['Stage', 'Enquiries', 'Share', 'Estimated value'],
            'rows' => $data['statuses']->map(fn (array $row) => [
                $row['label'],
                (string) $row['total'],
                $this->rateLabel($this->rate($row['total'], $data['total'])),
                cebinova_inr($row['value']),
            ])->all(),
            'empty' => 'No enquiries in this period.',
        ];
    }

    private function simpleTable(Collection $rows, string $heading, string $title): array
    {
        return [
            'title' => $title,
            'columns' => [$heading, 'Enquiries', 'Won', 'Win rate'],
            'rows' => $rows->map(fn (array $row) => [$row['label'], (string) $row['total'], (string) $row['won'], $this->rateLabel($row['rate'])])->all(),
            'empty' => 'Nothing recorded in this period.',
        ];
    }

    private function leaderCard(Collection $rows, string $unit, string $empty): array
    {
        $top = $rows->first();
        if ($top === null) {
            return ['figure' => '—', 'figureLabel' => $unit, 'reading' => $empty, 'tone' => ''];
        }
        $share = $rows->sum('total') > 0 ? (int) round(($top['total'] / $rows->sum('total')) * 100) : 0;

        return [
            'figure' => $top['label'],
            'figureLabel' => $top['total'].' '.$unit,
            'reading' => $share.'% of this period',
            'tone' => '',
        ];
    }

    private function teamCard(Collection $team): array
    {
        $busiest = $team->sortByDesc('open')->first();
        if ($busiest === null) {
            return ['figure' => '—', 'figureLabel' => 'open leads', 'reading' => 'No open workload yet.', 'tone' => ''];
        }
        $unassigned = $team->contains(fn (array $row) => $row['person'] === 'Unassigned' && ($row['total'] > 0 || $row['open'] > 0));

        return [
            'figure' => (string) $busiest['open'],
            'figureLabel' => 'open with '.$busiest['person'],
            'reading' => $unassigned ? 'Some enquiries are still unassigned.' : 'Everyone in view has an owner.',
            'tone' => $unassigned ? 'alert' : '',
        ];
    }

    private function serviceReading(Collection $rows): string
    {
        $top = $rows->first();
        if ($top === null) {
            return 'No service demand in this period.';
        }
        $weak = $rows->first(fn (array $row) => $row['total'] >= 2 && ($row['rate'] ?? 0) < 25);
        if ($weak) {
            return $weak['label'].' brings enquiries but few close. Review that offer before you promote it.';
        }

        return $top['label'].' leads demand in this period. Compare its win rate with the others before you invest in it.';
    }

    private function sourceReading(Collection $rows): string
    {
        $top = $rows->first();
        if ($top === null) {
            return 'No source data in this period.';
        }

        return $top['label'].' brought the most enquiries. Put more effort there only if its win rate holds up against the other sources.';
    }

    private function rate(int $part, int $whole): ?float
    {
        return $whole > 0 ? round(100 * $part / $whole, 1) : null;
    }

    private function rateLabel(?float $rate): string
    {
        return $rate === null ? '—' : $rate.'%';
    }

    private function changeLabel(?float $change): string
    {
        if ($change === null) {
            return 'No earlier period to compare';
        }

        return ($change > 0 ? '+' : '').$change.'% vs the previous period';
    }
}
