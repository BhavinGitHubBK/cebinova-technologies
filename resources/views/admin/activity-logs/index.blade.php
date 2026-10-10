@extends('layouts.admin')

@section('title', 'Activity logs')
@section('breadcrumb', 'Admin / Activity')
@section('heading', 'Activity logs')

@section('content')
@php
    $action = request('action', '');
    $filters = request()->only(['q', 'module', 'user', 'from', 'to', 'sort', 'dir']);
    $actionUrl = fn (string $next) => route('admin.activity-logs.index', array_filter([
        ...$filters,
        'action' => $next !== '' ? $next : null,
    ]));
    $badge = fn (?string $name) => match ($name) {
        'create', 'restore' => 'is-won',
        'update' => 'is-new',
        'delete' => 'is-lost',
        'login' => 'is-contacted',
        'logout' => 'is-off',
        'password_change', 'password_reset', 'bulk_status' => 'is-follow',
        default => 'is-off',
    };
    $label = fn (?string $name) => $name ? ucwords(str_replace(['-', '_'], ' ', $name)) : '—';
    $hasFilters = request()->hasAny(['q', 'module', 'user', 'action', 'from', 'to']);
@endphp
<p class="page-note">Each row is one saved change. Open the field link to see the old and new values.</p>
<section class="lead-board solution-board activity-board">
    <div class="solution-summary">
        <a class="solution-count {{ $action === '' ? 'is-current' : '' }}" href="{{ $actionUrl('') }}"><strong>{{ $counts['all'] }}</strong><span>events</span></a>
        <a class="solution-count {{ $action === 'update' ? 'is-current' : '' }}" href="{{ $actionUrl('update') }}"><strong>{{ $counts['update'] }}</strong><span>updates</span></a>
        <a class="solution-count is-active {{ $action === 'create' ? 'is-current' : '' }}" href="{{ $actionUrl('create') }}"><strong>{{ $counts['create'] }}</strong><span>created</span></a>
        <a class="solution-count is-removed {{ $action === 'delete' ? 'is-current' : '' }}" href="{{ $actionUrl('delete') }}"><strong>{{ $counts['delete'] }}</strong><span>removed</span></a>
    </div>
    <div class="admin-card solution-toolbar">
        <form method="GET" action="{{ route('admin.activity-logs.index') }}" class="activity-filters">
            @if (request('sort'))<input type="hidden" name="sort" value="{{ request('sort') }}">@endif
            @if (request('dir'))<input type="hidden" name="dir" value="{{ request('dir') }}">@endif
            <div class="admin-field is-search">
                <label class="admin-label" for="activity-search">Search</label>
                <input id="activity-search" class="admin-input" name="q" value="{{ request('q') }}" placeholder="Description">
            </div>
            <div class="admin-field">
                <label class="admin-label" for="activity-user">Who</label>
                <select id="activity-user" class="admin-select" name="user">
                    <option value="">All users</option>
                    @foreach ($people as $person)
                        <option value="{{ $person->id }}" @selected((string) request('user') === (string) $person->id)>{{ $person->name }}</option>
                    @endforeach
                </select>
            </div>
            <div class="admin-field">
                <label class="admin-label" for="activity-module">Module</label>
                <select id="activity-module" class="admin-select" name="module">
                    <option value="">All modules</option>
                    @foreach ($modules as $module)
                        <option value="{{ $module }}" @selected(request('module') === $module)>{{ $label($module) }}</option>
                    @endforeach
                </select>
            </div>
            <div class="admin-field">
                <label class="admin-label" for="activity-action">Action</label>
                <select id="activity-action" class="admin-select" name="action">
                    <option value="">All actions</option>
                    @foreach ($actions as $item)
                        <option value="{{ $item }}" @selected($action === $item)>{{ $label($item) }}</option>
                    @endforeach
                </select>
            </div>
            <div class="admin-field">
                <label class="admin-label" for="activity-from">From</label>
                <input id="activity-from" class="admin-input" type="date" name="from" value="{{ request('from') }}">
            </div>
            <div class="admin-field">
                <label class="admin-label" for="activity-to">To</label>
                <input id="activity-to" class="admin-input" type="date" name="to" value="{{ request('to') }}">
            </div>
            <button class="admin-btn admin-btn-primary" type="submit">Search</button>
            @if ($hasFilters)
                <a class="solution-clear" href="{{ route('admin.activity-logs.index') }}">Clear</a>
            @endif
        </form>
    </div>
    <div class="admin-card solution-list">
        <div class="admin-table-wrap">
            <table class="admin-table lead-table">
                <thead><tr>
                    <th><x-admin.sort column="created_at" label="When" :sort="$sort" :dir="$dir" desc-first /></th>
                    <th><x-admin.sort column="user" label="Who" :sort="$sort" :dir="$dir" /></th>
                    <th><x-admin.sort column="description" label="What changed" :sort="$sort" :dir="$dir" /></th>
                    <th>Where</th>
                    <th></th>
                </tr></thead>
                <tbody>
                @forelse ($logs as $log)
                    @php
                        $story = $log->story();
                        $place = in_array($log->location, [null, '', 'Not available'], true) ? null : $log->location;
                        $where = collect([$place, $log->ip_address, $log->device(), $story['page'] ? 'From '.$story['page'] : null])->filter()->implode(' · ');
                        $storyTitle = match ($story['mode']) {
                            'add' => count($story['rows']).' '.(count($story['rows']) === 1 ? 'detail' : 'details').' added',
                            'remove' => 'What was removed',
                            default => count($story['rows']).' '.(count($story['rows']) === 1 ? 'field' : 'fields').' changed',
                        };
                    @endphp
                    <tr>
                        <td class="lead-when">{{ $log->created_at?->timezone(config('app.timezone'))->format('d M Y, h:i A') }}</td>
                        <td>
                            <div class="activity-person">{{ $log->user?->name ?? 'Unknown' }}</div>
                        </td>
                        <td>
                            <div class="activity-note">{{ $log->description }}</div>
                            <div class="activity-line">
                                <span class="admin-badge {{ $badge($log->action) }}">{{ $label($log->action) }}</span>
                                <span class="activity-meta">{{ $label($log->module) }}</span>
                                @if ($story['rows'] !== [])
                                    <button type="button" class="activity-open" data-activity-open data-story="{{ json_encode(['title' => $storyTitle, 'mode' => $story['mode'], 'rows' => $story['rows']]) }}">{{ $storyTitle }}</button>
                                @endif
                            </div>
                        </td>
                        <td class="activity-where">{{ $where !== '' ? $where : '—' }}</td>
                        <td>
                            @php $link = $log->recordLink(); @endphp
                            @if ($link)
                                <a class="admin-btn admin-btn-ghost lead-open" href="{{ $link['url'] }}">{{ $link['label'] }}</a>
                            @endif
                        </td>
                    </tr>
                @empty
                    <tr><td colspan="5"><div class="admin-empty">No activity matches this view.</div></td></tr>
                @endforelse
                </tbody>
            </table>
        </div>
        @if ($logs->total() > 0)
            <nav class="activity-pager" aria-label="Activity pages">
                <p>Showing {{ $logs->firstItem() }}–{{ $logs->lastItem() }} of {{ $logs->total() }}</p>
                @if ($logs->hasPages())
                    <div>
                        @if ($logs->onFirstPage())
                            <span class="is-disabled">Previous</span>
                        @else
                            <a href="{{ $logs->previousPageUrl() }}">Previous</a>
                        @endif
                        @foreach ($logs->getUrlRange(max(1, $logs->currentPage() - 2), min($logs->lastPage(), $logs->currentPage() + 2)) as $page => $url)
                            <a href="{{ $url }}" @class(['is-current' => $page === $logs->currentPage()])>{{ $page }}</a>
                        @endforeach
                        @if ($logs->hasMorePages())
                            <a href="{{ $logs->nextPageUrl() }}">Next</a>
                        @else
                            <span class="is-disabled">Next</span>
                        @endif
                    </div>
                @endif
            </nav>
        @endif
    </div>
</section>
<style>
    .activity-filters { display: flex; flex-wrap: wrap; align-items: flex-end; gap: .7rem; width: 100%; }
    .activity-filters .admin-field { flex: 1 1 9rem; margin: 0; min-width: 8.5rem; }
    .activity-filters .is-search { flex: 1.6 1 14rem; }
    .activity-filters .admin-btn { margin-bottom: .05rem; }
    .activity-line { display: flex; flex-wrap: wrap; align-items: center; gap: .4rem .7rem; margin-top: .35rem; }
    .activity-board .activity-open { margin-top: 0; }
    .activity-where { color: #64748b; font-size: .86rem; line-height: 1.45; max-width: 18rem; }
    .activity-board .lead-when { white-space: nowrap; }
    .activity-pager { display: flex; flex-wrap: wrap; align-items: center; justify-content: space-between; gap: .75rem; }
    .activity-pager p { margin: 0; color: #64748b; font-size: .86rem; font-weight: 600; }
    .activity-pager div { display: flex; flex-wrap: wrap; gap: .35rem; }
    .activity-pager a, .activity-pager span { display: inline-flex; align-items: center; min-height: 2rem; padding: .25rem .7rem; border: 1px solid #e6ebf2; background: #fff; color: #0b2c5f; font-size: .84rem; font-weight: 700; text-decoration: none; }
    .activity-pager a:hover, .activity-pager a.is-current { background: #013a9d; border-color: #013a9d; color: #fff; }
    .activity-pager .is-disabled { color: #94a3b8; background: #f8fafc; }
</style>
@endsection
