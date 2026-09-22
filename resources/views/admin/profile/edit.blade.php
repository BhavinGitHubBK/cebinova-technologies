@extends('layouts.admin')
@section('title', 'Profile')
@section('breadcrumb', 'Admin / Profile')
@section('heading', 'My profile')
@section('content')
<div style="display:grid;grid-template-columns:repeat(auto-fit,minmax(280px,1fr));gap:1rem;">
<form method="POST" action="{{ route('admin.profile.update') }}" class="admin-card">
@csrf @method('PUT')
<div class="admin-field"><label class="admin-label">Name</label><input class="admin-input" name="name" value="{{ old('name', $user->name) }}" required></div>
<div class="admin-field"><label class="admin-label">Email</label><input class="admin-input" type="email" name="email" value="{{ old('email', $user->email) }}" required></div>
<p style="font-size:.85rem;color:#64748b;">Role: {{ $user->role?->label() }} · Last login: {{ $user->last_login_at ?: '—' }}</p>
<button class="admin-btn admin-btn-primary" type="submit">Save profile</button>
</form>
<form method="POST" action="{{ route('admin.profile.password') }}" class="admin-card">
@csrf @method('PUT')
<div class="admin-field"><label class="admin-label">Current password</label><input class="admin-input" type="password" name="current_password" required></div>
<div class="admin-field"><label class="admin-label">New password</label><input class="admin-input" type="password" name="password" required></div>
<div class="admin-field"><label class="admin-label">Confirm password</label><input class="admin-input" type="password" name="password_confirmation" required></div>
<button class="admin-btn admin-btn-accent" type="submit">Change password</button>
</form>
</div>
@endsection
