@extends('layouts.admin')
@section('title', 'Promotions')
@section('breadcrumb', 'Admin / Promotions')
@section('heading', 'Promotions')
@section('content')
@include('admin.promotions.partials.nav')
@php
    $search = request('q');
    $type = request('type');
    $statusUrl = fn (string $next) => route('admin.promotions.campaigns.index', array_filter([
        'q' => $search,
        'type' => $type,
        'status' => $next !== '' ? $next : null,
        'sort' => request('sort'),
        'dir' => request('dir'),
    ]));
    $badge = fn (string $state) => match ($state) {
        'active' => 'is-won',
        'scheduled' => 'is-follow',
        'draft' => 'is-new',
        default => 'is-off',
    };
@endphp
<section class="lead-board solution-board">
    <div class="solution-summary">
        <a class="solution-count {{ $status === '' ? 'is-current' : '' }}" href="{{ $statusUrl('') }}"><strong>{{ $counts['all'] }}</strong><span>campaigns</span></a>
        <a class="solution-count is-active {{ $status === 'active' ? 'is-current' : '' }}" href="{{ $statusUrl('active') }}"><strong>{{ $counts['active'] }}</strong><span>active</span></a>
        <a class="solution-count {{ $status === 'scheduled' ? 'is-current' : '' }}" href="{{ $statusUrl('scheduled') }}"><strong>{{ $counts['scheduled'] }}</strong><span>scheduled</span></a>
        <a class="solution-count {{ $status === 'draft' ? 'is-current' : '' }}" href="{{ $statusUrl('draft') }}"><strong>{{ $counts['draft'] }}</strong><span>draft</span></a>
        <a class="solution-count is-off {{ $status === 'ended' ? 'is-current' : '' }}" href="{{ $statusUrl('ended') }}"><strong>{{ $counts['ended'] }}</strong><span>ended</span></a>
    </div>
    <div class="admin-card solution-toolbar">
        <form method="GET" action="{{ route('admin.promotions.campaigns.index') }}" class="solution-search">
            @if ($status !== '')<input type="hidden" name="status" value="{{ $status }}">@endif
            <div class="admin-field">
                <label class="admin-label" for="campaign-search">Search</label>
                <input id="campaign-search" class="admin-input" name="q" value="{{ $search }}" placeholder="Name or slug">
            </div>
            <div class="admin-field">
                <label class="admin-label" for="campaign-type">Type</label>
                <select id="campaign-type" class="admin-select" name="type">
                    <option value="">All</option>
                    @foreach ($types as $value => $label)
                        <option value="{{ $value }}" @selected($type === $value)>{{ $label }}</option>
                    @endforeach
                </select>
            </div>
            <button class="admin-btn admin-btn-primary" type="submit">Search</button>
            @if (filled($search) || filled($type) || $status !== '')<a class="solution-clear" href="{{ route('admin.promotions.campaigns.index') }}">Clear</a>@endif
        </form>
    </div>
    <div class="admin-card solution-list">
        <div class="admin-table-wrap">
            <table class="admin-table lead-table">
                <thead><tr>
                    <th><x-admin.sort column="name" label="Campaign" :sort="$sort" :dir="$dir" /></th>
                    <th>Type</th>
                    <th><x-admin.sort column="discount_value" label="Discount" :sort="$sort" :dir="$dir" /></th>
                    <th><x-admin.sort column="starts_at" label="Starts" :sort="$sort" :dir="$dir" desc-first /></th>
                    <th><x-admin.sort column="ends_at" label="Ends" :sort="$sort" :dir="$dir" desc-first /></th>
                    <th>Status</th>
                    <th>Coupon</th>
                    <th>Popup</th>
                    <th>Usage</th>
                    <th></th>
                </tr></thead>
                <tbody>
                @forelse ($campaigns as $campaign)
                    <tr>
                        <td>
                            <a class="lead-name" href="{{ route('admin.promotions.campaigns.edit', $campaign) }}">{{ $campaign->name }}</a>
                            <span class="lead-meta">{{ $campaign->slug }}</span>
                        </td>
                        <td>{{ $campaign->campaign_type?->label() }}</td>
                        <td>{{ $campaign->discountLabel() }}</td>
                        <td>{{ $campaign->starts_at?->timezone(config('app.timezone'))->format('d M Y, h:i A') }}</td>
                        <td>{{ $campaign->ends_at?->timezone(config('app.timezone'))->format('d M Y, h:i A') }}</td>
                        <td><span class="admin-badge {{ $badge($campaign->state()) }}">{{ ucfirst($campaign->state()) }}</span></td>
                        <td>{{ $campaign->coupons->where('is_active', true)->pluck('code')->join(', ') ?: ($campaign->coupon_required ? 'Required' : 'Automatic') }}</td>
                        <td><span class="admin-badge {{ $campaign->show_popup ? 'is-won' : 'is-off' }}">{{ $campaign->show_popup ? 'On' : 'Off' }}</span></td>
                        <td class="solution-num">{{ $campaign->redemptions_count }}</td>
                        <td>
                            <div class="solution-row-actions">
                                <a class="admin-btn admin-btn-ghost lead-open" href="{{ route('admin.promotions.campaigns.edit', $campaign) }}">Edit</a>
                                @can('admin.promotions.manage')
                                    <form method="POST" action="{{ route('admin.promotions.campaigns.destroy', $campaign) }}" data-skip-ajax="1">
                                        @csrf
                                        @method('DELETE')
                                        <button class="admin-btn admin-btn-danger lead-open" type="submit" data-confirm="Delete this campaign? It leaves the site and prices go back to normal.">Delete</button>
                                    </form>
                                @endcan
                            </div>
                        </td>
                    </tr>
                @empty
                    <tr><td colspan="10"><div class="admin-empty">No campaigns match this view.</div></td></tr>
                @endforelse
                </tbody>
            </table>
        </div>
        {{ $campaigns->links() }}
    </div>
</section>
@endsection
