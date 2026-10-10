@extends('layouts.admin')

@section('title', 'Industries')
@section('breadcrumb', 'Admin / Industries')
@section('heading', 'Industries')

@section('content')
@php
    $status = $status ?? '';
    $search = request('q');
    $statusUrl = fn (string $next) => route('admin.industries.index', array_filter([
        'q' => $search,
        'status' => $next !== '' ? $next : null,
    ]));
@endphp

<section class="lead-board solution-board">
    <div class="solution-summary">
        <a class="solution-count {{ $status === '' ? 'is-current' : '' }}" href="{{ $statusUrl('') }}"><strong>{{ $counts['all'] }}</strong><span>industries</span></a>
        <a class="solution-count is-active {{ $status === 'active' ? 'is-current' : '' }}" href="{{ $statusUrl('active') }}"><strong>{{ $counts['active'] }}</strong><span>active</span></a>
        <a class="solution-count is-off {{ $status === 'inactive' ? 'is-current' : '' }}" href="{{ $statusUrl('inactive') }}"><strong>{{ $counts['inactive'] }}</strong><span>inactive</span></a>
    </div>
    <div class="admin-card solution-toolbar">
        <form method="GET" action="{{ route('admin.industries.index') }}" class="solution-search">
            @if ($status !== '')<input type="hidden" name="status" value="{{ $status }}">@endif
            <div class="admin-field">
                <label class="admin-label" for="industry-search">Search</label>
                <input id="industry-search" class="admin-input" name="q" value="{{ $search }}" placeholder="Name, slug, or group">
            </div>
            <button class="admin-btn admin-btn-primary" type="submit">Search</button>
            @if (filled($search) || $status !== '')<a class="solution-clear" href="{{ route('admin.industries.index') }}">Clear</a>@endif
        </form>
        @can('admin.industries.manage')<a class="admin-btn admin-btn-primary" href="{{ route('admin.industries.create') }}">Add industry</a>@endcan
    </div>
    <div class="admin-card solution-list">
        <div class="admin-table-wrap">
            <table class="admin-table lead-table">
                <thead><tr>
                    <th><x-admin.sort column="name" label="Name" :sort="$sort" :dir="$dir" /></th>
                    <th><x-admin.sort column="solutions_count" label="Solutions" :sort="$sort" :dir="$dir" /></th>
                    <th><x-admin.sort column="sort_order" label="Order" :sort="$sort" :dir="$dir" /></th>
                    <th><x-admin.sort column="is_active" label="Status" :sort="$sort" :dir="$dir" /></th>
                    <th></th>
                </tr></thead>
                <tbody>
                @forelse ($industries as $industry)
                    <tr>
                        <td>
                            <a class="lead-name" href="{{ route('admin.industries.show', $industry) }}">{{ $industry->name }}</a>
                            <span class="lead-meta">{{ $industry->slug }}@if ($industry->group) · {{ $industry->group }}@endif</span>
                        </td>
                        <td class="solution-num">{{ $industry->solutions_count }}</td>
                        <td class="solution-num">{{ $industry->sort_order }}</td>
                        <td>
                            <div class="status-pills">
                                <span class="admin-badge {{ $industry->is_active ? 'is-won' : 'is-off' }}">{{ $industry->is_active ? 'Active' : 'Inactive' }}</span>
                                @if ($industry->is_featured)<span class="solution-flag">Featured</span>@endif
                            </div>
                        </td>
                        <td><div class="solution-row-actions">
                            <a class="admin-btn admin-btn-ghost lead-open" href="{{ route('admin.industries.show', $industry) }}">View</a>
                            @can('admin.industries.manage')<a class="admin-btn admin-btn-ghost lead-open" href="{{ route('admin.industries.edit', $industry) }}">Edit</a>@endcan
                        </div></td>
                    </tr>
                @empty
                    <tr><td colspan="5"><div class="admin-empty">No industries match this view.</div></td></tr>
                @endforelse
                </tbody>
            </table>
        </div>
        {{ $industries->links() }}
    </div>
</section>
@endsection
