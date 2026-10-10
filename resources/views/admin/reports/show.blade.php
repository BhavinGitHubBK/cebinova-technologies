@extends('layouts.admin')
@section('title',ucfirst($type).' report')
@section('heading',ucfirst($type).' report')
@section('content')
<p>{{ $range->from->format('d M Y') }} to {{ $range->to->format('d M Y') }} (inclusive) · Timezone {{ config('app.timezone') }}</p>
<p>Current statuses within the created-period lead cohort. Estimated value is a sales estimate, not collections.</p>
<form class="admin-card" method="GET" style="display:flex;gap:.65rem;flex-wrap:wrap;align-items:end;margin-bottom:1rem;">@include('admin.reports.range')
@if($statuses)<div class="admin-field" style="margin:0;"><label class="admin-label">Status</label><select class="admin-select" name="status"><option value="">All</option>@foreach($statuses as $status)<option @selected(request('status')===$status)>{{ $status }}</option>@endforeach</select></div>@endif
<div class="admin-field" style="margin:0;"><label class="admin-label">Assigned user</label><select class="admin-select" name="user_id"><option value="">All</option>@foreach($users as $user)<option value="{{ $user->id }}" @selected(request('user_id')==$user->id)>{{ $user->name }}</option>@endforeach</select></div>
@if($services->isNotEmpty())<div class="admin-field" style="margin:0;"><label class="admin-label">Service</label><select class="admin-select" name="service_id"><option value="">All</option>@foreach($services as $service)<option value="{{ $service->id }}" @selected(request('service_id')==$service->id)>{{ $service->name }}</option>@endforeach</select></div>@endif
<button class="admin-btn admin-btn-primary">Apply filters</button></form>
<p><a class="admin-btn admin-btn-ghost" href="{{ route('admin.reports.export',['type'=>$type,...request()->except('page')]) }}">Export filtered CSV</a> <a href="{{ route('admin.reports.index') }}">All reports</a> · {{ $rows->total() }} matching records</p>
@php
    $reportSorts = ['Lead' => 'name', 'Business' => 'business_name', 'Source' => 'source', 'Service interest' => 'service', 'Status' => 'status', 'Estimated value' => 'estimated_value', 'Assigned' => 'assigned', 'Created' => 'created_at'];
@endphp
<div class="admin-card admin-table-wrap"><table class="admin-table lead-table"><thead><tr>@foreach($columns as $column)<th><x-admin.sort :column="$reportSorts[$column] ?? $column" :label="$column" :sort="$sort" :dir="$dir" :desc-first="in_array($reportSorts[$column] ?? '', ['created_at', 'estimated_value'], true)" /></th>@endforeach<th>Record</th></tr></thead>
<tbody>@forelse($rows as $row)<tr>@foreach($reports->row($type,$row) as $cell)<td>{{ $cell }}</td>@endforeach<td><a href="{{ $reports->link($type,$row,auth()->user()) }}">Open</a></td></tr>@empty<tr><td colspan="{{ count($columns)+1 }}">No data available for selected period</td></tr>@endforelse</tbody></table>{{ $rows->links() }}</div>
@endsection
