@extends('layouts.admin')
@section('title', $user->exists ? 'Edit user' : 'Create user')
@section('breadcrumb', 'Admin / Users / Form')
@section('heading', $user->exists ? 'Edit user' : 'Create user')
@section('content')
<form method="POST" action="{{ $user->exists ? route('admin.users.update', $user) : route('admin.users.store') }}" class="admin-card">
@csrf
@if($user->exists) @method('PUT') @endif
<div class="admin-field"><label class="admin-label">Name</label><input class="admin-input" name="name" value="{{ old('name', $user->name) }}" required></div>
<div class="admin-field"><label class="admin-label">Email</label><input class="admin-input" type="email" name="email" value="{{ old('email', $user->email) }}" required></div>
@unless($user->exists)
<div class="admin-field"><label class="admin-label">Password</label><input class="admin-input" type="password" name="password" required></div>
<div class="admin-field"><label class="admin-label">Confirm password</label><input class="admin-input" type="password" name="password_confirmation" required></div>
@endunless
<div class="admin-field"><label class="admin-label">Role</label>
<select class="admin-select" name="role" required>
@foreach($roles as $role)
<option value="{{ $role->value }}" @selected(old('role', $user->role?->value) === $role->value)>{{ $role->label() }}</option>
@endforeach
</select></div>
<label style="display:flex;gap:.4rem;margin-bottom:1rem;"><input type="checkbox" name="is_active" value="1" @checked(old('is_active', $user->is_active ?? true))> Active</label>
<button class="admin-btn admin-btn-primary" type="submit">Save</button>
</form>
@endsection
