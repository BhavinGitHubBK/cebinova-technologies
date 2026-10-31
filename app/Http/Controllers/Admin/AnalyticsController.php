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
        $visitorType = in_array($request->query('type'), ['named', 'anonymous'], true)
            ? (string) $request->query('type')
            : '';
        $city = trim((string) $request->query('city', ''));
        $source = trim((string) $request->query('source', ''));
        $lastSeen = in_array($request->query('last_seen'), ['today', '7_days', '30_days'], true)
            ? (string) $request->query('last_seen')
            : '';

        $cities = AnalyticsVisitor::query()
            ->whereNotNull('city')
            ->where('city', '<>', '')
            ->distinct()
            ->orderBy('city')
            ->pluck('city');
        $sources = AnalyticsVisitor::query()
            ->whereNotNull('first_referrer_domain')
            ->where('first_referrer_domain', '<>', '')
            ->distinct()
            ->orderBy('first_referrer_domain')
            ->pluck('first_referrer_domain');
        $hasDirectSource = AnalyticsVisitor::query()
            ->where(fn ($query) => $query->whereNull('first_referrer_domain')->orWhere('first_referrer_domain', ''))
            ->exists();

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
            ->when($visitorType === 'named', fn ($query) => $query
                ->whereNotNull('name')->where('name', '<>', '')
                ->whereNotNull('mobile')->where('mobile', '<>', ''))
            ->when($visitorType === 'anonymous', fn ($query) => $query->where(function ($anonymous) {
                $anonymous->whereNull('name')->orWhere('name', '')
                    ->orWhereNull('mobile')->orWhere('mobile', '');
            }))
            ->when($city !== '', fn ($query) => $query->where('city', $city))
            ->when($source === '__direct', fn ($query) => $query->where(function ($direct) {
                $direct->whereNull('first_referrer_domain')->orWhere('first_referrer_domain', '');
            }))
            ->when($source !== '' && $source !== '__direct', fn ($query) => $query->where('first_referrer_domain', $source))
            ->when($lastSeen === 'today', fn ($query) => $query->where('last_seen_at', '>=', now()->startOfDay()))
            ->when($lastSeen === '7_days', fn ($query) => $query->where('last_seen_at', '>=', now()->subDays(7)))
            ->when($lastSeen === '30_days', fn ($query) => $query->where('last_seen_at', '>=', now()->subDays(30)))
            ->orderByDesc('last_seen_at')
            ->paginate(25)
            ->withQueryString();

        app(VisitorCapture::class)->fillMissingLocalPlaces($visitors->getCollection());
        app(VisitorCapture::class)->fillMissingAddresses($visitors->getCollection());

        return view('admin.analytics.index', [
            'visitors' => $visitors,
            'search' => $search,
            'visitorType' => $visitorType,
            'city' => $city,
            'source' => $source,
            'lastSeen' => $lastSeen,
            'cities' => $cities,
            'sources' => $sources,
            'hasDirectSource' => $hasDirectSource,
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
