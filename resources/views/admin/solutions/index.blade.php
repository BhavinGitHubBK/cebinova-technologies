@extends('layouts.admin')

@section('title', 'Solutions')
@section('breadcrumb', 'Admin / Solutions')
@section('heading', 'Solutions')

@section('content')
@php
    $status = $status ?? '';
    $search = request('q');
    $statusUrl = function (string $next) use ($search) {
        return route('admin.solutions.index', array_filter([
            'q' => $search,
            'status' => $next !== '' ? $next : null,
        ]));
    };
@endphp

<section class="lead-board solution-board">
    <div class="solution-summary">
        <a class="solution-count {{ $status === '' ? 'is-current' : '' }}" href="{{ $statusUrl('') }}">
            <strong>{{ $counts['all'] }}</strong>
            <span>solutions</span>
        </a>
        <a class="solution-count is-active {{ $status === 'active' ? 'is-current' : '' }}" href="{{ $statusUrl('active') }}">
            <strong>{{ $counts['active'] }}</strong>
            <span>active</span>
        </a>
        <a class="solution-count is-off {{ $status === 'inactive' ? 'is-current' : '' }}" href="{{ $statusUrl('inactive') }}">
            <strong>{{ $counts['inactive'] }}</strong>
            <span>inactive</span>
        </a>
    </div>

    <div class="admin-card solution-toolbar">
        <form method="GET" action="{{ route('admin.solutions.index') }}" class="solution-search">
            @if ($status !== '')
                <input type="hidden" name="status" value="{{ $status }}">
            @endif
            <div class="admin-field">
                <label class="admin-label" for="solution-search">Search</label>
                <input id="solution-search" class="admin-input" name="q" value="{{ $search }}" placeholder="Name, slug, or category">
            </div>
            <button class="admin-btn admin-btn-primary" type="submit">Search</button>
            @if (filled($search) || $status !== '')
                <a class="solution-clear" href="{{ route('admin.solutions.index') }}">Clear</a>
            @endif
        </form>
        @can('admin.solutions.manage')
            <a class="admin-btn admin-btn-primary" href="{{ route('admin.solutions.create') }}">Add solution</a>
        @endcan
    </div>

    <div class="admin-card solution-list">
        <div class="admin-table-wrap">
            <table class="admin-table lead-table">
                <thead>
                    <tr>
                        <th><x-admin.sort column="name" label="Name" :sort="$sort" :dir="$dir" /></th>
                        <th><x-admin.sort column="services_count" label="Services" :sort="$sort" :dir="$dir" /></th>
                        <th><x-admin.sort column="industries_count" label="Industries" :sort="$sort" :dir="$dir" /></th>
                        <th><x-admin.sort column="sort_order" label="Order" :sort="$sort" :dir="$dir" /></th>
                        <th><x-admin.sort column="is_active" label="Status" :sort="$sort" :dir="$dir" /></th>
                        <th></th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($solutions as $solution)
                        <tr>
                            <td>
                                <a class="lead-name" href="{{ route('admin.solutions.show', $solution) }}">{{ $solution->name }}</a>
                                <span class="lead-meta">{{ $solution->slug }}@if ($solution->category) · {{ $solution->category }}@endif</span>
                            </td>
                            <td class="solution-num">{{ $solution->services_count }}</td>
                            <td class="solution-num">{{ $solution->industries_count }}</td>
                            <td class="solution-num">{{ $solution->sort_order }}</td>
                            <td>
                                <div class="status-pills">
                                    <span class="admin-badge {{ $solution->is_active ? 'is-won' : 'is-off' }}">{{ $solution->is_active ? 'Active' : 'Inactive' }}</span>
                                    @if ($solution->is_featured)<span class="solution-flag">Featured</span>@endif
                                </div>
                            </td>
                            <td>
                                <div class="solution-row-actions">
                                    <a class="admin-btn admin-btn-ghost lead-open" href="{{ route('admin.solutions.show', $solution) }}">View</a>
                                    @can('admin.solutions.manage')
                                        <a class="admin-btn admin-btn-ghost lead-open" href="{{ route('admin.solutions.edit', $solution) }}">Edit</a>
                                    @endcan
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6"><div class="admin-empty">No solutions match this view.</div></td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        {{ $solutions->links() }}
    </div>
</section>
@endsection
