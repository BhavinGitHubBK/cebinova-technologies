@extends('layouts.admin')
@section('title', $user->exists ? 'Edit user' : 'Create user')
@section('breadcrumb', 'Admin / Users / Form')
@section('heading', $user->exists ? 'Edit user' : 'Create user')
@section('content')
<form method="POST" action="{{ $user->exists ? route('admin.users.update', $user) : route('admin.users.store') }}" class="admin-card admin-form">
    @csrf
    @if($user->exists) @method('PUT') @endif

    <section class="form-block">
        <h2>Account</h2>
        <div class="form-grid">
            <div class="admin-field">
                <label class="admin-label" for="user-name">Name</label>
                <input id="user-name" class="admin-input" name="name" value="{{ old('name', $user->name) }}" required>
            </div>
            <div class="admin-field">
                <label class="admin-label" for="user-email">Email</label>
                <input id="user-email" class="admin-input" type="email" name="email" value="{{ old('email', $user->email) }}" required>
            </div>
            @unless($user->exists)
                <div class="admin-field">
                    <label class="admin-label" for="user-password">Password</label>
                    <input id="user-password" class="admin-input" type="password" name="password" required>
                </div>
                <div class="admin-field">
                    <label class="admin-label" for="user-password-confirm">Confirm password</label>
                    <input id="user-password-confirm" class="admin-input" type="password" name="password_confirmation" required>
                </div>
            @endunless
            <div class="admin-field">
                <label class="admin-label" for="user-role">Role</label>
                <select id="user-role" class="admin-select" name="role" required>
                    @foreach($roles as $role)
                        <option value="{{ $role->value }}" @selected(old('role', $user->role?->value) === $role->value)>{{ $role->label() }}</option>
                    @endforeach
                </select>
            </div>
        </div>
    </section>

    <div class="form-footer">
        <label class="admin-check"><input type="checkbox" name="is_active" value="1" @checked(old('is_active', $user->is_active ?? true))> Active</label>
        <button class="admin-btn admin-btn-primary" type="submit">Save</button>
    </div>
</form>
@endsection
