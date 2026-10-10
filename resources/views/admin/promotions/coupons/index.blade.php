@extends('layouts.admin')
@section('title', 'Coupons')
@section('heading', 'Coupons')
@section('breadcrumb', 'Admin / Promotions / Coupons')
@section('content')
@include('admin.promotions.partials.nav')
@php
    $search = request('q');
    $statusUrl = fn (string $next) => route('admin.promotions.coupons.index', array_filter([
        'q' => $search,
        'status' => $next !== '' ? $next : null,
        'sort' => request('sort'),
        'dir' => request('dir'),
    ]));
@endphp
<section class="lead-board solution-board">
    <div class="solution-summary">
        <a class="solution-count {{ $status === '' ? 'is-current' : '' }}" href="{{ $statusUrl('') }}"><strong>{{ $counts['all'] }}</strong><span>coupons</span></a>
        <a class="solution-count is-active {{ $status === 'active' ? 'is-current' : '' }}" href="{{ $statusUrl('active') }}"><strong>{{ $counts['active'] }}</strong><span>active</span></a>
        <a class="solution-count is-off {{ $status === 'off' ? 'is-current' : '' }}" href="{{ $statusUrl('off') }}"><strong>{{ $counts['off'] }}</strong><span>off</span></a>
    </div>
    <div class="admin-card solution-toolbar">
        <form method="GET" action="{{ route('admin.promotions.coupons.index') }}" class="solution-search">
            @if ($status !== '')<input type="hidden" name="status" value="{{ $status }}">@endif
            <div class="admin-field">
                <label class="admin-label" for="coupon-search">Search</label>
                <input id="coupon-search" class="admin-input" name="q" value="{{ $search }}" placeholder="Code or campaign">
            </div>
            <button class="admin-btn admin-btn-primary" type="submit">Search</button>
            @if (filled($search) || $status !== '')<a class="solution-clear" href="{{ route('admin.promotions.coupons.index') }}">Clear</a>@endif
        </form>
    </div>
    <div class="admin-card solution-list">
        <div class="admin-table-wrap">
            <table class="admin-table lead-table">
                <thead><tr>
                    <th><x-admin.sort column="code" label="Code" :sort="$sort" :dir="$dir" /></th>
                    <th>Campaign</th>
                    <th>Discount</th>
                    <th><x-admin.sort column="redemptions_count" label="Uses" :sort="$sort" :dir="$dir" /></th>
                    <th><x-admin.sort column="is_active" label="Active" :sort="$sort" :dir="$dir" /></th>
                    <th></th>
                </tr></thead>
                <tbody>
                @forelse ($coupons as $coupon)
                    <tr>
                        <td><a class="lead-name" href="{{ route('admin.promotions.coupons.edit', $coupon) }}">{{ $coupon->code }}</a></td>
                        <td>{{ $coupon->campaign?->name ?: 'Any current quote' }}</td>
                        <td>{{ $coupon->discount_type ? ($coupon->discount_type->value === 'percentage' ? rtrim(rtrim((string) $coupon->discount_value, '0'), '.').'%' : '₹'.$coupon->discount_value) : 'Campaign discount' }}</td>
                        <td class="solution-num">{{ $coupon->redemptions_count }}{{ $coupon->usage_limit ? ' / '.$coupon->usage_limit : '' }}</td>
                        <td><span class="admin-badge {{ $coupon->is_active ? 'is-won' : 'is-off' }}">{{ $coupon->is_active ? 'Active' : 'Off' }}</span></td>
                        <td>
                            <div class="solution-row-actions">
                                <a class="admin-btn admin-btn-ghost lead-open" href="{{ route('admin.promotions.coupons.edit', $coupon) }}">Edit</a>
                                @can('admin.promotions.manage')
                                    <form method="POST" action="{{ route('admin.promotions.coupons.destroy', $coupon) }}" data-skip-ajax="1">
                                        @csrf
                                        @method('DELETE')
                                        <button class="admin-btn admin-btn-danger lead-open" type="submit" data-confirm="Delete this coupon? Visitors will no longer be able to use it.">Delete</button>
                                    </form>
                                @endcan
                            </div>
                        </td>
                    </tr>
                @empty
                    <tr><td colspan="6"><div class="admin-empty">No coupons match this view.</div></td></tr>
                @endforelse
                </tbody>
            </table>
        </div>
        {{ $coupons->links() }}
    </div>
</section>
@endsection
