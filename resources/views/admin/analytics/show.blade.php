@extends('layouts.admin')

@section('title', $visitor->label())
@section('breadcrumb', 'Admin / Visitors / '.$visitor->label())
@section('heading', $visitor->label())

@section('content')
@php
    $named = filled($visitor->name);
    $nameParts = $named ? preg_split('/\s+/', trim($visitor->name)) : [];
    $initials = $named
        ? collect($nameParts)->filter()->take(2)->map(fn ($word) => mb_strtoupper(mb_substr($word, 0, 1)))->implode('')
        : 'V';
    $pageCount = $visitor->pageviews->count();
    $visitCount = $visitor->visits->count();
@endphp
<section class="visitor-detail">
    <p class="visitor-back"><a href="{{ route('admin.analytics.index') }}">All visitors</a></p>

    <header class="admin-card visitor-hero">
        <span class="visitor-hero-mark @if($named) is-named @endif" aria-hidden="true">{{ $initials }}</span>
        <div class="visitor-hero-copy">
            <h2>{{ $visitor->label() }}</h2>
            <p>{{ $visitor->mobileDisplay() }}</p>
            <p class="visitor-place">
                <span>{{ $visitor->place() }}</span>
                @if ($visitor->mapUrl())
                    <a class="visitor-map" href="{{ $visitor->mapUrl() }}" target="_blank" rel="noopener">Map</a>
                @endif
            </p>
        </div>
    </header>

    <dl class="visitor-stats">
        <div><dt>Visits</dt><dd>{{ $visitCount }}</dd></div>
        <div><dt>Pages</dt><dd>{{ $pageCount }}</dd></div>
        <div><dt>Opened</dt><dd>{{ page_label($visitor->first_landing_path) }}</dd></div>
        <div><dt>Last page</dt><dd>{{ page_label($visitor->exit_path) }}</dd></div>
        <div><dt>Came from</dt><dd>{{ $visitor->cameFrom() }}</dd></div>
        <div><dt>Last seen</dt><dd>{{ ist($visitor->last_seen_at) }}</dd></div>
    </dl>

    <dl class="visitor-meta">
        <div><dt>First seen</dt><dd>{{ ist($visitor->first_seen_at, 'd M Y, h:i A') }}</dd></div>
        <div><dt>Accuracy</dt><dd>{{ $visitor->accuracyLabel() ?: '—' }}</dd></div>
        <div><dt>IP address</dt><dd>{{ $visitor->ip_address ?: '—' }}</dd></div>
        <div><dt>Pin</dt><dd>{{ $visitor->latitude !== null && $visitor->longitude !== null ? $visitor->latitude.', '.$visitor->longitude : '—' }}</dd></div>
        <div class="is-wide"><dt>Source link</dt><dd>{{ $visitor->referrer_url ?: '—' }}</dd></div>
    </dl>

    <style>
        .visitor-timeline li.is-account strong { color: #0b2c5f; }
        .visitor-timeline li.is-login .visitor-timeline-mark,
        .visitor-visit.is-latest .visitor-timeline li.is-login:first-child .visitor-timeline-mark { background: #15803d; box-shadow: 0 0 0 4px #dcfce7; }
        .visitor-timeline li.is-logout .visitor-timeline-mark,
        .visitor-visit.is-latest .visitor-timeline li.is-logout:first-child .visitor-timeline-mark { background: #b91c1c; box-shadow: 0 0 0 4px #fee2e2; }
        .visitor-timeline li.is-login strong { color: #15803d; }
        .visitor-timeline li.is-logout strong { color: #b91c1c; }
    </style>

    <div class="visitor-history">
        <h2 class="visitor-history-title">Activity</h2>
        @php
            $recentPageLimit = 6;
            $visibleEarlierVisits = 3;
            $latestVisit = $visitor->visits->first();
            $earlierVisits = $visitor->visits->slice(1)->values();
        @endphp

        @if ($latestVisit)
            @php
                $recentPages = $latestVisit->timeline->take($recentPageLimit);
                $olderPages = $latestVisit->timeline->slice($recentPageLimit)->values();
            @endphp
            <article class="admin-card visitor-visit is-latest">
                <header>
                    <p class="visitor-visit-kicker">Latest visit</p>
                    <h2>{{ ist($latestVisit->last_seen_at, 'd M Y, h:i A') }}</h2>
                    <p>Started {{ ist($latestVisit->started_at, 'h:i A') }} · Opened {{ page_label($latestVisit->landing_path) }} · Last page {{ page_label($latestVisit->exit_path) }} · {{ $latestVisit->cameFrom() }} · {{ $latestVisit->pageviews->count() }} {{ \Illuminate\Support\Str::plural('page', $latestVisit->pageviews->count()) }}</p>
                </header>
                @include('admin.analytics.partials.timeline', ['views' => $recentPages])
                @if ($olderPages->isNotEmpty())
                    <details class="visitor-more">
                        <summary>
                            <span class="is-closed">Show {{ $olderPages->count() }} earlier {{ \Illuminate\Support\Str::plural('item', $olderPages->count()) }}</span>
                            <span class="is-open">Hide earlier items</span>
                        </summary>
                        @include('admin.analytics.partials.timeline', ['views' => $olderPages])
                    </details>
                @endif
            </article>

            @foreach ($earlierVisits->take($visibleEarlierVisits) as $visit)
                @include('admin.analytics.partials.visit', ['visit' => $visit])
            @endforeach

            @if ($earlierVisits->count() > $visibleEarlierVisits)
                <details class="admin-card visitor-older">
                    <summary>Show {{ $earlierVisits->count() - $visibleEarlierVisits }} older {{ \Illuminate\Support\Str::plural('visit', $earlierVisits->count() - $visibleEarlierVisits) }}</summary>
                    <div class="visitor-older-list">
                        @foreach ($earlierVisits->slice($visibleEarlierVisits) as $visit)
                            @include('admin.analytics.partials.visit', ['visit' => $visit])
                        @endforeach
                    </div>
                </details>
            @endif
        @else
            @php
                $recentPages = $visitor->timeline->take($recentPageLimit);
                $olderPages = $visitor->timeline->slice($recentPageLimit)->values();
            @endphp
            <article class="admin-card visitor-visit is-latest">
                <header>
                    <p class="visitor-visit-kicker">Pages</p>
                    <h2>{{ $pageCount }} {{ \Illuminate\Support\Str::plural('page', $pageCount) }}</h2>
                </header>
                @include('admin.analytics.partials.timeline', ['views' => $recentPages])
                @if ($olderPages->isNotEmpty())
                    <details class="visitor-more">
                        <summary>
                            <span class="is-closed">Show {{ $olderPages->count() }} earlier {{ \Illuminate\Support\Str::plural('item', $olderPages->count()) }}</span>
                            <span class="is-open">Hide earlier items</span>
                        </summary>
                        @include('admin.analytics.partials.timeline', ['views' => $olderPages])
                    </details>
                @endif
            </article>
        @endif
    </div>
</section>
@endsection
