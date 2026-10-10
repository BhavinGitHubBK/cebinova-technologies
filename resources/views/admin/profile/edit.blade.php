@extends('layouts.admin')
@section('title', 'Profile')
@section('breadcrumb', 'Admin / Profile')
@section('heading', 'My profile')
@section('content')
<div class="form-grid">
    <form method="POST" action="{{ route('admin.profile.update') }}" class="admin-card admin-form">
        @csrf
        @method('PUT')
        <section class="form-block">
            <h2>Profile</h2>
            <div class="admin-field">
                <label class="admin-label" for="profile-name">Name</label>
                <input id="profile-name" class="admin-input" name="name" value="{{ old('name', $user->name) }}" required>
            </div>
            <div class="admin-field">
                <label class="admin-label" for="profile-email">Email</label>
                <input id="profile-email" class="admin-input" type="email" name="email" value="{{ old('email', $user->email) }}" required>
            </div>
            <p class="form-hint">Role: {{ $user->role?->label() }} · Last login: {{ $user->last_login_at ?: '—' }}</p>
        </section>
        <div class="form-footer">
            <button class="admin-btn admin-btn-primary" type="submit">Save profile</button>
        </div>
    </form>
    <form method="POST" action="{{ route('admin.profile.password') }}" class="admin-card admin-form">
        @csrf
        @method('PUT')
        <section class="form-block">
            <h2>Password</h2>
            <div class="admin-field">
                <label class="admin-label" for="profile-current">Current password</label>
                <input id="profile-current" class="admin-input" type="password" name="current_password" required>
            </div>
            <div class="admin-field">
                <label class="admin-label" for="profile-password">New password</label>
                <input id="profile-password" class="admin-input" type="password" name="password" required>
            </div>
            <div class="admin-field">
                <label class="admin-label" for="profile-confirm">Confirm password</label>
                <input id="profile-confirm" class="admin-input" type="password" name="password_confirmation" required>
            </div>
        </section>
        <div class="form-footer">
            <button class="admin-btn admin-btn-accent" type="submit">Change password</button>
        </div>
    </form>
</div>
@endsection
