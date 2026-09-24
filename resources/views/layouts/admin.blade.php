<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'Admin') · CEBINOVA</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Outfit:wght@400;500;600;700;800&family=Plus+Jakarta+Sans:wght@500;600;700;800&display=swap" rel="stylesheet">
    @vite(['resources/css/admin.css', 'resources/js/admin.js'])
    @stack('head')
</head>
<body class="admin-body">
@php
    $newLeads = \App\Models\Lead::query()->where('status', 'New')->count();
@endphp
<div class="admin-shell" data-admin-shell>
    <div class="admin-backdrop" data-sidebar-backdrop></div>
    <aside class="admin-sidebar" aria-label="Admin navigation">
        <div class="admin-brand">
            <x-cebinova-logo
                variant="header"
                tone="dark"
                :href="route('admin.dashboard')"
                class="admin-brand-logo"
            />
            <span class="admin-brand-badge">Admin</span>
        </div>
        <nav class="admin-nav">
            <a href="{{ route('admin.dashboard') }}" class="{{ request()->routeIs('admin.dashboard') ? 'is-active' : '' }}">Dashboard</a>
            @can('admin.leads.view')
                <a href="{{ route('admin.leads.index') }}" class="{{ request()->routeIs('admin.leads.*') ? 'is-active' : '' }}">
                    Leads @if($newLeads)<span class="admin-badge admin-badge-new">{{ $newLeads }}</span>@endif
                </a>
            @endcan
            @can('admin.services.view')
                <a href="{{ route('admin.services.index') }}" class="{{ request()->routeIs('admin.services.*') ? 'is-active' : '' }}">Services</a>
            @endcan
            @can('admin.packages.view')
                <a href="{{ route('admin.packages.index') }}" class="{{ request()->routeIs('admin.packages.*') ? 'is-active' : '' }}">Packages</a>
            @endcan
            @can('admin.content.view')
                <a href="{{ route('admin.testimonials.index') }}" class="{{ request()->routeIs('admin.testimonials.*') ? 'is-active' : '' }}">Testimonials</a>
                <a href="{{ route('admin.team-members.index') }}" class="{{ request()->routeIs('admin.team-members.*') ? 'is-active' : '' }}">Team</a>
                <a href="{{ route('admin.faqs.index') }}" class="{{ request()->routeIs('admin.faqs.*') ? 'is-active' : '' }}">FAQs</a>
                <a href="{{ route('admin.page-sections.index') }}" class="{{ request()->routeIs('admin.page-sections.*') ? 'is-active' : '' }}">Page sections</a>
            @endcan
            @can('admin.media.view')
                <a href="{{ route('admin.media.index') }}" class="{{ request()->routeIs('admin.media.*') ? 'is-active' : '' }}">Media</a>
            @endcan
            @can('admin.settings.view')
                <a href="{{ route('admin.settings.edit') }}" class="{{ request()->routeIs('admin.settings.*') ? 'is-active' : '' }}">Settings</a>
            @endcan
            @can('admin.users.view')
                <a href="{{ route('admin.users.index') }}" class="{{ request()->routeIs('admin.users.*') ? 'is-active' : '' }}">Users</a>
            @endcan
            @can('admin.activity.view')
                <a href="{{ route('admin.activity-logs.index') }}" class="{{ request()->routeIs('admin.activity-logs.*') ? 'is-active' : '' }}">Activity</a>
            @endcan
        </nav>
    </aside>

    <div class="admin-main">
        <header class="admin-topbar">
            <div style="display:flex;align-items:center;gap:.75rem;">
                <button type="button" class="admin-btn admin-btn-ghost" data-sidebar-toggle aria-label="Toggle menu">Menu</button>
                <div>
                    <div style="font-size:.75rem;color:#64748b;">@yield('breadcrumb', 'Admin')</div>
                    <h1 style="margin:0;font-size:1.15rem;">@yield('heading', 'Dashboard')</h1>
                </div>
            </div>
            <div style="display:flex;align-items:center;gap:.75rem;">
                <a class="admin-btn admin-btn-ghost" href="{{ url('/') }}" target="_blank" rel="noopener">View site</a>
                <a class="admin-btn admin-btn-ghost" href="{{ route('admin.profile.edit') }}">{{ auth()->user()->name }}</a>
                <form method="POST" action="{{ route('admin.logout') }}">
                    @csrf
                    <button class="admin-btn admin-btn-primary" type="submit">Logout</button>
                </form>
            </div>
        </header>

        <main class="admin-content">
            @if (session('success'))
                <div class="admin-alert admin-alert-success" role="status">{{ session('success') }}</div>
            @endif
            @if (session('status'))
                <div class="admin-alert admin-alert-success" role="status">{{ session('status') }}</div>
            @endif
            @if ($errors->any())
                <div class="admin-alert admin-alert-error" role="alert">
                    <ul style="margin:0;padding-left:1.1rem;">
                        @foreach ($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

            @yield('content')
        </main>
    </div>
</div>
@stack('scripts')
</body>
</html>
