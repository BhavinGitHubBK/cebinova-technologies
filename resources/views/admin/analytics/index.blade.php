@extends('layouts.admin')

@section('title', 'Visitors')
@section('breadcrumb', 'Admin / Visitors')
@section('heading', 'Website visitors')

@section('content')
<section class="lead-board">
    <div class="lead-summary">
        <p><strong>{{ $today }}</strong><span>opened the site today</span></p>
        <p><strong>{{ $named }}</strong><span>left a name and mobile</span></p>
        <p><strong>{{ $visitors->total() }}</strong><span>in this list</span></p>
    </div>

    <div class="admin-card" style="margin-bottom:1rem;">
        <form method="GET" action="{{ route('admin.analytics.index') }}" class="solution-search analytics-filter-form">
            <div class="admin-field analytics-search-field">
                <label class="admin-label" for="visitor-search">Search</label>
                <input id="visitor-search" class="admin-input" name="q" value="{{ $search }}" placeholder="Name, mobile, city, or website">
            </div>
            <div class="admin-field">
                <label class="admin-label" for="visitor-type">Visitor type</label>
                <select id="visitor-type" class="admin-select" name="type">
                    <option value="">All visitors</option>
                    <option value="named" @selected($visitorType === 'named')>Named</option>
                    <option value="anonymous" @selected($visitorType === 'anonymous')>Anonymous</option>
                </select>
            </div>
            <div class="admin-field">
                <label class="admin-label" for="visitor-city">City</label>
                <select id="visitor-city" class="admin-select" name="city">
                    <option value="">All cities</option>
                    @foreach ($cities as $cityOption)
                        <option value="{{ $cityOption }}" @selected($city === $cityOption)>{{ $cityOption }}</option>
                    @endforeach
                </select>
            </div>
            <div class="admin-field">
                <label class="admin-label" for="visitor-source">Came from</label>
                <select id="visitor-source" class="admin-select" name="source">
                    <option value="">All sources</option>
                    @if ($hasDirectSource)
                        <option value="__direct" @selected($source === '__direct')>Direct</option>
                    @endif
                    @foreach ($sources as $sourceOption)
                        <option value="{{ $sourceOption }}" @selected($source === $sourceOption)>{{ $sourceOption }}</option>
                    @endforeach
                </select>
            </div>
            <div class="admin-field">
                <label class="admin-label" for="visitor-last-seen">Last seen</label>
                <select id="visitor-last-seen" class="admin-select" name="last_seen">
                    <option value="">Any time</option>
                    <option value="today" @selected($lastSeen === 'today')>Today</option>
                    <option value="7_days" @selected($lastSeen === '7_days')>Last 7 days</option>
                    <option value="30_days" @selected($lastSeen === '30_days')>Last 30 days</option>
                </select>
            </div>
            <div class="analytics-filter-actions">
                <button class="admin-btn" type="submit">Apply filters</button>
            @if ($search !== '' || $visitorType !== '' || $city !== '' || $source !== '' || $lastSeen !== '')
                <a class="admin-btn admin-btn-ghost" href="{{ route('admin.analytics.index') }}">Clear</a>
            @endif
            </div>
        </form>
    </div>

    <div class="admin-card admin-table-wrap">
        <table class="admin-table lead-table visitor-table analytics-visitor-table">
            <colgroup>
                <col class="analytics-visitor-name">
                <col class="analytics-visitor-mobile">
                <col class="analytics-visitor-location">
                <col class="analytics-visitor-source">
                <col class="analytics-visitor-seen">
            </colgroup>
            <thead>
                <tr>
                    <th>Name</th>
                    <th>Mobile</th>
                    <th>Location</th>
                    <th>Came from</th>
                    <th>Last seen</th>
                </tr>
            </thead>
            <tbody>
                @forelse ($visitors as $visitor)
                    <tr>
                        <td><a href="{{ route('admin.analytics.visitors.show', $visitor) }}">{{ $visitor->label() }}</a></td>
                        <td>{{ $visitor->mobileDisplay() }}</td>
                        <td class="visitor-place">
                            <span>{{ $visitor->place() }}</span>
                            @if ($visitor->mapUrl())
                                <a class="visitor-map" href="{{ $visitor->mapUrl() }}" target="_blank" rel="noopener">Map</a>
                            @endif
                        </td>
                        <td>{{ $visitor->cameFrom() }}</td>
                        <td>{{ ist($visitor->last_seen_at) }}</td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="5">No visitors yet. When someone opens the website, they appear here.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <div style="margin-top:1rem;">{{ $visitors->links() }}</div>
</section>
@endsection
