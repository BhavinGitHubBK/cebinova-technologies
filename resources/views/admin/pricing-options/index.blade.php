@extends('layouts.admin')

@section('title', 'Extra charges')
@section('breadcrumb', 'Admin / Extra charges')
@section('heading', 'Extra charges')

@section('content')
<p class="page-note">Android app, iOS app, domain, and hosting. These are not the Starter, Business, or Professional website plans.</p>
@php
    $status = $status ?? '';
    $search = request('q');
    $type = request('type');
    $statusUrl = fn (string $next) => route('admin.pricing-options.index', array_filter([
        'q' => $search,
        'type' => $type,
        'status' => $next !== '' ? $next : null,
    ]));
@endphp

<section class="lead-board solution-board">
    <div class="solution-summary">
        <a class="solution-count {{ $status === '' ? 'is-current' : '' }}" href="{{ $statusUrl('') }}"><strong>{{ $counts['all'] }}</strong><span>charges</span></a>
        <a class="solution-count is-active {{ $status === 'active' ? 'is-current' : '' }}" href="{{ $statusUrl('active') }}"><strong>{{ $counts['active'] }}</strong><span>active</span></a>
        <a class="solution-count is-off {{ $status === 'inactive' ? 'is-current' : '' }}" href="{{ $statusUrl('inactive') }}"><strong>{{ $counts['inactive'] }}</strong><span>inactive</span></a>
    </div>
    <div class="admin-card solution-toolbar">
        <form method="GET" action="{{ route('admin.pricing-options.index') }}" class="solution-search">
            @if ($status !== '')<input type="hidden" name="status" value="{{ $status }}">@endif
            <div class="admin-field">
                <label class="admin-label" for="option-search">Search</label>
                <input id="option-search" class="admin-input" name="q" value="{{ $search }}" placeholder="Option name">
            </div>
            <div class="admin-field">
                <label class="admin-label" for="option-type">Type</label>
                <select id="option-type" class="admin-select" name="type">
                    <option value="">All types</option>
                    @foreach ($types as $key => $label)
                        <option value="{{ $key }}" @selected($type === $key)>{{ $label }}</option>
                    @endforeach
                </select>
            </div>
            <button class="admin-btn admin-btn-primary" type="submit">Search</button>
            @if (filled($search) || filled($type) || $status !== '')<a class="solution-clear" href="{{ route('admin.pricing-options.index') }}">Clear</a>@endif
        </form>
        @can('admin.packages.manage')<a class="admin-btn admin-btn-primary" href="{{ route('admin.pricing-options.create') }}">Add charge</a>@endcan
    </div>
    <div class="admin-card solution-list">
        <div class="admin-table-wrap">
            <table class="admin-table lead-table">
                <thead><tr>
                    <th><x-admin.sort column="name" label="Name" :sort="$sort" :dir="$dir" /></th>
                    <th><x-admin.sort column="type" label="Type" :sort="$sort" :dir="$dir" /></th>
                    <th><x-admin.sort column="price" label="Price" :sort="$sort" :dir="$dir" /></th>
                    <th><x-admin.sort column="billing_period" label="Billing" :sort="$sort" :dir="$dir" /></th>
                    <th><x-admin.sort column="sort_order" label="Order" :sort="$sort" :dir="$dir" /></th>
                    <th><x-admin.sort column="is_active" label="Status" :sort="$sort" :dir="$dir" /></th>
                    <th></th>
                </tr></thead>
                <tbody>
                @forelse ($options as $option)
                    <tr>
                        <td>
                            <strong class="lead-name">{{ $option->name }}</strong>
                            @if ($option->is_recommended)<span class="solution-flag">Recommended</span>@endif
                        </td>
                        <td>{{ $types[$option->type] ?? $option->type }}</td>
                        <td class="solution-num">{{ cebinova_inr($option->price) }}</td>
                        <td>{{ \App\Models\PricingOption::BILLING_PERIODS[$option->billing_period] ?? $option->billing_period }}</td>
                        <td class="solution-num">{{ $option->sort_order }}</td>
                        <td><span class="admin-badge {{ $option->is_active ? 'is-won' : 'is-off' }}">{{ $option->is_active ? 'Active' : 'Inactive' }}</span></td>
                        <td>
                            @can('admin.packages.manage')
                                <div class="solution-row-actions">
                                    <a class="admin-btn admin-btn-ghost lead-open" href="{{ route('admin.pricing-options.edit', $option) }}">Edit</a>
                                    <form method="POST" action="{{ route('admin.pricing-options.destroy', $option) }}">
                                        @csrf @method('DELETE')
                                        <button class="admin-btn admin-btn-danger lead-open" type="submit" data-confirm="Archive this option?">Archive</button>
                                    </form>
                                </div>
                            @endcan
                        </td>
                    </tr>
                @empty
                    <tr><td colspan="7"><div class="admin-empty">No extra charges match this view.</div></td></tr>
                @endforelse
                </tbody>
            </table>
        </div>
        {{ $options->links() }}
    </div>
</section>
@endsection
