<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\AnalyticsAccountEvent;
use App\Models\AnalyticsVisitor;
use App\Models\Customer;
use App\Services\Analytics\VisitorCapture;
use Illuminate\Http\Request;
use Illuminate\View\View;

class AnalyticsController extends Controller
{
    public function index(Request $request): View
    {
        $search = trim((string) $request->query('q', ''));

        $visitors = AnalyticsVisitor::query()
            ->when($search !== '', function ($query) use ($search) {
                $numberedId = AnalyticsVisitor::idFromNumberedName($search);
                $query->where(function ($inner) use ($search, $numberedId) {
                    $inner->where('name', 'like', '%'.$search.'%')
                        ->when($numberedId, fn ($match) => $match->orWhere(fn ($unnamed) => $unnamed->whereKey($numberedId)->whereNull('name')))
                        ->orWhere('mobile', 'like', '%'.$search.'%')
                        ->orWhere('city', 'like', '%'.$search.'%')
                        ->orWhere('first_referrer_domain', 'like', '%'.$search.'%');
                });
            })
            ->orderByDesc('last_seen_at')
            ->paginate(25)
            ->withQueryString();

        app(VisitorCapture::class)->fillMissingLocalPlaces($visitors->getCollection());
        app(VisitorCapture::class)->fillMissingAddresses($visitors->getCollection());

        return view('admin.analytics.index', [
            'visitors' => $visitors,
            'search' => $search,
            'today' => AnalyticsVisitor::query()->where('last_seen_at', '>=', now()->startOfDay())->count(),
            'named' => AnalyticsVisitor::query()->whereNotNull('name')->whereNotNull('mobile')->count(),
        ]);
    }

    public function show(AnalyticsVisitor $visitor): View
    {
        app(VisitorCapture::class)->fillMissingAddresses([$visitor]);

        $visitor->load([
            'visits' => fn ($query) => $query->orderByDesc('last_seen_at')->with(['pageviews' => fn ($pages) => $pages->orderByDesc('viewed_at')]),
            'pageviews' => fn ($query) => $query->orderByDesc('viewed_at'),
        ]);

        $accountEvents = AnalyticsAccountEvent::query()
            ->where(function ($query) use ($visitor) {
                $query->where('analytics_visitor_id', $visitor->id);
                if (filled($visitor->mobile)) {
                    $query->orWhereIn('customer_id', Customer::query()->where('mobile', $visitor->mobile)->select('id'));
                }
            })
            ->latest('occurred_at')
            ->latest('id')
            ->get();

        $byVisit = [];
        $unplaced = collect();
        foreach ($accountEvents as $event) {
            $visit = $visitor->visits
                ->filter(fn ($visit) => $visit->started_at && $visit->started_at->lte($event->occurred_at))
                ->sortByDesc('started_at')
                ->first();
            if ($visit) {
                $byVisit[$visit->id][] = $event;
            } else {
                $unplaced->push($event);
            }
        }

        $merge = fn ($pages, $events) => collect($pages)
            ->concat($events)
            ->sortByDesc(fn ($item) => ($item instanceof AnalyticsAccountEvent ? $item->occurred_at : $item->viewed_at)?->getTimestamp() ?? 0)
            ->values();

        foreach ($visitor->visits as $index => $visit) {
            $events = $byVisit[$visit->id] ?? [];
            if ($index === $visitor->visits->count() - 1) {
                $events = array_merge($events, $unplaced->all());
            }
            $visit->setRelation('timeline', $merge($visit->pageviews, $events));
        }
        $visitor->setRelation('timeline', $merge($visitor->pageviews, $accountEvents));

        return view('admin.analytics.show', [
            'visitor' => $visitor,
        ]);
    }
}
