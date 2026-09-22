@extends('layouts.admin')
@section('title', 'Activity logs')
@section('breadcrumb', 'Admin / Activity')
@section('heading', 'Activity logs')
@section('content')
<form method="GET" class="admin-card" style="margin-bottom:1rem;display:flex;gap:.5rem;flex-wrap:wrap;">
<input class="admin-input" name="q" value="{{ request('q') }}" placeholder="Search description" style="max-width:220px;">
<input class="admin-input" name="module" value="{{ request('module') }}" placeholder="Module" style="max-width:140px;">
<input class="admin-input" name="action" value="{{ request('action') }}" placeholder="Action" style="max-width:140px;">
<button class="admin-btn admin-btn-primary" type="submit">Filter</button>
</form>
<div class="admin-card admin-table-wrap">
<table class="admin-table">
<thead><tr><th>When</th><th>User</th><th>Action</th><th>Module</th><th>Description</th><th>IP</th></tr></thead>
<tbody>
@forelse($logs as $log)
<tr>
<td>{{ $log->created_at }}</td>
<td>{{ $log->user?->name ?? '—' }}</td>
<td>{{ $log->action }}</td>
<td>{{ $log->module }}</td>
<td>{{ $log->description }}</td>
<td>{{ $log->ip_address }}</td>
</tr>
@empty
<tr><td colspan="6"><div class="admin-empty">No activity yet.</div></td></tr>
@endforelse
</tbody></table>
{{ $logs->links() }}
</div>
@endsection
