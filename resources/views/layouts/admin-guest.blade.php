<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'Admin Login') · CEBINOVA</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Outfit:wght@400;500;600;700;800&family=Plus+Jakarta+Sans:wght@500;600;700;800&display=swap" rel="stylesheet">
    @vite(['resources/css/admin.css', 'resources/js/admin.js'])
</head>
<body class="admin-body admin-auth-body">
@php
    $markVer = (int) @filemtime(public_path('assets/brand/cebinova-c-icon.png'));
    $markUrl = asset('assets/brand/cebinova-c-icon.png').($markVer ? '?v='.$markVer : '');
@endphp
<div class="admin-auth" style="--admin-auth-mark: url('{{ $markUrl }}')">
    <div class="admin-auth-atmosphere" aria-hidden="true">
        <div class="admin-auth-dots"></div>
        <div class="admin-auth-mesh"></div>
        <div class="admin-auth-orb admin-auth-orb--gold"></div>
        <div class="admin-auth-orb admin-auth-orb--blue"></div>
        <div class="admin-auth-watermark"></div>
    </div>

    <div class="admin-auth-stage">
        <div class="admin-auth-card">
            <div class="admin-auth-brand">
                <x-cebinova-logo variant="full" class="admin-auth-logo" />
                <p class="admin-auth-badge">Admin Panel</p>
                <p class="admin-auth-kicker">
                    <span class="admin-auth-kicker-dot" aria-hidden="true"></span>
                    @yield('kicker', 'Secure admin access')
                </p>
                <h1 class="admin-auth-title">@yield('heading', 'Sign in to continue')</h1>
                <p class="admin-auth-subtitle">@yield('subtitle', 'Manage leads, content, packages and website settings.')</p>
            </div>

            @if (session('status'))
                <div class="admin-alert admin-alert-success">{{ session('status') }}</div>
            @endif
            @if ($errors->any())
                <div class="admin-alert admin-alert-error">
                    @foreach ($errors->all() as $error)<div>{{ $error }}</div>@endforeach
                </div>
            @endif

            @yield('content')
        </div>

        <p class="admin-auth-foot">
            <a href="{{ url('/') }}">← Back to website</a>
        </p>
    </div>
</div>
</body>
</html>
