@extends('layouts.admin')
@section('title', 'Team members')
@section('breadcrumb', 'Admin / Team')
@section('heading', 'Team members')
@section('content')
<div style="display:flex;justify-content:space-between;gap:1rem;margin-bottom:1rem;">
    <form method="GET"><input class="admin-input" name="q" value="{{ request('q') }}" placeholder="Search team"></form>
    @can('admin.content.manage')<a class="admin-btn admin-btn-primary" href="{{ route('admin.team-members.create') }}">Add member</a>@endcan
</div>
<div class="admin-card admin-table-wrap">
<table class="admin-table">
<thead><tr><th>Name</th><th>Designation</th><th>Order</th><th>Status</th><th></th></tr></thead>
<tbody>
@forelse($members as $member)
<tr>
<td><strong>{{ $member->name }}</strong></td>
<td>{{ $member->designation }}</td>
<td>{{ $member->sort_order }}</td>
<td>{{ $member->is_active ? 'Active' : 'Inactive' }}</td>
<td style="white-space:nowrap;">
<a class="admin-btn admin-btn-ghost" href="{{ route('admin.team-members.show', $member) }}">View</a>
@can('admin.content.manage')<a class="admin-btn admin-btn-ghost" href="{{ route('admin.team-members.edit', $member) }}">Edit</a>@endcan
</td>
</tr>
@empty
<tr><td colspan="5"><div class="admin-empty">No team members yet.</div></td></tr>
@endforelse
</tbody>
</table>
{{ $members->links() }}
</div>
@endsection
