<section class="section-pad-lg bg-mist">
    <div class="container-wide">
        <div data-reveal>
            <x-section-heading eyebrow="Solutions" title="Solutions Built for Real Businesses.">
                SARVIX does not only talk about services. These working demos show what we can build for different businesses.
            </x-section-heading>
        </div>
        <div class="mt-12 grid gap-6 md:grid-cols-2" data-stagger>
            @foreach (collect(config('sarvix.business_solutions'))->whereIn('slug', ['kirana', 'professional-services', 'retail', 'ecommerce']) as $item)
                <article class="demo-frame lift-card card-surface flex h-full flex-col overflow-hidden">
                    <div class="demo-chrome">
                        <span class="demo-dot bg-[#ff5f57]"></span>
                        <span class="demo-dot bg-[#febc2e]"></span>
                        <span class="demo-dot bg-[#28c840]"></span>
                        <span class="demo-url">{{ $item['title'] }}</span>
                    </div>
                    <div class="relative overflow-hidden bg-navy">
                        @if ($item['preview'])
                            <img src="{{ asset('assets/'.$item['preview']) }}" alt="" class="demo-frame-shot h-56 w-full object-cover object-top lg:h-64" loading="lazy">
                        @else
                            <div class="flex h-56 items-center justify-center text-sm font-semibold text-white/50 lg:h-64">Preview coming soon</div>
                        @endif
                        <span class="absolute left-3 top-3 rounded-full bg-gold px-3 py-1 text-[10px] font-bold uppercase tracking-wider text-navy-deep">{{ $item['label'] }}</span>
                    </div>
                    <div class="flex flex-1 flex-col p-6 sm:p-7">
                        <p class="text-[12px] font-bold uppercase tracking-[0.14em] text-navy/40">{{ $item['industry'] }}</p>
                        <h3 class="mt-1 text-xl font-extrabold text-navy">{{ $item['title'] }}</h3>
                        <p class="mt-2 text-[15px] leading-relaxed text-muted">{{ $item['summary'] }}</p>
                        <ul class="mt-4 flex flex-wrap gap-2">
                            @foreach (array_slice($item['capabilities'], 0, 3) as $capability)
                                <li class="rounded-full bg-mist px-2.5 py-1 text-[12px] font-semibold text-navy/70">{{ $capability }}</li>
                            @endforeach
                        </ul>
                        @if ($item['demo'])
                            <a href="{{ demo_url($item['demo']) }}" class="group mt-6 inline-flex items-center gap-2 text-[15px] font-semibold text-navy transition hover:text-gold-dark">
                                View Demo
                                <svg class="arrow-shift h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M5 12h14M13 6l6 6-6 6"/></svg>
                            </a>
                        @endif
                    </div>
                </article>
            @endforeach
        </div>
        <div class="mt-8 flex flex-col gap-3 sm:flex-row">
            <x-button href="{{ route('solutions') }}">Explore All Solutions</x-button>
            <x-button href="{{ route('demos') }}" variant="outline">View Demos</x-button>
        </div>
    </div>
</section>
