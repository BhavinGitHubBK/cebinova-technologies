@extends('layouts.admin')

@section('title', 'Page sections')
@section('breadcrumb', 'Admin / Page sections')
@section('heading', 'Page sections')

@section('content')
<p class="page-note">These sections fill the existing page layout. Editing the text updates the website. Adding extra proof stats or a section the page does not use will not change the design.</p>
@php
    $status = $status ?? '';
    $search = request('q');
    $pageFilter = request('section_page');
    $statusUrl = fn (string $next) => route('admin.page-sections.index', array_filter([
        'q' => $search,
        'section_page' => $pageFilter,
        'status' => $next !== '' ? $next : null,
    ]));
@endphp

<section class="lead-board solution-board">
    <div class="solution-summary">
        <a class="solution-count {{ $status === '' ? 'is-current' : '' }}" href="{{ $statusUrl('') }}"><strong>{{ $counts['all'] }}</strong><span>sections</span></a>
        <a class="solution-count is-active {{ $status === 'active' ? 'is-current' : '' }}" href="{{ $statusUrl('active') }}"><strong>{{ $counts['active'] }}</strong><span>visible</span></a>
        <a class="solution-count is-off {{ $status === 'inactive' ? 'is-current' : '' }}" href="{{ $statusUrl('inactive') }}"><strong>{{ $counts['inactive'] }}</strong><span>hidden</span></a>
    </div>
    <div class="admin-card solution-toolbar">
        <form method="GET" action="{{ route('admin.page-sections.index') }}" class="solution-search">
            @if ($status !== '')<input type="hidden" name="status" value="{{ $status }}">@endif
            <div class="admin-field">
                <label class="admin-label" for="section-search">Search</label>
                <input id="section-search" class="admin-input" name="q" value="{{ $search }}" placeholder="Key or heading">
            </div>
            <div class="admin-field">
                <label class="admin-label" for="section-page">Page</label>
                <select id="section-page" class="admin-select" name="section_page">
                    <option value="">All pages</option>
                    @foreach ($pages as $page)
                        <option value="{{ $page }}" @selected($pageFilter === $page)>{{ $page }}</option>
                    @endforeach
                </select>
            </div>
            <button class="admin-btn admin-btn-primary" type="submit">Search</button>
            @if (filled($search) || filled($pageFilter) || $status !== '')<a class="solution-clear" href="{{ route('admin.page-sections.index') }}">Clear</a>@endif
        </form>
        @can('admin.content.manage')<a class="admin-btn admin-btn-primary" href="{{ route('admin.page-sections.create') }}">Add section</a>@endcan
    </div>
    <div class="admin-card solution-list">
        <div class="admin-table-wrap">
            <table class="admin-table lead-table">
                <thead><tr>
                    <th><x-admin.sort column="page" label="Page" :sort="$sort" :dir="$dir" /></th>
                    <th><x-admin.sort column="key" label="Key" :sort="$sort" :dir="$dir" /></th>
                    <th><x-admin.sort column="heading" label="Heading" :sort="$sort" :dir="$dir" /></th>
                    <th><x-admin.sort column="is_visible" label="Visible" :sort="$sort" :dir="$dir" /></th>
                    <th></th>
                </tr></thead>
                <tbody>
                @forelse ($sections as $section)
                    <tr>
                        <td>{{ $section->page }}</td>
                        <td>
                            <a class="lead-name" href="{{ route('admin.page-sections.show', $section) }}">{{ $section->key }}</a>
                        </td>
                        <td>{{ $section->heading ?: '—' }}</td>
                        <td><span class="admin-badge {{ $section->is_visible ? 'is-won' : 'is-off' }}">{{ $section->is_visible ? 'Visible' : 'Hidden' }}</span></td>
                        <td><div class="solution-row-actions">
                            <a class="admin-btn admin-btn-ghost lead-open" href="{{ route('admin.page-sections.show', $section) }}">View</a>
                            @can('admin.content.manage')<a class="admin-btn admin-btn-ghost lead-open" href="{{ route('admin.page-sections.edit', $section) }}">Edit</a>@endcan
                        </div></td>
                    </tr>
                @empty
                    <tr><td colspan="5"><div class="admin-empty">No sections match this view.</div></td></tr>
                @endforelse
                </tbody>
            </table>
        </div>
        {{ $sections->links() }}
    </div>
</section>
@endsection
