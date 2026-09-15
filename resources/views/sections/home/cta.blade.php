<section class="relative overflow-hidden bg-navy py-20 sm:py-24 lg:py-[7rem]">
    <div class="pointer-events-none absolute inset-0 bg-dots-light"></div>
    <div class="hero-mesh pointer-events-none absolute inset-0 opacity-40"></div>
    <svg class="pointer-events-none absolute inset-0 h-full w-full opacity-35" viewBox="0 0 1200 360" aria-hidden="true">
        <g fill="none" stroke="#C9A227" stroke-opacity="0.28" stroke-width="1.1">
            <path d="M40 220C220 120 380 280 560 180C740 80 920 240 1160 140"/>
            <circle cx="560" cy="180" r="3.5" fill="#C9A227"/>
            <circle cx="920" cy="240" r="3" fill="#C9A227"/>
        </g>
    </svg>
    <div class="container-wide relative grid items-center gap-10 lg:grid-cols-12">
        <div class="min-w-0 lg:col-span-7" data-reveal>
            <p class="kicker kicker-light">Next step</p>
            <h2 class="section-title text-3xl font-extrabold text-white sm:text-4xl lg:text-[2.75rem] lg:leading-[1.2]">Ready to Take Your Business Forward?</h2>
            <p class="mt-5 max-w-2xl text-[17px] leading-relaxed text-white/72 sm:text-lg">
                Start with the solution you need today. SARVIX can grow with you tomorrow.
            </p>
        </div>
        <div class="flex min-w-0 flex-col gap-3 sm:flex-row lg:col-span-5 lg:justify-end">
            <x-button href="{{ consultation_url() }}" size="lg" class="group">
                Get Free Consultation
                <svg class="arrow-shift h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M5 12h14M13 6l6 6-6 6"/></svg>
            </x-button>
            <x-button href="{{ whatsapp_url() }}" variant="light" size="lg">Talk to SARVIX</x-button>
        </div>
    </div>
</section>
