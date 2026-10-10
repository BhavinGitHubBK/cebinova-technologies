@php    $solutionGroups = config('cebinova.nav_solutions');
    $businessIcons = [
        'kirana' => 'bag',
        'retail' => 'cart',
        'professional-services' => 'briefcase',
        'ecommerce' => 'store',
        'business-management' => 'layers',
        'ai-automation' => 'spark',
    ];
    $navServices ??= [];
    $navOffers ??= [];
    $navItems = collect(config('cebinova.nav'))
        ->reject(fn ($item) => ($item['type'] ?? null) === 'offers' && $navOffers === [])
        ->map(function ($item) use ($navOffers, $navServices) {
            $item['type'] ??= null;
            $item['href'] = route($item['route']);
            $item['badge'] = trim((string) ($item['badge'] ?? ''));
            $item['active'] = nav_item_active($item);

            if ($item['type'] === 'offers' && count($navOffers) === 1) {
                $offer = $navOffers[0];
                $item['label'] = $offer['name'];
                $item['href'] = route('offers.show', $offer['slug']);
                $item['badge'] = $offer['discount'] !== '' ? $offer['discount'].' off' : 'Live';
                $item['type'] = null;
            } elseif ($item['type'] === 'offers') {
                $item['badge'] = count($navOffers).' live';
            }

            if ($item['type'] === 'services' && $navServices === []) {
                $item['type'] = null;
            }

            return $item;
        });
@endphp

<header id="site-nav" class="site-nav">
    <div class="container-wide">
        <div class="nav-inner">
            <x-cebinova-logo variant="header" class="site-nav-brand" />

            <nav class="site-nav-primary" aria-label="Primary">
                @foreach ($navItems as $item)
                    @php
                        $active = $item['active'];
                        $badge = $item['badge'];
                        $linkClass = 'nav-link'
                            .($active ? ' nav-link-active' : '')
                            .($badge !== '' ? ' nav-link-has-badge' : '');
                    @endphp
                    @if ($item['type'] === 'services')
                        <div class="nav-dropdown">
                            <a href="{{ $item['href'] }}" class="{{ $linkClass }}">
                                {{ $item['label'] }}
                                <svg class="nav-link-chevron" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M6 9l6 6 6-6"/></svg>
                            </a>
                            <div class="nav-mega" role="menu">
                                <div class="nav-mega-col">
                                    <p class="nav-mega-heading">
                                        <span class="nav-mega-dot" aria-hidden="true"></span>
                                        Our Services
                                    </p>
                                    <div class="nav-mega-list">
                                        @foreach ($navServices as $service)
                                            <a href="{{ route('services.show', $service['slug']) }}" class="nav-mega-item nav-mega-item--rich" role="menuitem">
                                                <span class="nav-mega-icon" aria-hidden="true">
                                                    <x-mark :name="$service['icon']" class="h-3.5 w-3.5" />
                                                </span>
                                                <span class="nav-mega-item-copy">
                                                    <span class="nav-mega-item-label">{{ $service['name'] }}</span>
                                                    @if ($service['summary'])
                                                        <span class="nav-mega-item-note">{{ $service['summary'] }}</span>
                                                    @endif
                                                </span>
                                            </a>
                                        @endforeach
                                    </div>
                                </div>
                                <div class="nav-mega-foot">
                                    <a href="{{ route('services.index') }}" class="nav-mega-foot-link">
                                        View All Services
                                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M5 12h14M13 6l6 6-6 6"/></svg>
                                    </a>
                                    <a href="{{ route('pricing') }}" class="nav-mega-foot-link nav-mega-foot-link--muted">
                                        See Pricing
                                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M5 12h14M13 6l6 6-6 6"/></svg>
                                    </a>
                                </div>
                            </div>
                        </div>
                    @elseif ($item['type'] === 'offers')
                        <div class="nav-dropdown">
                            <a href="{{ $item['href'] }}" class="{{ $linkClass }} nav-link-offers">
                                <span class="nav-link-label">{{ $item['label'] }}</span>
                                <span class="nav-link-badge">{{ $badge }}</span>
                                <svg class="nav-link-chevron" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M6 9l6 6 6-6"/></svg>
                            </a>
                            <div class="nav-mega nav-mega--compact" role="menu">
                                <div class="nav-mega-col">
                                    <p class="nav-mega-heading">
                                        <span class="nav-mega-dot" aria-hidden="true"></span>
                                        Running Offers
                                    </p>
                                    @foreach ($navOffers as $offer)
                                        <a href="{{ route('offers.show', $offer['slug']) }}" class="nav-mega-item nav-mega-item--rich" role="menuitem">
                                            <span class="nav-offer-discount" aria-hidden="true">{{ $offer['discount'] }}<small>off</small></span>
                                            <span class="nav-mega-item-copy">
                                                <span class="nav-mega-item-label">{{ $offer['name'] }}</span>
                                                <span class="nav-mega-item-note">{{ collect([$offer['occasion'], $offer['ends'] ? 'Ends '.$offer['ends'] : null])->filter()->implode(' · ') }}</span>
                                            </span>
                                        </a>
                                    @endforeach
                                </div>
                                <div class="nav-mega-foot">
                                    <a href="{{ route('offers.index') }}" class="nav-mega-foot-link">
                                        View All Offers
                                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M5 12h14M13 6l6 6-6 6"/></svg>
                                    </a>
                                </div>
                            </div>
                        </div>
                    @elseif ($item['type'] === 'mega')
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
                        <a href="{{ $item['href'] }}" class="{{ $linkClass }}">
                            <span class="nav-link-label">{{ $item['label'] }}</span>
                            @if ($badge !== '')
                                <span class="nav-link-badge">{{ $badge }}</span>
                            @endif
                        </a>
                    @endif
                @endforeach
            </nav>

            <div class="site-nav-cta">
                @include('components.site-account')
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
        @foreach ($navItems as $item)
            @php
                $active = $item['active'];
                $badge = $item['badge'];
            @endphp
            <a href="{{ $item['href'] }}" class="mobile-menu-link{{ $active ? ' is-active' : '' }}{{ $badge !== '' ? ' has-badge' : '' }}">
                <span class="mobile-menu-link-copy">
                    <span>{{ $item['label'] }}</span>
                    @if ($badge !== '')
                        <span class="nav-link-badge">{{ $badge }}</span>
                    @endif
                </span>
            </a>
            @if ($item['type'] === 'services')
                <div class="mobile-menu-group">
                    @foreach ($navServices as $service)
                        <a href="{{ route('services.show', $service['slug']) }}" class="mobile-menu-sublink">{{ $service['name'] }}</a>
                    @endforeach
                    <a href="{{ route('services.index') }}" class="mobile-menu-sublink">View All Services</a>
                </div>
            @elseif ($item['type'] === 'offers')
                <div class="mobile-menu-group">
                    @foreach ($navOffers as $offer)
                        <a href="{{ route('offers.show', $offer['slug']) }}" class="mobile-menu-sublink">
                            {{ $offer['name'] }}
                            @if ($offer['discount'] !== '')
                                <span class="mobile-menu-offer">{{ $offer['discount'] }} off</span>
                            @endif
                        </a>
                    @endforeach
                    <a href="{{ route('offers.index') }}" class="mobile-menu-sublink">View All Offers</a>
                </div>
            @elseif ($item['type'] === 'mega')
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
        @include('components.site-account')
        <x-button href="{{ whatsapp_url() }}" variant="whatsapp" class="w-full">
            <x-mark name="whatsapp" class="h-4 w-4 text-[#25D366]" />            Talk to CEBINOVA
        </x-button>
    </div>
</aside>
