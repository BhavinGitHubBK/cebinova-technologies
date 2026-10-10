@extends('layouts.admin')

@php
    $group = $group ?? 'website';
    $isMarketing = $group === 'marketing';
@endphp

@section('title', $isMarketing ? 'Marketing packages' : 'Website packages')
@section('breadcrumb', $isMarketing ? 'Admin / Marketing packages' : 'Admin / Website packages')
@section('heading', $isMarketing ? 'Marketing packages' : 'Website packages')

@section('content')
@php
    $status = $status ?? '';
    $search = request('q');
    $category = request('category');
    $filters = array_filter([
        'group' => $isMarketing ? 'marketing' : null,
        'q' => $search,
        'category' => $category,
    ]);
    $statusUrl = fn (string $next) => route('admin.packages.index', array_filter([
        ...$filters,
        'status' => $next !== '' ? $next : null,
    ]));
@endphp

<p class="page-note">{{ $isMarketing ? 'Regular, Festival, and Complete Growth plans for the marketing page.' : 'Starter, Business, and Professional only. App, domain, and hosting prices are under Extra Charges.' }}</p>

<section class="lead-board solution-board" data-package-group="{{ $group }}">
    <div class="solution-summary">
        <a class="solution-count {{ $status === '' ? 'is-current' : '' }}" href="{{ $statusUrl('') }}"><strong>{{ $counts['all'] }}</strong><span>packages</span></a>
        <a class="solution-count is-active {{ $status === 'active' ? 'is-current' : '' }}" href="{{ $statusUrl('active') }}"><strong>{{ $counts['active'] }}</strong><span>active</span></a>
        <a class="solution-count is-off {{ $status === 'inactive' ? 'is-current' : '' }}" href="{{ $statusUrl('inactive') }}"><strong>{{ $counts['inactive'] }}</strong><span>inactive</span></a>
    </div>
    <div class="admin-card solution-toolbar">
        <form method="GET" action="{{ route('admin.packages.index') }}" class="solution-search">
            @if ($isMarketing)<input type="hidden" name="group" value="marketing">@endif
            @if ($status !== '')<input type="hidden" name="status" value="{{ $status }}">@endif
            <div class="admin-field">
                <label class="admin-label" for="package-search">Search</label>
                <input id="package-search" class="admin-input" name="q" value="{{ $search }}" placeholder="Name or slug">
            </div>
            <div class="admin-field">
                <label class="admin-label" for="package-category">Category</label>
                <select id="package-category" class="admin-select" name="category">
                    <option value="">All</option>
                    @foreach ($categories as $key => $label)
                        <option value="{{ $key }}" @selected($category === $key)>{{ $label }}</option>
                    @endforeach
                </select>
            </div>
            <button class="admin-btn admin-btn-primary" type="submit">Search</button>
            @if (filled($search) || filled($category) || $status !== '')<a class="solution-clear" href="{{ route('admin.packages.index', $isMarketing ? ['group' => 'marketing'] : []) }}">Clear</a>@endif
        </form>
        @can('admin.packages.manage')<a class="admin-btn admin-btn-primary" href="{{ route('admin.packages.create', $isMarketing ? ['group' => 'marketing'] : []) }}">Add package</a>@endcan
    </div>
    <div class="admin-card solution-list">
        <div class="admin-table-wrap">
            <table class="admin-table lead-table">
                <thead><tr>
                    <th><x-admin.sort column="name" label="Name" :sort="$sort" :dir="$dir" /></th>
                    <th><x-admin.sort column="category" label="Category" :sort="$sort" :dir="$dir" /></th>
                    <th><x-admin.sort column="plans_count" label="Plans" :sort="$sort" :dir="$dir" /></th>
                    <th><x-admin.sort column="is_active" label="Status" :sort="$sort" :dir="$dir" /></th>
                    <th></th>
                </tr></thead>
                <tbody>
                @forelse ($packages as $package)
                    <tr>
                        <td>
                            <a class="lead-name" href="{{ route('admin.packages.show', $package) }}">{{ $package->name }}</a>
                            <span class="lead-meta">{{ $package->slug }}</span>
                        </td>
                        <td>{{ $categories[$package->category] ?? $package->category }}</td>
                        <td class="solution-num">{{ $package->plans_count }}</td>
                        <td>
                            <div class="status-pills">
                                <span class="admin-badge {{ $package->is_active ? 'is-won' : 'is-off' }}">{{ $package->is_active ? 'Active' : 'Inactive' }}</span>
                                @if ($package->is_highlighted)<span class="solution-flag">Highlighted</span>@endif
                            </div>
                        </td>
                        <td><div class="solution-row-actions">
                            <a class="admin-btn admin-btn-ghost lead-open" href="{{ route('admin.packages.show', $package) }}">View</a>
                            @can('admin.packages.manage')<a class="admin-btn admin-btn-ghost lead-open" href="{{ route('admin.packages.edit', $package) }}">Edit</a>@endcan
                        </div></td>
                    </tr>
                @empty
                    <tr><td colspan="5"><div class="admin-empty">No {{ $isMarketing ? 'marketing' : 'website' }} packages match this view.</div></td></tr>
                @endforelse
                </tbody>
            </table>
        </div>
        {{ $packages->links() }}
    </div>
</section>
@endsection
