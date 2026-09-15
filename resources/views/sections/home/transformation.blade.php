<section class="section-pad-lg bg-paper">
    <div class="container-wide">
        <div data-reveal>
            <x-section-heading eyebrow="Business transformation" title="From Local Business to Digital Business.">
                SARVIX helps businesses grow step by step instead of forcing complicated technology from day one.
            </x-section-heading>
        </div>

        <div class="mt-12 grid items-stretch gap-5 lg:grid-cols-12" data-stagger>
            <article class="card-surface p-7 sm:p-8 lg:col-span-4">
                <p class="text-[11px] font-bold uppercase tracking-[0.16em] text-navy/40">Traditional Business</p>
                <ul class="mt-6 space-y-3.5 text-[15.5px] text-muted">
                    @foreach (['Manual enquiries', 'Offline-only sales', 'Spreadsheet tracking', 'Repeated tasks', 'Limited visibility'] as $item)
                        <li class="flex items-start gap-3">
                            <span class="mt-2 h-1.5 w-1.5 shrink-0 rounded-full bg-navy/25"></span>{{ $item }}
                        </li>
                    @endforeach
                </ul>
            </article>

            <article class="flex flex-col justify-center rounded-[1.2rem] border border-gold/40 bg-navy px-7 py-10 text-center text-white lg:col-span-4">
                <p class="text-[11px] font-bold uppercase tracking-[0.18em] text-gold">SARVIX pathway</p>
                <p class="mt-4 text-2xl font-extrabold tracking-tight">Start → Digital → Grow</p>
                <p class="mt-4 text-[15px] leading-relaxed text-white/65">A staged move from local operations to connected systems, at the pace your team can use.</p>
            </article>

            <article class="card-surface p-7 sm:p-8 lg:col-span-4">
                <p class="text-[11px] font-bold uppercase tracking-[0.16em] text-gold-dark">Digital Business</p>
                <ul class="mt-6 space-y-3.5 text-[15.5px] text-navy/80">
                    @foreach (['Online presence', 'eCommerce', 'Central software', 'Automated workflow', 'Marketing & insights'] as $item)
                        <li class="flex items-start gap-3">
                            <span class="mt-2 h-1.5 w-1.5 shrink-0 rounded-full bg-gold"></span>{{ $item }}
                        </li>
                    @endforeach
                </ul>
            </article>
        </div>
    </div>
</section>
