@extends('layouts.admin')

@section('title', 'Users')
@section('breadcrumb', 'Admin / Users')
@section('heading', 'Users & roles')

@section('content')
@php
    $status = $status ?? '';
    $search = request('q');
    $role = request('role');
    $statusUrl = fn (string $next) => route('admin.users.index', array_filter([
        'q' => $search,
        'role' => $role,
        'status' => $next !== '' ? $next : null,
    ]));
@endphp

<section class="lead-board solution-board">
    <div class="solution-summary">
        <a class="solution-count {{ $status === '' ? 'is-current' : '' }}" href="{{ $statusUrl('') }}"><strong>{{ $counts['all'] }}</strong><span>users</span></a>
        <a class="solution-count is-active {{ $status === 'active' ? 'is-current' : '' }}" href="{{ $statusUrl('active') }}"><strong>{{ $counts['active'] }}</strong><span>active</span></a>
        <a class="solution-count is-off {{ $status === 'inactive' ? 'is-current' : '' }}" href="{{ $statusUrl('inactive') }}"><strong>{{ $counts['inactive'] }}</strong><span>inactive</span></a>
    </div>
    <div class="admin-card solution-toolbar">
        <form method="GET" action="{{ route('admin.users.index') }}" class="solution-search">
            @if ($status !== '')<input type="hidden" name="status" value="{{ $status }}">@endif
            <div class="admin-field">
                <label class="admin-label" for="user-search">Search</label>
                <input id="user-search" class="admin-input" name="q" value="{{ $search }}" placeholder="Name or email">
            </div>
            <div class="admin-field">
                <label class="admin-label" for="user-role">Role</label>
                <select id="user-role" class="admin-select" name="role">
                    <option value="">All roles</option>
                    @foreach ($roles as $option)
                        <option value="{{ $option->value }}" @selected($role === $option->value)>{{ $option->label() }}</option>
                    @endforeach
                </select>
            </div>
            <button class="admin-btn admin-btn-primary" type="submit">Search</button>
            @if (filled($search) || filled($role) || $status !== '')<a class="solution-clear" href="{{ route('admin.users.index') }}">Clear</a>@endif
        </form>
        @can('admin.users.manage')<a class="admin-btn admin-btn-primary" href="{{ route('admin.users.create') }}">Add user</a>@endcan
    </div>
    <div class="admin-card solution-list">
        <div class="admin-table-wrap">
            <table class="admin-table lead-table">
                <thead><tr>
                    <th><x-admin.sort column="name" label="Name" :sort="$sort" :dir="$dir" /></th>
                    <th><x-admin.sort column="email" label="Email" :sort="$sort" :dir="$dir" /></th>
                    <th><x-admin.sort column="role" label="Role" :sort="$sort" :dir="$dir" /></th>
                    <th><x-admin.sort column="is_active" label="Status" :sort="$sort" :dir="$dir" /></th>
                    <th><x-admin.sort column="last_login_at" label="Last login" :sort="$sort" :dir="$dir" desc-first /></th>
                    <th></th>
                </tr></thead>
                <tbody>
                @forelse ($users as $user)
                    <tr>
                        <td><strong>{{ $user->name }}</strong></td>
                        <td><a href="mailto:{{ $user->email }}">{{ $user->email }}</a></td>
                        <td>{{ $user->role?->label() }}</td>
                        <td><span class="admin-badge {{ $user->is_active ? 'is-won' : 'is-off' }}">{{ $user->is_active ? 'Active' : 'Inactive' }}</span></td>
                        <td class="lead-when">
                            {{ $user->last_login_at?->timezone(config('app.timezone'))->format('d M Y, h:i A') ?: '—' }}
                            @if ($user->last_login_ip)<span class="lead-meta">{{ $user->last_login_ip }}</span>@endif
                        </td>
                        <td>
                            @can('admin.users.manage')
                                <div class="solution-row-actions">
                                    <a class="admin-btn admin-btn-ghost lead-open" href="{{ route('admin.users.edit', $user) }}">Edit</a>
                                    <form method="POST" action="{{ route('admin.users.reset-password', $user) }}">
                                        @csrf
                                        <button class="admin-btn admin-btn-ghost lead-open" type="submit" data-confirm="Reset this user's password?">Reset password</button>
                                    </form>
                                    <form method="POST" action="{{ route('admin.users.destroy', $user) }}">
                                        @csrf @method('DELETE')
                                        <button class="admin-btn admin-btn-danger lead-open" type="submit" data-confirm="Delete this user?">Delete</button>
                                    </form>
                                </div>
                            @endcan
                        </td>
                    </tr>
                @empty
                    <tr><td colspan="6"><div class="admin-empty">No users match this view.</div></td></tr>
                @endforelse
                </tbody>
            </table>
        </div>
        {{ $users->links() }}
    </div>
</section>
@endsection
