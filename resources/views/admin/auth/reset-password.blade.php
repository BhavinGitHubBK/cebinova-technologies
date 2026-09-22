@extends('layouts.admin-guest')

@section('title', 'Reset password')
@section('kicker', 'Account recovery')
@section('heading', 'Choose a new password')
@section('subtitle', 'Use a strong password you have not used elsewhere.')
@section('content')
<form method="POST" action="{{ route('admin.password.store') }}" class="admin-auth-form">
    @csrf
    <input type="hidden" name="token" value="{{ $token }}">
    <div class="admin-field">
        <label class="admin-label" for="email">Email</label>
        <input class="admin-input" id="email" type="email" name="email" value="{{ old('email', $email) }}" required autocomplete="username">
    </div>
    <div class="admin-field">
        <label class="admin-label" for="password">New password</label>
        <div class="admin-password">
            <input class="admin-input" id="password" type="password" name="password" required autocomplete="new-password">
            <button type="button" class="admin-password-toggle" data-password-toggle aria-label="Show password" aria-pressed="false" title="Show password">
                <svg class="admin-password-icon admin-password-icon--show" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M2.5 12s3.5-6.5 9.5-6.5S21.5 12 21.5 12s-3.5 6.5-9.5 6.5S2.5 12 2.5 12Z"/><circle cx="12" cy="12" r="2.75"/></svg>
                <svg class="admin-password-icon admin-password-icon--hide" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M3 3l18 18M10.6 10.6a2.75 2.75 0 0 0 3.8 3.8M9.5 5.3A10.4 10.4 0 0 1 12 5.5c6 0 9.5 6.5 9.5 6.5a16.7 16.7 0 0 1-2.2 2.8M6.2 6.2C4 7.8 2.5 12 2.5 12S6 18.5 12 18.5a10 10 0 0 0 3.3-.5"/></svg>
            </button>
        </div>
    </div>
    <div class="admin-field">
        <label class="admin-label" for="password_confirmation">Confirm password</label>
        <div class="admin-password">
            <input class="admin-input" id="password_confirmation" type="password" name="password_confirmation" required autocomplete="new-password">
            <button type="button" class="admin-password-toggle" data-password-toggle aria-label="Show password" aria-pressed="false" title="Show password">
                <svg class="admin-password-icon admin-password-icon--show" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M2.5 12s3.5-6.5 9.5-6.5S21.5 12 21.5 12s-3.5 6.5-9.5 6.5S2.5 12 2.5 12Z"/><circle cx="12" cy="12" r="2.75"/></svg>
                <svg class="admin-password-icon admin-password-icon--hide" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M3 3l18 18M10.6 10.6a2.75 2.75 0 0 0 3.8 3.8M9.5 5.3A10.4 10.4 0 0 1 12 5.5c6 0 9.5 6.5 9.5 6.5a16.7 16.7 0 0 1-2.2 2.8M6.2 6.2C4 7.8 2.5 12 2.5 12S6 18.5 12 18.5a10 10 0 0 0 3.3-.5"/></svg>
            </button>
        </div>
    </div>
    <button class="admin-btn admin-btn-primary admin-auth-submit" type="submit">Reset password</button>
</form>
@endsection
