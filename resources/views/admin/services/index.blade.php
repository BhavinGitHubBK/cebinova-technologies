@extends('layouts.admin')
@section('title', 'Services')
@section('breadcrumb', 'Admin / Services')
@section('heading', 'Services')
@section('content')
<div style="display:flex;justify-content:space-between;gap:1rem;margin-bottom:1rem;">
    <form method="GET"><input class="admin-input" name="q" value="{{ request('q') }}" placeholder="Search services"></form>
    @can('admin.services.manage')<a class="admin-btn admin-btn-primary" href="{{ route('admin.services.create') }}">Add service</a>@endcan
</div>
<div class="admin-card admin-table-wrap">
<table class="admin-table">
<thead><tr><th>Name</th><th>Category</th><th>Order</th><th>Status</th><th></th></tr></thead>
<tbody>
@forelse($services as $service)
<tr>
<td><strong>{{ $service->name }}</strong><div style="font-size:.8rem;color:#64748b;">{{ $service->slug }}</div></td>
<td>{{ $service->category }}</td>
<td>{{ $service->sort_order }}</td>
<td>{{ $service->is_active ? 'Active' : 'Inactive' }}</td>
<td style="white-space:nowrap;">
<a class="admin-btn admin-btn-ghost" href="{{ route('admin.services.show', $service) }}">View</a>
@can('admin.services.manage')<a class="admin-btn admin-btn-ghost" href="{{ route('admin.services.edit', $service) }}">Edit</a>@endcan
</td>
</tr>
@empty
<tr><td colspan="5"><div class="admin-empty">No services yet. Seed or create one.</div></td></tr>
@endforelse
</tbody>
</table>
{{ $services->links() }}
</div>
@endsection
