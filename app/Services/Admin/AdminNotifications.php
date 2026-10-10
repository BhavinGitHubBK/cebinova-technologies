<?php

namespace App\Services\Admin;

use App\Models\Lead;
use Carbon\CarbonInterface;
use Illuminate\Support\Carbon;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Schema;

class AdminNotifications
{
    public function current(): array
    {
        $seenAt = session('admin.notifications_seen_at');
        $seen = $seenAt ? Carbon::parse($seenAt) : null;
        $items = $this->items();

        return [
            'items' => $items->sortByDesc(fn (array $item) => $item['time'])->take(8)->values(),
            'unread' => $items->filter(fn (array $item) => $this->isUnread($item['time'], $seen))->count(),
        ];
    }

    public function markSeen(): void
    {
        session(['admin.notifications_seen_at' => now()->toIso8601String()]);
    }

    /**
     * @return Collection<int, array{title: string, detail: string, time: CarbonInterface, url: string}>
     */
    private function items(): Collection
    {
        $items = collect();

        if (auth()->user()?->can('admin.leads.view') && Schema::hasTable('leads')) {
            Lead::query()
                ->where('status', Lead::STATUS_NEW)
                ->latest()
                ->limit(8)
                ->get()
                ->each(function (Lead $lead) use ($items) {
                    $detail = collect([$lead->business_name, $lead->service])->filter()->implode(' · ');
                    $items->push([
                        'title' => $lead->name,
                        'detail' => $detail !== '' ? $detail : 'New enquiry',
                        'time' => $lead->created_at,
                        'url' => route('admin.leads.show', $lead),
                    ]);
                });
        }

        return $items;
    }

    private function isUnread(?CarbonInterface $time, ?CarbonInterface $seen): bool
    {
        if (! $time) {
            return false;
        }

        if ($seen) {
            return $time->greaterThan($seen);
        }

        return $time->greaterThanOrEqualTo(now()->subDay());
    }
}
