@php
    $aiIcons = ['chat', 'trend', 'whatsapp', 'nodes', 'clock', 'spark', 'layers', 'cpu'];
    $aiNotes = [
        'Answer common questions after hours.',
        'Route new enquiries to the right person.',
        'Send updates where customers already chat.',
        'Remove repeated internal steps.',
        'Keep teams and customers informed.',
        'Draft useful copy for offers and follow-ups.',
        'See what needs attention next.',
        'Connect the tools you already use.',
    ];
@endphp

<section class="relative overflow-hidden bg-navy section-pad-lg">
    <div class="pointer-events-none absolute inset-0 bg-dots-light"></div>
    <svg class="pointer-events-none absolute inset-0 h-full w-full opacity-35" viewBox="0 0 1200 620" aria-hidden="true">
        <g fill="none" stroke="#C9A227" stroke-opacity="0.32" stroke-width="1.1">
            <path class="js-ai-line" d="M60 130C220 70 340 210 510 170C680 130 760 70 980 150"/>
            <path class="js-ai-line" d="M90 430C250 360 410 510 580 450C780 370 900 500 1140 410"/>
            <path class="js-ai-line" d="M40 300C200 260 360 320 540 280C720 240 880 310 1160 250"/>
        </g>
    </svg>
    <div class="container-wide relative z-10 grid items-start gap-12 lg:grid-cols-12">
        <div class="min-w-0 lg:col-span-5" data-reveal>
            <x-section-heading light :wrap="true" eyebrow="AI & Automation" title="Make Technology Work for You.">
                SARVIX helps businesses reduce manual work through intelligent automation and AI-powered solutions.
            </x-section-heading>
            <p class="mt-5 text-[15.5px] leading-relaxed text-white/60">Enquiry in. Follow-up out. Reporting in between - without adding another disconnected tool.</p>
            <p class="mt-4 text-[13px] font-medium text-white/40">Hover or focus a capability to see how it fits the workflow.</p>
            <div class="mt-8">
                <x-button href="{{ route('services.show', 'ai-automation') }}" variant="primary">Explore AI Solutions</x-button>
            </div>
        </div>
        <ul class="relative z-10 grid gap-3 sm:grid-cols-2 lg:col-span-7" data-stagger>
            @foreach (config('sarvix.ai_features') as $feature)
                <li class="ai-block group rounded-xl border border-white/12 bg-white/[0.06] px-4 py-4 transition duration-300 hover:border-gold/40 focus-within:border-gold/40" tabindex="0" data-ai-block>
                    <div class="flex items-center gap-3">
                        <span class="flex h-9 w-9 shrink-0 items-center justify-center rounded-lg bg-navy-mid text-gold">
                            <x-icon :name="$aiIcons[$loop->index] ?? 'spark'" class="h-4 w-4" />
                        </span>
                        <span class="text-[15.5px] font-semibold text-white">{{ $feature }}</span>
                    </div>
                    <p class="mt-2 max-h-0 overflow-hidden text-[13.5px] leading-relaxed text-white/55 transition-all duration-300 group-hover:max-h-16 group-focus-within:max-h-16">{{ $aiNotes[$loop->index] ?? '' }}</p>
                </li>
            @endforeach
        </ul>
    </div>
</section>
