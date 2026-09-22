@extends('layouts.admin-guest')

@section('title', 'Login')
@section('kicker', 'Secure admin access')
@section('heading', 'Welcome back')
@section('subtitle', 'Sign in to manage CEBINOVA leads, CMS and packages.')
@section('content')
<form method="POST" action="{{ route('admin.login.store') }}" class="admin-auth-form">
    @csrf
    <div class="admin-field">
        <label class="admin-label" for="email">Email</label>
        <input class="admin-input" id="email" type="email" name="email" value="{{ old('email') }}" required autofocus autocomplete="username" placeholder="admin@cebinova.test">
    </div>
    <div class="admin-field">
        <label class="admin-label" for="password">Password</label>
        <div class="admin-password">
            <input class="admin-input" id="password" type="password" name="password" required autocomplete="current-password" placeholder="••••••••">
            <button type="button" class="admin-password-toggle" data-password-toggle aria-label="Show password" aria-pressed="false" title="Show password">
                <svg class="admin-password-icon admin-password-icon--show" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M2.5 12s3.5-6.5 9.5-6.5S21.5 12 21.5 12s-3.5 6.5-9.5 6.5S2.5 12 2.5 12Z"/><circle cx="12" cy="12" r="2.75"/></svg>
                <svg class="admin-password-icon admin-password-icon--hide" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M3 3l18 18M10.6 10.6a2.75 2.75 0 0 0 3.8 3.8M9.5 5.3A10.4 10.4 0 0 1 12 5.5c6 0 9.5 6.5 9.5 6.5a16.7 16.7 0 0 1-2.2 2.8M6.2 6.2C4 7.8 2.5 12 2.5 12S6 18.5 12 18.5a10 10 0 0 0 3.3-.5"/></svg>
            </button>
        </div>
    </div>
    <div class="admin-auth-row">
        <label class="admin-auth-remember">
            <input type="checkbox" name="remember" value="1">
            <span>Remember me</span>
        </label>
        <a class="admin-auth-link" href="{{ route('admin.password.request') }}">Forgot password?</a>
    </div>
    <button class="admin-btn admin-btn-primary admin-auth-submit" type="submit">
        Sign in
        <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M5 12h14M13 6l6 6-6 6"/></svg>
    </button>
</form>
@endsection
