@extends('layouts.admin')

@section('title', 'Services')
@section('breadcrumb', 'Admin / Services')
@section('heading', 'Services')

@section('content')
@php
    $status = $status ?? '';
    $search = request('q');
    $statusUrl = fn (string $next) => route('admin.services.index', array_filter([
        'q' => $search,
        'status' => $next !== '' ? $next : null,
    ]));
@endphp

<section class="lead-board solution-board">
    <div class="solution-summary">
        <a class="solution-count {{ $status === '' ? 'is-current' : '' }}" href="{{ $statusUrl('') }}"><strong>{{ $counts['all'] }}</strong><span>services</span></a>
        <a class="solution-count is-active {{ $status === 'active' ? 'is-current' : '' }}" href="{{ $statusUrl('active') }}"><strong>{{ $counts['active'] }}</strong><span>active</span></a>
        <a class="solution-count is-off {{ $status === 'inactive' ? 'is-current' : '' }}" href="{{ $statusUrl('inactive') }}"><strong>{{ $counts['inactive'] }}</strong><span>inactive</span></a>
    </div>
    <div class="admin-card solution-toolbar">
        <form method="GET" action="{{ route('admin.services.index') }}" class="solution-search">
            @if ($status !== '')<input type="hidden" name="status" value="{{ $status }}">@endif
            <div class="admin-field">
                <label class="admin-label" for="service-search">Search</label>
                <input id="service-search" class="admin-input" name="q" value="{{ $search }}" placeholder="Name, slug, or category">
            </div>
            <button class="admin-btn admin-btn-primary" type="submit">Search</button>
            @if (filled($search) || $status !== '')<a class="solution-clear" href="{{ route('admin.services.index') }}">Clear</a>@endif
        </form>
        @can('admin.services.manage')<a class="admin-btn admin-btn-primary" href="{{ route('admin.services.create') }}">Add service</a>@endcan
    </div>
    <div class="admin-card solution-list">
        <div class="admin-table-wrap">
            <table class="admin-table lead-table">
                <thead><tr>
                    <th><x-admin.sort column="name" label="Name" :sort="$sort" :dir="$dir" /></th>
                    <th><x-admin.sort column="category" label="Category" :sort="$sort" :dir="$dir" /></th>
                    <th><x-admin.sort column="sort_order" label="Order" :sort="$sort" :dir="$dir" /></th>
                    <th><x-admin.sort column="is_active" label="Status" :sort="$sort" :dir="$dir" /></th>
                    <th></th>
                </tr></thead>
                <tbody>
                @forelse ($services as $service)
                    <tr>
                        <td>
                            <a class="lead-name" href="{{ route('admin.services.show', $service) }}">{{ $service->name }}</a>
                            <span class="lead-meta">{{ $service->slug }}</span>
                        </td>
                        <td>{{ $service->category ?: '—' }}</td>
                        <td class="solution-num">{{ $service->sort_order }}</td>
                        <td>
                            <div class="status-pills">
                                <span class="admin-badge {{ $service->is_active ? 'is-won' : 'is-off' }}">{{ $service->is_active ? 'Active' : 'Inactive' }}</span>
                                @if ($service->is_featured)<span class="solution-flag">Featured</span>@endif
                            </div>
                        </td>
                        <td><div class="solution-row-actions">
                            <a class="admin-btn admin-btn-ghost lead-open" href="{{ route('admin.services.show', $service) }}">View</a>
                            @can('admin.services.manage')<a class="admin-btn admin-btn-ghost lead-open" href="{{ route('admin.services.edit', $service) }}">Edit</a>@endcan
                        </div></td>
                    </tr>
                @empty
                    <tr><td colspan="5"><div class="admin-empty">No services match this view.</div></td></tr>
                @endforelse
                </tbody>
            </table>
        </div>
        {{ $services->links() }}
    </div>
</section>
@endsection
