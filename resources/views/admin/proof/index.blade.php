@extends('layouts.admin')

@section('title', 'Demos')
@section('breadcrumb', 'Admin / Demos')
@section('heading', 'Demos')

@section('content')
@php
    $status = $status ?? '';
    $filters = request()->only(['q', 'industry', 'solution', 'sort']);
    $statusUrl = fn (string $next) => route('admin.demos.index', array_filter($filters + [
        'status' => $next !== '' ? $next : null,
    ]));
@endphp

<section class="lead-board solution-board">
    <p class="lead-meta">Sample implementations that show what CEBINOVA can build. These are not client projects.</p>
    <div class="solution-summary">
        <a class="solution-count {{ $status === '' ? 'is-current' : '' }}" href="{{ $statusUrl('') }}"><strong>{{ $counts['all'] }}</strong><span>demos</span></a>
        <a class="solution-count is-active {{ $status === 'active' ? 'is-current' : '' }}" href="{{ $statusUrl('active') }}"><strong>{{ $counts['active'] }}</strong><span>active</span></a>
        <a class="solution-count is-off {{ $status === 'inactive' ? 'is-current' : '' }}" href="{{ $statusUrl('inactive') }}"><strong>{{ $counts['inactive'] }}</strong><span>inactive</span></a>
    </div>
    <div class="admin-card solution-toolbar">
        <form method="GET" action="{{ route('admin.demos.index') }}" class="solution-search">
            @if ($status !== '')<input type="hidden" name="status" value="{{ $status }}">@endif
            <div class="admin-field">
                <label class="admin-label" for="demo-search">Search</label>
                <input id="demo-search" class="admin-input" name="q" value="{{ request('q') }}" placeholder="Demo name">
            </div>
            <div class="admin-field">
                <label class="admin-label" for="demo-industry">Industry</label>
                <select id="demo-industry" class="admin-select" name="industry">
                    <option value="">All</option>
                    @foreach ($industries as $option)
                        <option value="{{ $option->id }}" @selected(request('industry') == $option->id)>{{ $option->name }}</option>
                    @endforeach
                </select>
            </div>
            <div class="admin-field">
                <label class="admin-label" for="demo-solution">Solution</label>
                <select id="demo-solution" class="admin-select" name="solution">
                    <option value="">All</option>
                    @foreach ($solutions as $option)
                        <option value="{{ $option->id }}" @selected(request('solution') == $option->id)>{{ $option->name }}</option>
                    @endforeach
                </select>
            </div>
            <div class="admin-field">
                <label class="admin-label" for="demo-sort">Sort</label>
                <select id="demo-sort" class="admin-select" name="sort">
                    @foreach (['sort_order' => 'Order', 'name' => 'Name', 'updated_at' => 'Updated'] as $value => $label)
                        <option value="{{ $value }}" @selected(request('sort', 'sort_order') === $value)>{{ $label }}</option>
                    @endforeach
                </select>
            </div>
            <button class="admin-btn admin-btn-primary" type="submit">Search</button>
        </form>
        @can('admin.content.manage')<a class="admin-btn admin-btn-primary" href="{{ route('admin.demos.create') }}">Add demo</a>@endcan
    </div>
    <div class="admin-card solution-list">
        <div class="admin-table-wrap">
            <table class="admin-table lead-table">
                <thead><tr>
                    <th><x-admin.sort column="name" label="Name" :sort="$sort" :dir="$dir" /></th>
                    <th><x-admin.sort column="industry" label="Industry / solution" :sort="$sort" :dir="$dir" /></th>
                    <th><x-admin.sort column="sort_order" label="Order" :sort="$sort" :dir="$dir" /></th>
                    <th><x-admin.sort column="is_active" label="Status" :sort="$sort" :dir="$dir" /></th>
                    <th><x-admin.sort column="updated_at" label="Updated" :sort="$sort" :dir="$dir" desc-first /></th>
                    <th></th>
                </tr></thead>
                <tbody>
                @forelse ($records as $record)
                    <tr>
                        <td>
                            @can('admin.content.manage')
                                <a class="lead-name" href="{{ route('admin.demos.edit', $record) }}">{{ $record->name }}</a>
                            @else
                                <strong>{{ $record->name }}</strong>
                            @endcan
                            <span class="lead-meta">{{ $record->slug }}</span>
                        </td>
                        <td>
                            {{ $record->industries->pluck('name')->implode(', ') ?: '—' }}
                            <span class="lead-meta">{{ $record->solutions->pluck('name')->implode(', ') ?: 'No solution' }}</span>
                        </td>
                        <td class="solution-num">{{ $record->sort_order }}</td>
                        <td>
                            <div class="status-pills">
                                <span class="admin-badge {{ $record->is_active ? 'is-won' : 'is-off' }}">{{ $record->is_active ? 'Active' : 'Inactive' }}</span>
                                @if ($record->is_featured)<span class="solution-flag">Featured</span>@endif
                            </div>
                        </td>
                        <td class="lead-when">{{ $record->updated_at?->timezone(config('app.timezone'))->format('d M Y') }}</td>
                        <td>
                            @can('admin.content.manage')
                                <div class="solution-row-actions">
                                    <a class="admin-btn admin-btn-ghost lead-open" href="{{ route('admin.demos.edit', $record) }}">Edit</a>
                                    <form method="POST" action="{{ route('admin.demos.destroy', $record) }}">
                                        @csrf @method('DELETE')
                                        <button class="admin-btn admin-btn-danger lead-open" type="submit" data-confirm="Archive this demo?">Archive</button>
                                    </form>
                                </div>
                            @endcan
                        </td>
                    </tr>
                @empty
                    <tr><td colspan="6"><div class="admin-empty">No demos match this view.</div></td></tr>
                @endforelse
                </tbody>
            </table>
        </div>
        {{ $records->links() }}
    </div>
</section>
@endsection
