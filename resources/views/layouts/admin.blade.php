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
    <style>
        html, body.admin-body { height: 100%; overflow: hidden; }
        body.admin-body, .admin-topbar h1, .dash-hero-title, .dash-kpi-value, .admin-auth-title {
            font-family: Outfit, ui-sans-serif, system-ui, sans-serif;
        }
        .admin-sidebar .cebinova-brand-name,
        .admin-sidebar .cebinova-brand-tech {
            font-family: "Plus Jakarta Sans", Outfit, ui-sans-serif, sans-serif;
        }
        .admin-shell { height: 100dvh; max-height: 100dvh; overflow: hidden; }
        .admin-sidebar,
        .admin-nav {
            scrollbar-width: none !important;
            -ms-overflow-style: none;
        }
        .admin-sidebar::-webkit-scrollbar,
        .admin-nav::-webkit-scrollbar { display: none !important; width: 0 !important; height: 0 !important; }
        .admin-nav {
            display: flex;
            flex-direction: column;
            min-height: 0;
            overflow-x: hidden;
            overflow-y: auto;
        }
        .admin-nav-block-end { margin-top: 0; padding-top: 0; }
        .admin-main { min-height: 0; height: 100%; overflow: auto; }
        .admin-sidebar .cebinova-brand-line,
        .admin-sidebar .cebinova-brand-line-sheen,
        .admin-sidebar .cebinova-brand-line-node { display: block !important; }
        .admin-sidebar .cebinova-tagline { display: flex !important; font-size: 0.68rem; }
        .admin-sidebar .cebinova-logo-icon { height: 2.35rem !important; }
        .admin-sidebar .cebinova-brand-row { font-size: 1.22rem !important; }
        .admin-nav-label { margin: 0.85rem 0.75rem 0.2rem; font-size: 0.74rem; color: rgba(255,255,255,0.62); }
        button.admin-nav-toggle { margin: 0.85rem 0.15rem 0.2rem; padding: 0 0.6rem; font-size: 0.74rem; color: rgba(255,255,255,0.62); }
        .admin-nav-block,
        .admin-nav-group-items { display: grid; gap: 0.22rem; }
        .admin-nav a {
            padding: 0.48rem 0.8rem;
            font-size: 1.02rem;
            font-weight: 500;
            gap: 0.7rem;
            color: rgba(255,255,255,0.58);
            transition: background 0.55s ease, color 0.55s ease;
        }
        .admin-nav a svg { opacity: 0.55; }
        .admin-nav a:hover {
            background: rgba(255,255,255,0.08);
            color: rgba(255,255,255,0.9);
        }
        .admin-nav a:hover svg { color: #fbb50b; opacity: 1; }
        .admin-nav a.is-active,
        .admin-nav a.is-active:hover {
            background: rgba(255,255,255,0.07);
            color: rgba(255,255,255,0.92);
            font-size: 1.02rem;
            font-weight: 600;
            box-shadow: inset 2px 0 0 #fbb50b;
        }
        .admin-nav a.is-active svg,
        .admin-nav a.is-active:hover svg { color: #fbb50b; opacity: 1; }
        .admin-nav a svg,
        .admin-nav-toggle svg { width: 1.22rem; height: 1.22rem; transition: color 0.55s ease, transform 0.55s ease, opacity 0.55s ease, width 0.55s ease, height 0.55s ease; }
        .admin-swal .swal2-actions { display: flex; gap: 0.65rem; justify-content: center; }
        .admin-swal .swal2-cancel { display: inline-flex; }
        .activity-swal { width: min(42rem, calc(100vw - 2rem)) !important; max-width: calc(100vw - 2rem) !important; box-sizing: border-box; overflow: hidden; }
        .activity-swal .swal2-html-container { display: block; width: 100%; max-width: 100%; box-sizing: border-box; margin: 0; padding: 0 1.35rem 0.35rem; overflow: auto; text-align: left; }
        .activity-modal-row { display: grid; gap: 0.55rem; min-width: 0; padding: 0.85rem 0; border-top: 1px solid #eef2f6; }
        .activity-modal-row strong { color: #0b2c5f; font-size: 0.78rem; font-weight: 700; letter-spacing: 0.08em; text-transform: uppercase; }
        .activity-modal-values { display: grid; grid-template-columns: minmax(0, 1fr) minmax(0, 1fr); gap: 0.85rem; min-width: 0; }
        .activity-modal-values.is-single { grid-template-columns: minmax(0, 1fr); }
        .activity-modal-values > div { min-width: 0; }
        .activity-modal-values span { display: block; color: #8b97a8; font-size: 0.72rem; font-weight: 700; letter-spacing: 0.06em; text-transform: uppercase; }
        .activity-modal-values p { margin: 0.25rem 0 0; color: #12263f; font-weight: 600; line-height: 1.45; white-space: pre-wrap; overflow-wrap: anywhere; }
    </style>
    @stack('head')
</head>
<body class="admin-body is-busy">
    <div class="admin-progress is-active" data-admin-progress><span></span></div>
@php
    $newLeads = \Illuminate\Support\Facades\Schema::hasTable('leads')
        ? \App\Models\Lead::query()->where('status', 'New')->count()
        : 0;
@endphp
<div class="admin-shell" data-admin-shell inert>
    <div class="admin-backdrop" data-sidebar-backdrop></div>
    <aside class="admin-sidebar" aria-label="Admin navigation">
        <div class="admin-brand">
            <x-cebinova-logo
                variant="header"
                tone="dark"
                :href="route('admin.dashboard')"
                class="admin-brand-logo"
            />
        </div>
        <nav class="admin-nav">
            <div class="admin-nav-block">
                <p class="admin-nav-label">Overview</p>
                <a href="{{ route('admin.dashboard') }}" class="{{ request()->routeIs('admin.dashboard') ? 'is-active' : '' }}">
                    <x-mark name="layout" class="h-4 w-4" /> Dashboard
                </a>
                @can('admin.reports.view')
                    @if (Route::has('admin.reports.index'))
                        <a href="{{ route('admin.reports.index') }}" class="{{ request()->routeIs('admin.reports.*') ? 'is-active' : '' }}">
                            <x-mark name="chart" class="h-4 w-4" /> Reports
                        </a>
                    @endif
                @endcan
                @can('admin.analytics.view')
                    <a href="{{ route('admin.analytics.index') }}" class="{{ request()->routeIs('admin.analytics.*') ? 'is-active' : '' }}">
                        <x-mark name="trend" class="h-4 w-4" /> Analytics
                    </a>
                    <a href="{{ route('admin.customers.index') }}" class="{{ request()->routeIs('admin.customers.*') ? 'is-active' : '' }}">
                        <x-mark name="users" class="h-4 w-4" /> Customers
                    </a>
                @endcan

                @can('admin.leads.view')
                    <p class="admin-nav-label">Sales</p>
                    <a href="{{ route('admin.live-chats.index') }}" class="{{ request()->routeIs('admin.live-chats.*') ? 'is-active' : '' }}">
                        <x-mark name="inbox" class="h-4 w-4" /> Live chats
                    </a>
                    @can('admin.promotions.view')
                        <a href="{{ route('admin.promotions.campaigns.index') }}" class="{{ request()->routeIs('admin.promotions.*') ? 'is-active' : '' }}">
                            <x-mark name="spark" class="h-4 w-4" /> Promotions
                        </a>
                    @endcan
                    <a href="{{ route('admin.leads.index') }}" class="{{ request()->routeIs('admin.leads.*') ? 'is-active' : '' }}">
                        <x-mark name="inbox" class="h-4 w-4" /> Leads
                        @if($newLeads)
                            <span class="admin-nav-count" title="{{ $newLeads }} new {{ \Illuminate\Support\Str::plural('lead', $newLeads) }} waiting to be contacted" aria-label="{{ $newLeads }} new {{ \Illuminate\Support\Str::plural('lead', $newLeads) }}">
                                <strong>{{ $newLeads }}</strong>
                                <em>new</em>
                            </span>
                        @endif
                    </a>
                @endcan
            </div>

            <div class="admin-nav-group" data-nav-group="website">
                <button type="button" class="admin-nav-label admin-nav-toggle" data-nav-toggle aria-expanded="true">
                    Website
                    <x-mark name="chevron" class="h-3.5 w-3.5" />
                </button>
                <div class="admin-nav-group-items">
                    @can('admin.solutions.view')
                        <a href="{{ route('admin.solutions.index') }}" class="{{ request()->routeIs('admin.solutions.*') ? 'is-active' : '' }}">
                            <x-mark name="layers" class="h-4 w-4" /> Solutions
                        </a>
                    @endcan
                    @can('admin.industries.view')
                        <a href="{{ route('admin.industries.index') }}" class="{{ request()->routeIs('admin.industries.*') ? 'is-active' : '' }}">
                            <x-mark name="globe" class="h-4 w-4" /> Industries
                        </a>
                    @endcan
                    @can('admin.services.view')
                        <a href="{{ route('admin.services.index') }}" class="{{ request()->routeIs('admin.services.*') ? 'is-active' : '' }}">
                            <x-mark name="briefcase" class="h-4 w-4" /> Services
                        </a>
                    @endcan
                    @can('admin.packages.view')
                        <a href="{{ route('admin.packages.index') }}" class="{{ request()->routeIs('admin.packages.*') && request('group') !== 'marketing' && ! (request()->route('package') instanceof \App\Models\Package && request()->route('package')->isMarketing()) ? 'is-active' : '' }}">
                            <x-mark name="bag" class="h-4 w-4" /> Website Packages
                        </a>
                        <a href="{{ route('admin.packages.index', ['group' => 'marketing']) }}" class="{{ request('group') === 'marketing' || (request()->route('package') instanceof \App\Models\Package && request()->route('package')->isMarketing()) ? 'is-active' : '' }}">
                            <x-mark name="megaphone" class="h-4 w-4" /> Marketing Packages
                        </a>
                        <a href="{{ route('admin.pricing-options.index') }}" class="{{ request()->routeIs('admin.pricing-options.*') ? 'is-active' : '' }}">
                            <x-mark name="spark" class="h-4 w-4" /> Extra Charges
                        </a>
                    @endcan
                    @can('admin.content.view')
                        @if (Route::has('admin.demos.index'))
                            <a href="{{ route('admin.demos.index') }}" class="{{ request()->routeIs('admin.demos.*') ? 'is-active' : '' }}">
                                <x-mark name="device" class="h-4 w-4" /> Demos
                            </a>
                        @endif
                        <a href="{{ route('admin.page-sections.index') }}" class="{{ request()->routeIs('admin.page-sections.*') ? 'is-active' : '' }}">
                            <x-mark name="file" class="h-4 w-4" /> Page sections
                        </a>
                    @endcan
                </div>
            </div>

            <div class="admin-nav-block admin-nav-block-end">
                <p class="admin-nav-label">System</p>
                @can('admin.settings.view')
                    <a href="{{ route('admin.settings.edit') }}" class="{{ request()->routeIs('admin.settings.edit') ? 'is-active' : '' }}">
                        <x-mark name="gear" class="h-4 w-4" /> Settings
                    </a>
                @endcan
                @can('admin.cache.manage')
                    <a href="{{ route('admin.cache.index') }}" class="{{ request()->routeIs('admin.cache.*') ? 'is-active' : '' }}">
                        <x-mark name="spark" class="h-4 w-4" /> Cache
                    </a>
                @endcan
                @can('admin.users.view')
                    <a href="{{ route('admin.users.index') }}" class="{{ request()->routeIs('admin.users.*') ? 'is-active' : '' }}">
                        <x-mark name="users" class="h-4 w-4" /> Users
                    </a>
                @endcan
                @can('admin.activity.view')
                    <a href="{{ route('admin.activity-logs.index') }}" class="{{ request()->routeIs('admin.activity-logs.*') ? 'is-active' : '' }}">
                        <x-mark name="list" class="h-4 w-4" /> Activity
                    </a>
                @endcan
            </div>
        </nav>
    </aside>

    <div class="admin-main">
        <header class="admin-topbar">
            <div class="admin-topbar-left">
                <button type="button" class="admin-icon-btn" data-sidebar-toggle aria-label="Toggle menu">
                    <x-mark name="menu" class="h-5 w-5" />
                </button>
                <div>
                    <p class="admin-crumb" data-admin-crumb>@yield('breadcrumb', 'Admin')</p>
                    <h1 data-admin-heading>@yield('heading', 'Dashboard')</h1>
                </div>
            </div>
            <div class="admin-topbar-right">
                <a class="admin-text-link" href="{{ url('/') }}" target="_blank" rel="noopener">
                    <x-mark name="globe" class="h-4 w-4" /> View website
                </a>
                <div class="admin-notes" data-menu data-notifications="{{ route('admin.notifications.seen') }}">
                    <button type="button" class="admin-note-btn" data-menu-toggle aria-expanded="false" aria-haspopup="true" aria-label="Updates">
                        <svg class="admin-bell" viewBox="0 0 24 24" fill="none" aria-hidden="true">
                            <circle cx="12" cy="3.6" r="1.15" fill="currentColor"/>
                            <path d="M12 4.7v1.15" stroke="currentColor" stroke-width="1.8" stroke-linecap="round"/>
                            <path d="M6.8 16.6c.7-1.15 1.05-2.4 1.05-3.7V10.6a4.15 4.15 0 0 1 8.3 0v2.3c0 1.3.35 2.55 1.05 3.7" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"/>
                            <path d="M4.8 16.6h14.4" stroke="currentColor" stroke-width="1.8" stroke-linecap="round"/>
                            <path d="M9.7 16.6a2.3 2.3 0 0 0 4.6 0" stroke="currentColor" stroke-width="1.8" stroke-linecap="round"/>
                        </svg>
                        @if (($adminNotifications['unread'] ?? 0) > 0)
                            <span class="admin-note-badge" data-notification-count>{{ $adminNotifications['unread'] > 9 ? '9+' : $adminNotifications['unread'] }}</span>
                        @endif
                    </button>
                    <div class="admin-menu admin-note-menu" data-menu-panel hidden>
                        <p class="admin-note-head">New leads</p>
                        @forelse ($adminNotifications['items'] ?? [] as $item)
                            <a class="admin-note-item" href="{{ $item['url'] }}">
                                <strong>{{ $item['title'] }}</strong>
                                <span>{{ $item['detail'] }} · {{ $item['time']?->diffForHumans() }}</span>
                            </a>
                        @empty
                            <p class="admin-note-empty">No new leads.</p>
                        @endforelse
                        @can('admin.leads.view')
                            <a class="admin-note-all" href="{{ route('admin.leads.index', ['status' => 'New']) }}">View new leads</a>
                        @endcan
                    </div>
                </div>
                <div class="admin-profile" data-menu>
                    <button type="button" class="admin-profile-btn" data-menu-toggle aria-expanded="false" aria-haspopup="true">
                        <span class="dash-avatar" aria-hidden="true">{{ strtoupper(substr(auth()->user()->name, 0, 1)) }}</span>
                        <span class="admin-profile-name">{{ auth()->user()->name }}</span>
                        <x-mark name="chevron" class="h-4 w-4" />
                    </button>
                    <div class="admin-menu" data-menu-panel hidden>
                        <a href="{{ route('admin.profile.edit') }}">Profile</a>
                        @can('admin.settings.view')
                            <a href="{{ route('admin.settings.edit') }}">Settings</a>
                        @endcan
                        <form method="POST" action="{{ route('admin.logout') }}">
                            @csrf
                            <button type="submit" data-confirm="You will leave the admin on this device.">Logout</button>
                        </form>
                    </div>
                </div>
            </div>
        </header>

        <main class="admin-content" data-admin-page @if ($list = admin_index_url()) data-admin-list="{{ $list }}" @endif>
            @if (session('success') || session('status'))
                <div data-admin-flash="success" hidden>{{ session('success') ?: session('status') }}</div>
            @endif
            @if ($errors->any())
                <div data-admin-flash="error" hidden>{{ $errors->first() }}</div>
                <div class="admin-form-errors" role="alert">
                    @foreach ($errors->all() as $error)
                        <p>{{ $error }}</p>
                    @endforeach
                </div>
            @endif

            @yield('content')
        </main>

        <footer class="admin-footer">
            <p>&copy; {{ date('Y') }} {{ config('cebinova.name') }}. All Rights Reserved.</p>
            <nav aria-label="Footer">
                <a href="{{ route('privacy') }}">Privacy Policy</a>
                <a href="{{ route('terms') }}">Terms</a>
                <a href="{{ url('/') }}" target="_blank" rel="noopener">View website</a>
            </nav>
        </footer>
    </div>
</div>
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11.22.2/dist/sweetalert2.all.min.js"></script>
<div data-admin-page-scripts>
@stack('scripts')
</div>
</body>
</html>
