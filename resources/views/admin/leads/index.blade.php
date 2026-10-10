@extends('layouts.admin')

@section('title', 'Leads')
@section('breadcrumb', 'Admin / Leads')
@section('heading', 'Leads & enquiries')

@section('content')
@php
    $statusClass = fn (string $status) => match ($status) {
        'New' => 'is-new',
        'Contacted' => 'is-contacted',
        'Follow-up', 'Qualified', 'Proposal Sent' => 'is-follow',
        'Converted', 'Won' => 'is-won',
        'Lost' => 'is-lost',
        default => '',
    };
    $activeStatus = $filters['status'] ?? '';
    $hasFilters = collect($filters)->except(['sort', 'dir'])->contains(fn ($value) => filled($value));
    $sort = $sort ?? 'created_at';
    $dir = $dir ?? 'desc';
    $sortUrl = function (string $column) use ($filters, $sort, $dir) {
        $next = $sort === $column && $dir === 'asc' ? 'desc' : 'asc';
        if ($column === 'created_at' && $sort !== $column) {
            $next = 'desc';
        }

        return route('admin.leads.index', array_merge($filters, ['sort' => $column, 'dir' => $next]));
    };
@endphp

<section class="lead-board">
    <div class="lead-summary">
        <p><strong>{{ $leads->total() }}</strong><span>in this view</span></p>
        <p class="is-new"><strong data-new-count>{{ $newCount }}</strong><span>new, not contacted yet</span></p>
        @if ($hasFilters)
            <a href="{{ route('admin.leads.index') }}">Clear filters</a>
        @endif
    </div>

    <div class="admin-card lead-filters is-collapsed" data-lead-filters>
        <div class="lead-filter-bar">
            <button type="button" class="lead-filter-toggle" data-filter-toggle aria-expanded="false" aria-controls="lead-filter-panel">
                Filters
                @if ($hasFilters)
                    <span>On</span>
                @endif
                <x-mark name="chevron" class="h-4 w-4" />
            </button>
            <a class="admin-btn admin-btn-ghost" href="{{ route('admin.leads.export', request()->query()) }}">Export CSV</a>
        </div>
        <form method="GET" id="lead-filter-panel" class="lead-filter-body" hidden>
        <div class="lead-filter-grid">
            <div class="admin-field">
                <label class="admin-label" for="lead-search">Search</label>
                <input id="lead-search" class="admin-input" name="q" value="{{ $filters['q'] ?? '' }}" placeholder="Name, phone, or email">
            </div>
            <div class="admin-field">
                <label class="admin-label" for="lead-service">Service</label>
                <select id="lead-service" class="admin-select" name="service">
                    <option value="">All</option>
                    @foreach ($serviceOptions as $option)
                        <option value="{{ $option }}" @selected(($filters['service'] ?? '') === $option)>{{ $option }}</option>
                    @endforeach
                </select>
            </div>
            <div class="admin-field">
                <label class="admin-label" for="lead-package">Package</label>
                <select id="lead-package" class="admin-select" name="package_category">
                    <option value="">All</option>
                    @foreach ($packageOptions as $option)
                        <option value="{{ $option }}" @selected(($filters['package_category'] ?? '') === $option)>{{ $option }}</option>
                    @endforeach
                </select>
            </div>
            <div class="admin-field">
                <label class="admin-label" for="lead-source">Source</label>
                <select id="lead-source" class="admin-select" name="source">
                    <option value="">All</option>
                    @foreach ($sourceOptions as $option)
                        <option value="{{ $option }}" @selected(($filters['source'] ?? '') === $option)>{{ $option }}</option>
                    @endforeach
                </select>
            </div>
            <div class="admin-field">
                <label class="admin-label" for="lead-from">From</label>
                <input id="lead-from" class="admin-input" type="date" name="from" value="{{ $filters['from'] ?? '' }}">
            </div>
            <div class="admin-field">
                <label class="admin-label" for="lead-to">To</label>
                <input id="lead-to" class="admin-input" type="date" name="to" value="{{ $filters['to'] ?? '' }}">
            </div>
        </div>
        <div class="lead-filter-status">
            <span class="admin-label">Status</span>
            <div class="lead-choices">
                <label class="{{ $activeStatus === '' ? 'is-active' : '' }}">
                    <input type="radio" name="status" value="" @checked($activeStatus === '')>
                    All
                </label>
                @foreach ($statuses as $status)
                    <label class="{{ $activeStatus === $status ? 'is-active' : '' }}">
                        <input type="radio" name="status" value="{{ $status }}" @checked($activeStatus === $status)>
                        {{ $status }}
                    </label>
                @endforeach
            </div>
        </div>
        <div class="lead-filter-actions">
            <button class="admin-btn admin-btn-primary" type="submit">Filter</button>
        </div>
        </form>
    </div>

    <form method="POST" action="{{ route('admin.leads.bulk-status') }}" class="admin-card lead-list" data-lead-bulk>
        @csrf
        @can('admin.leads.manage')
            <div class="lead-bulk">
                <div class="admin-field">
                    <label class="admin-label" for="lead-bulk-status">Update selected</label>
                    <select id="lead-bulk-status" class="admin-select" name="status" required>
                        @foreach ($statuses as $status)
                            <option value="{{ $status }}">{{ $status }}</option>
                        @endforeach
                    </select>
                </div>
                <button class="admin-btn admin-btn-accent" type="submit">Update selected</button>
            </div>
        @endcan
        <div class="admin-table-wrap">
            <table class="admin-table lead-table">
                <thead>
                    <tr>
                        @can('admin.leads.manage')<th class="lead-check"></th>@endcan
                        @foreach (['name' => 'Name', 'phone' => 'Contact', 'service' => 'Service', 'status' => 'Status', 'source' => 'Source', 'created_at' => 'Created'] as $column => $label)
                            <th>
                                <a class="lead-sort {{ $sort === $column ? 'is-active' : '' }}" href="{{ $sortUrl($column) }}">
                                    {{ $label }}
                                    <span aria-hidden="true">{{ $sort === $column ? ($dir === 'asc' ? '↑' : '↓') : '↕' }}</span>
                                </a>
                            </th>
                        @endforeach
                        <th></th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($leads as $lead)
                        <tr data-lead-id="{{ $lead->id }}" data-lead-status="{{ $lead->status }}">
                            @can('admin.leads.manage')
                                <td class="lead-check"><input type="checkbox" name="ids[]" value="{{ $lead->id }}" aria-label="Select {{ $lead->name }}"></td>
                            @endcan
                            <td>
                                <a class="lead-name" href="{{ route('admin.leads.show', $lead) }}">{{ $lead->name }}</a>
                                @if ($lead->business_name)
                                    <span class="lead-meta">{{ $lead->business_name }}</span>
                                @endif
                            </td>
                            <td>
                                <div class="lead-contact">
                                    <a href="tel:{{ preg_replace('/\D+/', '', (string) $lead->phone) }}">{{ $lead->phone }}</a>
                                    @if ($lead->email)
                                        <a href="mailto:{{ $lead->email }}">{{ $lead->email }}</a>
                                    @endif
                                </div>
                            </td>
                            <td>
                                {{ $lead->service ?: '—' }}
                                @if ($lead->package_category)
                                    <span class="lead-meta">{{ $lead->package_category }}@if($lead->plan_duration) / {{ $lead->plan_duration }}@endif</span>
                                @endif
                            </td>
                            <td><span class="admin-badge {{ $statusClass($lead->status) }}" data-lead-badge>{{ $lead->status }}</span></td>
                            <td>{{ $lead->source ?: '—' }}</td>
                            <td class="lead-when">{{ $lead->created_at?->format('d M Y') }}</td>
                            <td class="lead-action"><a class="admin-btn admin-btn-ghost lead-open" href="{{ route('admin.leads.show', $lead) }}">Open</a></td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="8"><div class="admin-empty">No leads match your filters.</div></td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        {{ $leads->links() }}
    </form>
</section>
@endsection
