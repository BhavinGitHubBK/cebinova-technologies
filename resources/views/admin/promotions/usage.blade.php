@extends('layouts.admin')
@section('title', 'Promotion usage')
@section('heading', 'Coupon and campaign usage')
@section('breadcrumb', 'Admin / Promotions / Usage')
@section('content')
@include('admin.promotions.partials.nav')
@php
    $search = request('q');
    $campaignId = request('campaign');
    $kindUrl = fn (string $next) => route('admin.promotions.usage', array_filter([
        'q' => $search,
        'campaign' => $campaignId,
        'kind' => $next !== '' ? $next : null,
        'sort' => request('sort'),
        'dir' => request('dir'),
    ]));
@endphp
<p class="page-note">A redemption is stored when an enquiry is saved, not when someone types a code into the calculator.</p>
<section class="lead-board solution-board">
    <div class="solution-summary">
        <a class="solution-count {{ $kind === '' ? 'is-current' : '' }}" href="{{ $kindUrl('') }}"><strong>{{ $counts['all'] }}</strong><span>redemptions</span></a>
        <a class="solution-count is-active {{ $kind === 'coupon' ? 'is-current' : '' }}" href="{{ $kindUrl('coupon') }}"><strong>{{ $counts['coupon'] }}</strong><span>with a coupon</span></a>
        <a class="solution-count {{ $kind === 'automatic' ? 'is-current' : '' }}" href="{{ $kindUrl('automatic') }}"><strong>{{ $counts['automatic'] }}</strong><span>automatic</span></a>
    </div>
    <div class="admin-card solution-toolbar">
        <form method="GET" action="{{ route('admin.promotions.usage') }}" class="solution-search">
            @if ($kind !== '')<input type="hidden" name="kind" value="{{ $kind }}">@endif
            <div class="admin-field">
                <label class="admin-label" for="usage-search">Search</label>
                <input id="usage-search" class="admin-input" name="q" value="{{ $search }}" placeholder="Campaign, coupon, or lead">
            </div>
            <div class="admin-field">
                <label class="admin-label" for="usage-campaign">Campaign</label>
                <select id="usage-campaign" class="admin-select" name="campaign">
                    <option value="">All</option>
                    @foreach ($campaigns as $campaign)
                        <option value="{{ $campaign->id }}" @selected((string) $campaignId === (string) $campaign->id)>{{ $campaign->name }}</option>
                    @endforeach
                </select>
            </div>
            <button class="admin-btn admin-btn-primary" type="submit">Search</button>
            @if (filled($search) || filled($campaignId) || $kind !== '')<a class="solution-clear" href="{{ route('admin.promotions.usage') }}">Clear</a>@endif
        </form>
    </div>
    <div class="admin-card solution-list">
        <div class="admin-table-wrap">
            <table class="admin-table lead-table">
                <thead><tr>
                    <th><x-admin.sort column="redeemed_at" label="When" :sort="$sort" :dir="$dir" desc-first /></th>
                    <th>Campaign</th>
                    <th>Coupon</th>
                    <th>Lead</th>
                    <th><x-admin.sort column="original_amount" label="Original" :sort="$sort" :dir="$dir" /></th>
                    <th><x-admin.sort column="discount_amount" label="Discount" :sort="$sort" :dir="$dir" /></th>
                    <th><x-admin.sort column="final_amount" label="Final" :sort="$sort" :dir="$dir" /></th>
                </tr></thead>
                <tbody>
                @forelse ($redemptions as $row)
                    <tr>
                        <td>{{ $row->redeemed_at?->timezone(config('app.timezone'))->format('d M Y, h:i A') }}</td>
                        <td>{{ $row->campaign?->name }}</td>
                        <td>{{ $row->coupon?->code ?: '—' }}</td>
                        <td>@if ($row->lead)<a class="lead-name" href="{{ route('admin.leads.show', $row->lead) }}">{{ $row->lead->name }}</a>@else — @endif</td>
                        <td class="solution-num">{{ $row->original_amount }}</td>
                        <td class="solution-num">{{ $row->discount_amount }}</td>
                        <td class="solution-num">{{ $row->final_amount }}</td>
                    </tr>
                @empty
                    <tr><td colspan="7"><div class="admin-empty">No redemptions match this view.</div></td></tr>
                @endforelse
                </tbody>
            </table>
        </div>
        {{ $redemptions->links() }}
    </div>
</section>
@endsection
