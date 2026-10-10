<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphTo;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\Route;

class ActivityLog extends Model
{
    protected $fillable = [
        'user_id',
        'action',
        'module',
        'subject_type',
        'subject_id',
        'description',
        'properties',
        'ip_address',
        'user_agent',
        'location',
    ];

    protected function casts(): array
    {
        return [
            'properties' => 'array',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function subject(): MorphTo
    {
        return $this->morphTo();
    }

    public function device(): ?string
    {
        $agent = (string) $this->user_agent;
        if ($agent === '') {
            return null;
        }
        $browser = match (true) {
            str_contains($agent, 'Edg/') => 'Edge',
            str_contains($agent, 'Chrome/') => 'Chrome',
            str_contains($agent, 'Firefox/') => 'Firefox',
            str_contains($agent, 'Safari/') => 'Safari',
            default => null,
        };
        $system = match (true) {
            str_contains($agent, 'Windows') => 'Windows',
            str_contains($agent, 'Mac OS') => 'Mac',
            str_contains($agent, 'Android') => 'Android',
            str_contains($agent, 'iPhone') => 'iPhone',
            str_contains($agent, 'Linux') => 'Linux',
            default => null,
        };
        $label = collect([$browser, $system])->filter()->implode(' · ');

        return $label !== '' ? $label : null;
    }

    /**
     * @return array{mode: string, rows: list<array{label: string, before: string, after: string}>, page: ?string}
     */
    public function story(): array
    {
        $props = is_array($this->properties) ? $this->properties : [];
        $page = is_string($props['page'] ?? null) ? $props['page'] : null;
        if (is_array($props['removed'] ?? null) && $props['removed'] !== []) {
            return [
                'mode' => 'remove',
                'rows' => $this->rows($props['removed'], []),
                'page' => $page,
            ];
        }

        $before = is_array($props['old'] ?? null) ? $props['old'] : (is_array($props['before'] ?? null) ? $props['before'] : []);
        $after = is_array($props['new'] ?? null) ? $props['new'] : (is_array($props['after'] ?? null) ? $props['after'] : []);
        if ($before === [] && $after === []) {
            return ['mode' => 'note', 'rows' => [], 'page' => $page];
        }

        return [
            'mode' => $before === [] ? 'add' : 'edit',
            'rows' => $this->rows($before, $after),
            'page' => $page,
        ];
    }

    /**
     * @return array{url: string, label: string}|null
     */
    public function recordLink(): ?array
    {
        if ($this->module === 'settings' && Route::has('admin.settings.edit')) {
            return ['url' => route('admin.settings.edit'), 'label' => 'Open settings'];
        }
        if ($this->module === 'profile' && Route::has('admin.profile.edit')) {
            return ['url' => route('admin.profile.edit'), 'label' => 'Open profile'];
        }

        $map = [
            'page-sections' => ['admin.page-sections.edit', 'page_section'],
            'solutions' => ['admin.solutions.edit', 'solution'],
            'services' => ['admin.services.edit', 'service'],
            'industries' => ['admin.industries.edit', 'industry'],
            'packages' => ['admin.packages.edit', 'package'],
            'pricing_options' => ['admin.pricing-options.edit', 'pricingOption'],
            'demos' => ['admin.demos.edit', 'record'],
            'users' => ['admin.users.edit', 'user'],
            'leads' => ['admin.leads.show', 'lead'],
        ];
        if (! isset($map[$this->module]) || ! $this->subject_id || ! Route::has($map[$this->module][0]) || ! $this->subjectStillExists()) {
            return null;
        }

        [$name, $param] = $map[$this->module];

        return [
            'url' => route($name, [$param => $this->subject_id]),
            'label' => $this->action === 'create' ? 'Open new record' : 'Edit record',
        ];
    }

    /**
     * @param  array<string, mixed>  $before
     * @param  array<string, mixed>  $after
     * @return list<array{label: string, before: string, after: string}>
     */
    private function rows(array $before, array $after): array
    {
        $rows = [];
        foreach (array_unique([...array_keys($before), ...array_keys($after)]) as $key) {
            $left = $this->showValue($before[$key] ?? null);
            $right = $this->showValue($after[$key] ?? null);
            if ($left === $right) {
                continue;
            }
            $rows[] = [
                'label' => ucwords(str_replace(['_', '-'], ' ', (string) $key)),
                'before' => $left,
                'after' => $right,
            ];
        }

        return $rows;
    }

    private function showValue(mixed $value): string
    {
        if ($value === null || $value === '') {
            return '—';
        }
        if (is_bool($value)) {
            return $value ? 'Yes' : 'No';
        }
        if (is_array($value)) {
            return (string) json_encode($value, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
        }

        return (string) $value;
    }

    private function subjectStillExists(): bool
    {
        if (! is_string($this->subject_type) || ! class_exists($this->subject_type) || ! is_subclass_of($this->subject_type, Model::class)) {
            return false;
        }

        $query = in_array(SoftDeletes::class, class_uses_recursive($this->subject_type), true)
            ? $this->subject_type::withTrashed()
            : $this->subject_type::query();

        return $query->whereKey($this->subject_id)->exists();
    }
}
