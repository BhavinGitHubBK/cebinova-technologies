@extends('layouts.admin')
@section('title', 'Projects')
@section('breadcrumb', 'Admin / Projects')
@section('heading', 'Projects')
@section('content')
<div style="display:flex;justify-content:space-between;gap:1rem;margin-bottom:1rem;">
    <form method="GET"><input class="admin-input" name="q" value="{{ request('q') }}" placeholder="Search projects"></form>
    @can('admin.content.manage')<a class="admin-btn admin-btn-primary" href="{{ route('admin.projects.create') }}">Add project</a>@endcan
</div>
<div class="admin-card admin-table-wrap">
<table class="admin-table">
<thead><tr><th>Title</th><th>Type</th><th>Order</th><th>Status</th><th></th></tr></thead>
<tbody>
@forelse($projects as $project)
<tr>
<td><strong>{{ $project->title }}</strong><div style="font-size:.8rem;color:#64748b;">{{ $project->slug }}</div></td>
<td>{{ $project->type }}</td>
<td>{{ $project->sort_order }}</td>
<td>{{ $project->status }}</td>
<td style="white-space:nowrap;">
<a class="admin-btn admin-btn-ghost" href="{{ route('admin.projects.show', $project) }}">View</a>
@can('admin.content.manage')<a class="admin-btn admin-btn-ghost" href="{{ route('admin.projects.edit', $project) }}">Edit</a>@endcan
</td>
</tr>
@empty
<tr><td colspan="5"><div class="admin-empty">No projects yet. Seed or create one.</div></td></tr>
@endforelse
</tbody>
</table>
{{ $projects->links() }}
</div>
@endsection
