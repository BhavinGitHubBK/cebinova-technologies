@php
    $solutionGroups = config('sarvix.nav_solutions');
@endphp

<header id="site-nav" class="fixed inset-x-0 top-0 z-50 border-b border-transparent bg-white/95">
    <div class="container-wide">
        <div class="nav-inner relative flex min-h-[4.5rem] items-center justify-between gap-3 py-2.5 transition-all duration-300 lg:min-h-[4.75rem] lg:gap-4 lg:py-2.5">
            <x-sarvix-logo variant="header" class="min-w-0 shrink-0" />

            <nav class="hidden min-w-0 items-center gap-2.5 xl:flex 2xl:gap-4" aria-label="Primary">
                @foreach (config('sarvix.nav') as $item)
                    @php $active = nav_item_active($item); @endphp
                    @if (($item['type'] ?? null) === 'mega')
                        <div class="nav-dropdown">
                            <a href="{{ route($item['route']) }}" class="nav-link {{ $active ? 'nav-link-active' : '' }}">
                                {{ $item['label'] }}
                            </a>
                            <div class="nav-mega" role="menu">
                                <div class="nav-mega-grid">
                                    <div>
                                        <p class="nav-mega-heading">Business Solutions</p>
                                        @foreach ($solutionGroups['business'] as $child)
                                            <a href="{{ nav_solution_href($child) }}" class="nav-dropdown-item" role="menuitem">{{ $child['label'] }}</a>
                                        @endforeach
                                    </div>
                                    <div>
                                        <p class="nav-mega-heading">Industries</p>
                                        @foreach ($solutionGroups['industries'] as $child)
                                            <a href="{{ route('industries') }}#{{ $child['slug'] }}" class="nav-dropdown-item" role="menuitem">{{ $child['label'] }}</a>
                                        @endforeach
                                    </div>
                                </div>
                                <a href="{{ route('solutions') }}" class="nav-mega-foot">View All Solutions</a>
                            </div>
                        </div>
                    @else
                        <a href="{{ route($item['route']) }}" class="nav-link {{ $active ? 'nav-link-active' : '' }}">
                            {{ $item['label'] }}
                        </a>
                    @endif
                @endforeach
            </nav>

            <div class="hidden shrink-0 items-center xl:flex">
                <x-button href="{{ consultation_url() }}" size="md" class="shadow-[0_8px_20px_rgba(201,162,39,0.28)]">Get Free Consultation</x-button>
            </div>

            <button type="button" id="menu-open" class="menu-toggle ml-auto inline-flex h-10 w-10 shrink-0 items-center justify-center gap-2 rounded-lg text-sm font-semibold sm:w-auto sm:px-3" aria-label="Open menu" aria-controls="mobile-menu" aria-expanded="false">
                <x-icon name="menu" class="h-5 w-5" />
                <span class="hidden sm:inline">Menu</span>
            </button>
        </div>
    </div>
</header>

<div id="mobile-overlay" class="fixed inset-0 z-50 hidden bg-navy/45 xl:hidden" hidden></div>

<aside id="mobile-menu" class="fixed inset-y-0 right-0 z-50 flex w-[min(22rem,88vw)] translate-x-full flex-col bg-white shadow-[-20px_0_50px_rgba(11,31,58,0.16)] transition-transform duration-300 xl:hidden" aria-hidden="true">
    <div class="flex items-center justify-between border-b border-line px-5 py-4">
        <x-sarvix-logo variant="compact" class="min-w-0" />
        <button type="button" id="menu-close" class="inline-flex h-10 w-10 items-center justify-center rounded-full border border-line text-navy" aria-label="Close menu">
            <x-icon name="close" class="h-5 w-5" />
        </button>
    </div>
    <nav class="flex flex-col gap-0.5 overflow-y-auto px-3 py-4" aria-label="Mobile">
        @foreach (config('sarvix.nav') as $item)
            @php $active = nav_item_active($item); @endphp
            <a href="{{ route($item['route']) }}" class="rounded-lg px-4 py-3 text-[15.5px] font-semibold {{ $active ? 'bg-mist text-navy ring-1 ring-gold/35' : 'text-navy/80 hover:bg-mist hover:text-navy' }}">{{ $item['label'] }}</a>
            @if (($item['type'] ?? null) === 'mega')
                <a href="{{ route('solutions') }}" class="rounded-lg px-8 py-2 text-[14.5px] font-medium text-navy/70 hover:bg-mist hover:text-navy">Business Solutions</a>
                <a href="{{ route('industries') }}" class="rounded-lg px-8 py-2 text-[14.5px] font-medium text-navy/70 hover:bg-mist hover:text-navy">Industries</a>
                <a href="{{ route('solutions') }}" class="rounded-lg px-8 py-2 text-[14.5px] font-medium text-navy/70 hover:bg-mist hover:text-navy">View All Solutions</a>
            @endif
        @endforeach
    </nav>
    <div class="mt-auto space-y-3 border-t border-line px-5 py-5">
        <x-button href="{{ consultation_url() }}" class="w-full">Get Free Consultation</x-button>
        <x-button href="{{ whatsapp_url() }}" variant="whatsapp" class="w-full">
            <x-icon name="whatsapp" class="h-4 w-4 text-[#25D366]" />
            Talk to SARVIX
        </x-button>
    </div>
</aside>
