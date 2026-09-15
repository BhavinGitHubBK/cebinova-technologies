<section class="section-pad-lg bg-mist">
    <div class="container-wide">
        <div data-reveal>
            <x-section-heading eyebrow="Solutions" title="Start With What Your Business Needs Today.">
                Choose a starting path. You can add selling, systems and automation when the business is ready.
            </x-section-heading>
        </div>
        <p class="mt-6 text-[14px] font-semibold uppercase tracking-[0.08em] text-navy/55">Start → Digital → Manage → Scale</p>
        <p class="mt-2 text-[15px] font-semibold text-navy/65">Starter → Sell Online → Manage → Automate &amp; Scale</p>
        <div class="relative mt-10">
            <div class="pointer-events-none absolute left-[8%] right-[8%] top-8 hidden h-[2px] bg-line xl:block" aria-hidden="true"></div>
            <div class="grid gap-5 md:grid-cols-2 xl:grid-cols-4" data-stagger>
                @foreach (config('sarvix.solution_paths') as $index => $path)
                    @php $highlight = $path['key'] === 'automate'; @endphp
                    <article class="lift-card relative flex h-full flex-col rounded-[1.2rem] border p-7 {{ $highlight ? 'border-gold/55 bg-navy text-white' : 'border-line bg-white' }}">
                        <div class="flex items-center justify-between gap-3">
                            <p class="relative z-[1] flex h-11 w-11 shrink-0 items-center justify-center rounded-full border-2 text-xs font-extrabold {{ $highlight ? 'border-gold bg-gold text-navy-deep' : 'border-gold bg-white text-navy' }}">0{{ $index + 1 }}</p>
                            @if ($highlight)
                                <span class="rounded-full bg-gold/15 px-2.5 py-1 text-[10px] font-bold uppercase tracking-wider text-gold">Recommended</span>
                            @elseif (! $loop->last)
                                <span class="hidden text-gold/70 xl:inline" aria-hidden="true">→</span>
                            @endif
                        </div>
                        <h3 class="mt-5 text-[1.4rem] font-extrabold {{ $highlight ? 'text-white' : 'text-navy' }}">{{ $path['title'] }}</h3>
                        <p class="mt-3 text-[15.5px] leading-relaxed {{ $highlight ? 'text-white/70' : 'text-muted' }}">{{ $path['for'] }}</p>
                        <ul class="mt-6 flex-1 space-y-2.5">
                            @foreach ($path['includes'] as $item)
                                <li class="flex items-start gap-2 text-[15px] {{ $highlight ? 'text-white/80' : 'text-navy/80' }}">
                                    <span class="mt-1.5 h-1.5 w-1.5 shrink-0 rounded-full bg-gold"></span>
                                    {{ $item }}
                                </li>
                            @endforeach
                        </ul>
                        <a href="{{ route('solutions') }}#{{ $path['key'] }}" class="mt-6 text-[15px] font-semibold {{ $highlight ? 'text-gold' : 'text-navy' }} transition hover:text-gold-dark">
                            Explore path →
                        </a>
                    </article>
                @endforeach
            </div>
        </div>
        <p class="mt-8 text-[15px] text-muted">Website, app, domain and hosting prices are on the Pricing page. Marketing retainers are listed separately under Marketing Packages.</p>
    </div>
</section>
