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
        <form method="GET" action="{{ route('admin.analytics.index') }}" class="solution-search" style="display:flex;gap:.75rem;align-items:end;flex-wrap:wrap;">
            <div class="admin-field" style="min-width:16rem;flex:1;">
                <label class="admin-label" for="visitor-search">Search</label>
                <input id="visitor-search" class="admin-input" name="q" value="{{ $search }}" placeholder="Name, mobile, city, or website">
            </div>
            <button class="admin-btn" type="submit">Search</button>
            @if ($search !== '')
                <a class="admin-btn admin-btn-ghost" href="{{ route('admin.analytics.index') }}">Clear</a>
            @endif
        </form>
    </div>

    <div class="admin-card admin-table-wrap">
        <table class="admin-table lead-table visitor-table">
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
