@extends('layouts.admin')
@section('title', 'Packages')
@section('breadcrumb', 'Admin / Packages')
@section('heading', 'Packages & pricing')
@section('content')
<div style="display:flex;justify-content:space-between;gap:1rem;margin-bottom:1rem;flex-wrap:wrap;">
<form method="GET" style="display:flex;gap:.5rem;flex-wrap:wrap;">
<input class="admin-input" name="q" value="{{ request('q') }}" placeholder="Search">
<select class="admin-select" name="category" style="width:auto;">
<option value="">All categories</option>
@foreach($categories as $key => $label)<option value="{{ $key }}" @selected(request('category')===$key)>{{ $label }}</option>@endforeach
</select>
<button class="admin-btn admin-btn-ghost" type="submit">Filter</button>
</form>
@can('admin.packages.manage')<a class="admin-btn admin-btn-primary" href="{{ route('admin.packages.create') }}">Add package</a>@endcan
</div>
<div class="admin-card admin-table-wrap">
<table class="admin-table">
<thead><tr><th>Name</th><th>Category</th><th>Plans</th><th>Status</th><th></th></tr></thead>
<tbody>
@forelse($packages as $package)
<tr>
<td><strong>{{ $package->name }}</strong><div style="font-size:.8rem;color:#64748b;">{{ $package->slug }}</div></td>
<td>{{ $categories[$package->category] ?? $package->category }}</td>
<td>{{ $package->plans_count }}</td>
<td>{{ $package->is_active ? 'Active' : 'Inactive' }}</td>
<td><a class="admin-btn admin-btn-ghost" href="{{ route('admin.packages.show', $package) }}">View</a>
@can('admin.packages.manage')<a class="admin-btn admin-btn-ghost" href="{{ route('admin.packages.edit', $package) }}">Edit</a>@endcan</td>
</tr>
@empty
<tr><td colspan="5"><div class="admin-empty">No packages yet.</div></td></tr>
@endforelse
</tbody></table>
{{ $packages->links() }}
</div>
@endsection
