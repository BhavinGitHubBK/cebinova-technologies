@extends('layouts.admin')
@section('title','Business command center')
@section('heading','Business command center')
@section('content')
<p>Period: {{ $range->from->format('d M Y') }}–{{ $range->to->format('d M Y') }} · {{ config('app.timezone') }}.</p>
<form method="GET" class="admin-card" style="display:flex;gap:.65rem;flex-wrap:wrap;align-items:end;margin-bottom:1rem;">@include('admin.reports.range')<button class="admin-btn admin-btn-primary">Update dashboard</button><a class="admin-btn admin-btn-ghost" href="{{ route('admin.reports.index') }}">Reports and exports</a></form>
<div class="admin-card" style="margin-bottom:1rem;">
    <p>Leads created in period</p>
    <h2>{{ $leadTotal }}</h2>
    <p>{{ $leadChange === null ? 'No comparable previous-period data' : (($leadChange > 0 ? '+' : '').$leadChange.'% vs preceding equal-length period') }}</p>
    <p>Previous period: {{ $previousLeads }}</p>
</div>
<div class="admin-card" style="margin-bottom:1rem;">
    <h3>Created-period lead cohort</h3>
    @forelse($leadCounts as $status=>$count)<p>{{ $status }}: {{ $count }}</p>@empty<p>No data available for selected period</p>@endforelse
    <p>Lead → Won: {{ $wonRate === null ? 'No cohort data' : $wonRate.'%' }}</p>
    <p>Qualified / cohort: {{ $qualifiedRate === null ? 'No cohort data' : $qualifiedRate.'%' }}</p>
</div>
<div class="admin-card admin-table-wrap" style="margin-bottom:1rem;">
    <h3>Current pipeline</h3>
    @php
        $stages = collect($pipeline);
        if (request()->filled('sort')) {
            [$pipeSort, $pipeDir] = admin_sort(request(), ['status', 'total', 'estimated_value'], 'status');
            $stages = $pipeDir === 'desc' ? $stages->sortByDesc($pipeSort) : $stages->sortBy($pipeSort);
        }
    @endphp
    <table class="admin-table lead-table"><thead><tr>
        <th><x-admin.sort column="status" label="Stage" :sort="request('sort', '')" :dir="request('dir', 'asc')" /></th>
        <th><x-admin.sort column="total" label="Leads" :sort="request('sort', '')" :dir="request('dir', 'asc')" /></th>
        <th><x-admin.sort column="estimated_value" label="Estimated value" :sort="request('sort', '')" :dir="request('dir', 'asc')" /></th>
    </tr></thead>
    <tbody>@forelse($stages as $stage)<tr><td>{{ $stage->status }}</td><td>{{ $stage->total }}</td><td>{{ cebinova_inr((int) $stage->estimated_value) }}</td></tr>@empty<tr><td colspan="3">No data available for selected period</td></tr>@endforelse</tbody></table>
</div>
<div class="admin-card" style="margin-bottom:1rem;">
    <h3>Lead sources</h3>
    @forelse($sources as $group)<p>{{ $group->source ?: 'Not recorded' }}: {{ $group->total }}</p>@empty<p>No data available for selected period</p>@endforelse
</div>
<div class="admin-card" style="margin-bottom:1rem;">
    <h3>Service interest</h3>
    @forelse($services as $group)<p>{{ $group->service ?: 'Not recorded' }}: {{ $group->total }}</p>@empty<p>No data available for selected period</p>@endforelse
</div>
<section class="admin-card">
    <h2>Overdue follow-ups</h2>
    @forelse($attention['followups'] as $record)
        <p><a href="{{ route('admin.leads.show', $record->lead) }}">{{ $record->title }}</a> · {{ $record->due_at?->format('d M Y') }}</p>
    @empty
        <p>No records needing attention.</p>
    @endforelse
</section>
@endsection
