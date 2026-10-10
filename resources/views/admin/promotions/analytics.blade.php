@extends('layouts.admin')
@section('title', 'Promotion analytics')
@section('heading', 'Campaign performance')
@section('breadcrumb', 'Admin / Promotions / Analytics')
@section('content')
@include('admin.promotions.partials.nav')
@php
    $search = request('q');
    $statusUrl = fn (string $next) => route('admin.promotions.analytics', array_filter([
        'q' => $search,
        'status' => $next !== '' ? $next : null,
    ]));
    $campaignSort = $table === 'campaigns' ? $sort : '';
    $couponSort = $table === 'coupons' ? $sort : '';
@endphp
<p class="page-note">Conversion rate is enquiries divided by offer clicks. Discount granted is the sum of saved enquiry snapshots. This is not a profit figure.</p>
<section class="lead-board solution-board">
    <div class="solution-summary">
        <a class="solution-count {{ $status === '' ? 'is-current' : '' }}" href="{{ $statusUrl('') }}"><strong>{{ $counts['all'] }}</strong><span>campaigns</span></a>
        <a class="solution-count is-active {{ $status === 'active' ? 'is-current' : '' }}" href="{{ $statusUrl('active') }}"><strong>{{ $counts['active'] }}</strong><span>active</span></a>
        <a class="solution-count {{ $status === 'scheduled' ? 'is-current' : '' }}" href="{{ $statusUrl('scheduled') }}"><strong>{{ $counts['scheduled'] }}</strong><span>scheduled</span></a>
        <a class="solution-count {{ $status === 'draft' ? 'is-current' : '' }}" href="{{ $statusUrl('draft') }}"><strong>{{ $counts['draft'] }}</strong><span>draft</span></a>
        <a class="solution-count is-off {{ $status === 'ended' ? 'is-current' : '' }}" href="{{ $statusUrl('ended') }}"><strong>{{ $counts['ended'] }}</strong><span>ended</span></a>
    </div>
    <div class="admin-card solution-toolbar">
        <form method="GET" action="{{ route('admin.promotions.analytics') }}" class="solution-search">
            @if ($status !== '')<input type="hidden" name="status" value="{{ $status }}">@endif
            <div class="admin-field">
                <label class="admin-label" for="analytics-search">Search</label>
                <input id="analytics-search" class="admin-input" name="q" value="{{ $search }}" placeholder="Campaign or coupon">
            </div>
            <button class="admin-btn admin-btn-primary" type="submit">Search</button>
            @if (filled($search) || $status !== '')<a class="solution-clear" href="{{ route('admin.promotions.analytics') }}">Clear</a>@endif
        </form>
    </div>
    <div class="analytics-totals">
        <p><strong>{{ number_format($totals['visitors']) }}</strong><span>visitors in this view</span></p>
        <p><strong>{{ number_format($totals['leads']) }}</strong><span>leads</span></p>
        <p><strong>₹{{ number_format($totals['discount'], 2) }}</strong><span>discount granted</span></p>
    </div>
    <div class="admin-card solution-list">
        <div class="admin-table-wrap">
            <table class="admin-table lead-table">
                <thead><tr>
                    <th><x-admin.sort column="name" label="Campaign" :sort="$campaignSort" :dir="$dir" table="campaigns" /></th>
                    <th><x-admin.sort column="visitors" label="Visitors" :sort="$campaignSort" :dir="$dir" table="campaigns" /></th>
                    <th><x-admin.sort column="impressions" label="Impressions" :sort="$campaignSort" :dir="$dir" table="campaigns" /></th>
                    <th><x-admin.sort column="popup_views" label="Popup views" :sort="$campaignSort" :dir="$dir" table="campaigns" /></th>
                    <th><x-admin.sort column="clicks" label="Clicks" :sort="$campaignSort" :dir="$dir" table="campaigns" /></th>
                    <th><x-admin.sort column="coupon_applications" label="Coupon uses" :sort="$campaignSort" :dir="$dir" table="campaigns" /></th>
                    <th><x-admin.sort column="leads" label="Leads" :sort="$campaignSort" :dir="$dir" table="campaigns" /></th>
                    <th><x-admin.sort column="qualified" label="Qualified" :sort="$campaignSort" :dir="$dir" table="campaigns" /></th>
                    <th><x-admin.sort column="won" label="Won" :sort="$campaignSort" :dir="$dir" table="campaigns" /></th>
                    <th><x-admin.sort column="conversion" label="Conversion" :sort="$campaignSort" :dir="$dir" table="campaigns" /></th>
                    <th><x-admin.sort column="discount" label="Discount granted" :sort="$campaignSort" :dir="$dir" table="campaigns" /></th>
                </tr></thead>
                <tbody>
                @forelse ($campaigns as $row)
                    <tr>
                        <td><a class="lead-name" href="{{ route('admin.promotions.campaigns.edit', $row['campaign']) }}">{{ $row['campaign']->name }}</a></td>
                        <td class="solution-num">{{ $row['visitors'] }}</td>
                        <td class="solution-num">{{ $row['impressions'] }}</td>
                        <td class="solution-num">{{ $row['popup_views'] }}</td>
                        <td class="solution-num">{{ $row['clicks'] }}</td>
                        <td class="solution-num">{{ $row['coupon_applications'] }}</td>
                        <td class="solution-num">{{ $row['leads'] }}</td>
                        <td class="solution-num">{{ $row['qualified'] }}</td>
                        <td class="solution-num">{{ $row['won'] }}</td>
                        <td class="solution-num">{{ $row['conversion'] !== null ? $row['conversion'].'%' : '—' }}</td>
                        <td class="solution-num">₹{{ number_format($row['discount'], 2) }}</td>
                    </tr>
                @empty
                    <tr><td colspan="11"><div class="admin-empty">No campaigns match this view.</div></td></tr>
                @endforelse
                </tbody>
            </table>
        </div>
    </div>
    <h2 class="analytics-heading">Coupons</h2>
    <div class="admin-card solution-list">
        <div class="admin-table-wrap">
            <table class="admin-table lead-table">
                <thead><tr>
                    <th><x-admin.sort column="code" label="Coupon" :sort="$couponSort" :dir="$dir" table="coupons" /></th>
                    <th><x-admin.sort column="attempts" label="Attempts" :sort="$couponSort" :dir="$dir" table="coupons" /></th>
                    <th><x-admin.sort column="applied" label="Applied" :sort="$couponSort" :dir="$dir" table="coupons" /></th>
                    <th><x-admin.sort column="redemptions" label="Redemptions" :sort="$couponSort" :dir="$dir" table="coupons" /></th>
                    <th><x-admin.sort column="discount" label="Discount granted" :sort="$couponSort" :dir="$dir" table="coupons" /></th>
                    <th><x-admin.sort column="leads" label="Leads" :sort="$couponSort" :dir="$dir" table="coupons" /></th>
                    <th><x-admin.sort column="won" label="Won" :sort="$couponSort" :dir="$dir" table="coupons" /></th>
                </tr></thead>
                <tbody>
                @forelse ($coupons as $row)
                    <tr>
                        <td><a class="lead-name" href="{{ route('admin.promotions.coupons.edit', $row['coupon']) }}">{{ $row['coupon']->code }}</a></td>
                        <td class="solution-num">{{ $row['attempts'] }}</td>
                        <td class="solution-num">{{ $row['applied'] }}</td>
                        <td class="solution-num">{{ $row['redemptions'] }}</td>
                        <td class="solution-num">₹{{ number_format($row['discount'], 2) }}</td>
                        <td class="solution-num">{{ $row['leads'] }}</td>
                        <td class="solution-num">{{ $row['won'] }}</td>
                    </tr>
                @empty
                    <tr><td colspan="7"><div class="admin-empty">No coupons match this view.</div></td></tr>
                @endforelse
                </tbody>
            </table>
        </div>
    </div>
</section>
<style>
.analytics-totals{display:flex;flex-wrap:wrap;gap:.75rem;margin:0}
.analytics-totals p{display:flex;flex-direction:column;gap:.15rem;min-width:10rem;margin:0;padding:.8rem 1rem;border:1px solid #e2e8f0;border-top:3px solid #fbb50b;background:#fff}
.analytics-totals strong{color:#013a9d;font-size:1.25rem}
.analytics-totals span{color:#5c6168;font-size:.82rem}
.analytics-heading{margin:0;color:#013a9d;font-size:.74rem;font-weight:800;letter-spacing:.12em;text-transform:uppercase}
</style>
@endsection
