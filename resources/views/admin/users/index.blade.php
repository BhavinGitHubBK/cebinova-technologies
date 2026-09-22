@extends('layouts.admin')
@section('title', 'Users')
@section('breadcrumb', 'Admin / Users')
@section('heading', 'Users & roles')
@section('content')
@can('admin.users.manage')
<div style="margin-bottom:1rem;"><a class="admin-btn admin-btn-primary" href="{{ route('admin.users.create') }}">Add user</a></div>
@endcan
<div class="admin-card admin-table-wrap">
<table class="admin-table">
<thead><tr><th>Name</th><th>Email</th><th>Role</th><th>Active</th><th>Last login</th><th></th></tr></thead>
<tbody>
@foreach($users as $user)
<tr>
<td>{{ $user->name }}</td>
<td>{{ $user->email }}</td>
<td>{{ $user->role?->label() }}</td>
<td>{{ $user->is_active ? 'Yes' : 'No' }}</td>
<td>{{ $user->last_login_at?->diffForHumans() ?: '—' }}<div style="font-size:.75rem;color:#64748b;">{{ $user->last_login_ip }}</div></td>
<td>
@can('admin.users.manage')
<a class="admin-btn admin-btn-ghost" href="{{ route('admin.users.edit', $user) }}">Edit</a>
<form method="POST" action="{{ route('admin.users.reset-password', $user) }}" style="display:inline;">@csrf
<button class="admin-btn admin-btn-ghost" data-confirm="Reset password?" type="submit">Reset PW</button></form>
<form method="POST" action="{{ route('admin.users.destroy', $user) }}" style="display:inline;">@csrf @method('DELETE')
<button class="admin-btn admin-btn-danger" data-confirm="Delete user?" type="submit">Delete</button></form>
@endcan
</td>
</tr>
@endforeach
</tbody></table>
{{ $users->links() }}
</div>
@endsection
