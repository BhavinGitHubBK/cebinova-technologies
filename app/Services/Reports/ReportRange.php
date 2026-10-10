<?php

namespace App\Services\Reports;

use Carbon\CarbonImmutable;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;

class ReportRange
{
    public function __construct(public CarbonImmutable $from, public CarbonImmutable $to) {}

    public static function fromRequest(Request $request): self
    {
        $data = $request->validate(['range' => ['nullable', Rule::in(['today', 'last7', 'last30', 'month', 'last_month', 'quarter', 'year', 'custom'])],
            'from' => 'nullable|date_format:Y-m-d', 'to' => 'nullable|date_format:Y-m-d|after_or_equal:from']);
        $today = CarbonImmutable::today(config('app.timezone'));
        $preset = $data['range'] ?? (isset($data['from']) || isset($data['to']) ? 'custom' : 'month');
        [$from, $to] = match ($preset) {
            'today' => [$today, $today], 'last7' => [$today->subDays(6), $today], 'last30' => [$today->subDays(29), $today],
            'last_month' => [$today->subMonthNoOverflow()->startOfMonth(), $today->subMonthNoOverflow()->endOfMonth()->startOfDay()],
            'quarter' => [$today->startOfQuarter(), $today], 'year' => [$today->startOfYear(), $today],
            'custom' => [isset($data['from']) ? CarbonImmutable::parse($data['from'], config('app.timezone')) : null,
                isset($data['to']) ? CarbonImmutable::parse($data['to'], config('app.timezone')) : null],
            default => [$today->startOfMonth(), $today],
        };
        if (! $from || ! $to || $from->gt($to) || $from->diffInDays($to) > 365) {
            throw ValidationException::withMessages(['range' => 'Select both dates in order, covering at most 366 days.']);
        }

        return new self($from, $to);
    }

    public function apply($query, string $column, bool $dateOnly = false)
    {
        return $dateOnly ? $query->whereBetween($column, [$this->from->toDateString(), $this->to->toDateString()])
            : $query->where($column, '>=', $this->from->startOfDay())->where($column, '<', $this->to->addDay()->startOfDay());
    }

    public function previous(): self
    {
        $days = (int) $this->from->diffInDays($this->to) + 1;

        return new self($this->from->subDays($days), $this->from->subDay());
    }

    public function parameters(): array
    {
        return ['range' => 'custom', 'from' => $this->from->toDateString(), 'to' => $this->to->toDateString()];
    }
}
