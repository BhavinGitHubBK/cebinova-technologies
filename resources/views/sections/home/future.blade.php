@php
    $modules = ['Website', 'Commerce', 'Business Software', 'Mobile App', 'AI', 'Automation', 'Marketing'];
    $stack = [
        'Frontend' => 'JavaScript',
        'Backend' => 'Laravel / PHP',
        'Commerce' => 'Custom stores',
        'Cloud' => 'Hosted systems',
        'AI' => 'AI APIs',
        'Automation' => 'Workflows',
        'Integrations' => 'REST APIs',
    ];
@endphp

<section class="section-pad-lg bg-white">
    <div class="container-wide">
        <div class="grid items-start gap-10 lg:grid-cols-12">
            <div class="min-w-0 lg:col-span-5" data-reveal>
                <x-section-heading :wrap="true" eyebrow="Build with SARVIX" title="Start Small. Scale Without Rebuilding.">
                    Clients can start with one solution and add more as their business grows.
                </x-section-heading>
            </div>
            <div class="flex min-w-0 flex-wrap items-center gap-2 lg:col-span-7" data-stagger>
                @foreach ($modules as $module)
                    <span class="rounded-full border border-line bg-mist px-4 py-2.5 text-[14px] font-semibold text-navy">{{ $module }}</span>
                    @if (! $loop->last)
                        <span class="text-gold" aria-hidden="true">+</span>
                    @endif
                @endforeach
            </div>
        </div>

        <div class="mt-16 grid items-start gap-12 border-t border-line pt-16 lg:grid-cols-12">
            <div class="min-w-0 lg:col-span-5" data-reveal>
                <x-section-heading eyebrow="Future ecosystem" title="More Than a Service Company.">
                    Our vision is to build a connected SARVIX ecosystem where businesses can manage their websites, marketing, support, software and automation from one platform.
                </x-section-heading>
                <p class="mt-5 text-[15.5px] font-medium text-navy/70">IT services today. A connected technology platform tomorrow.</p>
                <p class="mt-3 text-[13px] font-semibold uppercase tracking-[0.14em] text-gold-dark">Future Ecosystem / Expanding Capabilities</p>
                <p class="mt-2 text-[15px] font-semibold text-navy/55">Commerce → Business → AI → Automate → Cloud</p>
            </div>
            <div class="grid gap-3 sm:grid-cols-2 lg:col-span-7" data-stagger>
                @foreach (config('sarvix.ecosystem') as $index => $item)
                    <article class="lift-card rounded-[1.15rem] border border-line bg-mist p-6 {{ $loop->last ? 'sm:col-span-2' : '' }}">
                        <div class="flex items-start justify-between gap-3">
                            <div class="min-w-0">
                                <p class="text-[11px] font-semibold uppercase tracking-[0.16em] text-gold">0{{ $index + 1 }}</p>
                                <h3 class="mt-2 text-lg font-bold text-navy">{{ $item['name'] }}</h3>
                            </div>
                            <span class="shrink-0 rounded-full border border-gold/30 px-2.5 py-1 text-[10px] font-bold uppercase tracking-wider text-gold-dark">In vision</span>
                        </div>
                        <p class="mt-2 text-[15px] leading-relaxed text-muted">{{ $item['text'] }}</p>
                    </article>
                @endforeach
            </div>
        </div>

        <div class="mt-16 border-t border-line pt-12">
            <p class="text-[12px] font-bold uppercase tracking-[0.16em] text-gold-dark">Built With Modern Technology.</p>
            <div class="mt-5 flex flex-wrap gap-2">
                @foreach ($stack as $label => $example)
                    <span class="rounded-full border border-line bg-paper px-3.5 py-2 text-[13px] font-semibold text-navy/80">
                        {{ $label }} <span class="text-navy/40">· {{ $example }}</span>
                    </span>
                @endforeach
            </div>
        </div>
    </div>
</section>
