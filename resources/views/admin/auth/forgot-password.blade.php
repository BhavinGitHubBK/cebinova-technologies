@extends('layouts.admin-guest')

@section('title', 'Forgot password')
@section('kicker', 'Account recovery')
@section('heading', 'Reset your password')
@section('subtitle', 'Enter your admin email and we will send a reset link.')
@section('content')
<form method="POST" action="{{ route('admin.password.email') }}" class="admin-auth-form">
    @csrf
    <div class="admin-field">
        <label class="admin-label" for="email">Email</label>
        <input class="admin-input" id="email" type="email" name="email" value="{{ old('email') }}" required autocomplete="username">
    </div>
    <button class="admin-btn admin-btn-primary admin-auth-submit" type="submit">Send reset link</button>
    <p class="admin-auth-alt">
        <a class="admin-auth-link" href="{{ route('admin.login') }}">Back to login</a>
    </p>
</form>
@endsection
