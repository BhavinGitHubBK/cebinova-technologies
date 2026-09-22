ï»¿@php    $solutionGroups = config('cebinova.nav_solutions');
    $businessIcons = [
        'kirana' => 'bag',
        'retail' => 'cart',
        'professional-services' => 'briefcase',
        'ecommerce' => 'store',
        'business-management' => 'layers',
        'ai-automation' => 'spark',
    ];
@endphp

<header id="site-nav" class="site-nav">
    <div class="container-wide">
        <div class="nav-inner">
            <x-cebinova-logo variant="header" class="site-nav-brand" />

            <nav class="site-nav-primary" aria-label="Primary">
                @foreach (config('cebinova.nav') as $item)
                    @php
                        $active = nav_item_active($item);
                        $badge = trim((string) ($item['badge'] ?? ''));
                        $linkClass = 'nav-link'
                            .($active ? ' nav-link-active' : '')
                            .($badge !== '' ? ' nav-link-has-badge' : '');
                    @endphp
                    @if (($item['type'] ?? null) === 'mega')
                        <div class="nav-dropdown">
                            <a href="{{ route($item['route']) }}" class="{{ $linkClass }}">
                                {{ $item['label'] }}
                                <svg class="nav-link-chevron" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M6 9l6 6 6-6"/></svg>
                            </a>
                            <div class="nav-mega" role="menu">
                                <div class="nav-mega-grid">
                                    <div class="nav-mega-col">
                                        <p class="nav-mega-heading">
                                            <span class="nav-mega-dot" aria-hidden="true"></span>
                                            Business Solutions
                                        </p>
                                        @foreach ($solutionGroups['business'] as $child)
                                            <a href="{{ nav_solution_href($child) }}" class="nav-mega-item" role="menuitem">
                                                <span class="nav-mega-icon" aria-hidden="true">
                                                    <x-mark :name="$businessIcons[$child['slug']] ?? 'layers'" class="h-3.5 w-3.5" />                                                </span>
                                                <span class="nav-mega-item-label">{{ $child['label'] }}</span>
                                            </a>
                                        @endforeach
                                    </div>
                                    <div class="nav-mega-col">
                                        <p class="nav-mega-heading">
                                            <span class="nav-mega-dot" aria-hidden="true"></span>
                                            Industries
                                        </p>
                                        @foreach ($solutionGroups['industries'] as $child)
                                            <a href="{{ route('industries') }}#{{ $child['slug'] }}" class="nav-mega-item nav-mega-item--plain" role="menuitem">
                                                <span class="nav-mega-item-label">{{ $child['label'] }}</span>
                                            </a>
                                        @endforeach
                                    </div>
                                </div>
                                <div class="nav-mega-foot">
                                    <a href="{{ route('solutions') }}" class="nav-mega-foot-link">
                                        View All Solutions
                                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M5 12h14M13 6l6 6-6 6"/></svg>
                                    </a>
                                    <a href="{{ route('industries') }}" class="nav-mega-foot-link nav-mega-foot-link--muted">
                                        View Industries
                                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M5 12h14M13 6l6 6-6 6"/></svg>
                                    </a>
                                </div>
                            </div>
                        </div>
                    @else
                        <a href="{{ route($item['route']) }}" class="{{ $linkClass }}">
                            <span class="nav-link-label">{{ $item['label'] }}</span>
                            @if ($badge !== '')
                                <span class="nav-link-badge">{{ $badge }}</span>
                            @endif
                        </a>
                    @endif
                @endforeach
            </nav>

            <div class="site-nav-cta">
                @if (cebinova_phone())
                    <a href="tel:{{ preg_replace('/\s+/', '', cebinova_phone()) }}" class="site-nav-phone">
                        <span class="site-nav-phone-icon" aria-hidden="true">
                            <x-mark name="phone" class="h-3.5 w-3.5" />                        </span>
                        <span class="site-nav-phone-copy">
                            <span class="site-nav-phone-label">Call us</span>
                            <span class="site-nav-phone-number">{{ cebinova_phone() }}</span>
                        </span>
                    </a>
                @endif
                <x-button href="{{ consultation_url() }}" size="md" class="site-nav-consult">
                    Get Free Consultation
                    <svg class="arrow-shift h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M5 12h14M13 6l6 6-6 6"/></svg>
                </x-button>
            </div>

            <button type="button" id="menu-open" class="menu-toggle" aria-label="Open menu" aria-controls="mobile-menu" aria-expanded="false">
                <x-mark name="menu" class="h-5 w-5" />                <span class="menu-toggle-label">Menu</span>
            </button>
        </div>
    </div>
</header>

<div id="mobile-overlay" class="mobile-overlay" hidden></div>

<aside id="mobile-menu" class="mobile-menu" aria-hidden="true">
    <div class="mobile-menu-top">
        <x-cebinova-logo variant="compact" class="min-w-0" />
        <button type="button" id="menu-close" class="mobile-menu-close" aria-label="Close menu">
            <x-mark name="close" class="h-5 w-5" />        </button>
    </div>
    <nav class="mobile-menu-nav" aria-label="Mobile">
        @foreach (config('cebinova.nav') as $item)
            @php
                $active = nav_item_active($item);
                $badge = trim((string) ($item['badge'] ?? ''));
            @endphp
            <a href="{{ route($item['route']) }}" class="mobile-menu-link{{ $active ? ' is-active' : '' }}{{ $badge !== '' ? ' has-badge' : '' }}">
                <span class="mobile-menu-link-copy">
                    <span>{{ $item['label'] }}</span>
                    @if ($badge !== '')
                        <span class="nav-link-badge">{{ $badge }}</span>
                    @endif
                </span>
            </a>
            @if (($item['type'] ?? null) === 'mega')
                <div class="mobile-menu-group">
                    <p class="mobile-menu-heading">
                        <span class="nav-mega-dot" aria-hidden="true"></span>
                        Business Solutions
                    </p>
                    @foreach ($solutionGroups['business'] as $child)
                        <a href="{{ nav_solution_href($child) }}" class="mobile-menu-sublink">{{ $child['label'] }}</a>
                    @endforeach
                    <p class="mobile-menu-heading">
                        <span class="nav-mega-dot" aria-hidden="true"></span>
                        Industries
                    </p>
                    <a href="{{ route('industries') }}" class="mobile-menu-sublink">View All Industries</a>
                    <a href="{{ route('solutions') }}" class="mobile-menu-sublink">View All Solutions</a>
                </div>
            @endif
        @endforeach
    </nav>
    <div class="mobile-menu-actions">
        <x-button href="{{ consultation_url() }}" class="w-full">Get Free Consultation</x-button>
        <x-button href="{{ whatsapp_url() }}" variant="whatsapp" class="w-full">
            <x-mark name="whatsapp" class="h-4 w-4 text-[#25D366]" />            Talk to CEBINOVA
        </x-button>
    </div>
</aside>
