@php
    $whyIcons = ['nodes', 'layers', 'briefcase', 'trend', 'check', 'shield'];
@endphp

<section class="section-pad-lg bg-mist">
    <div class="container-wide">
        <div class="grid gap-5 lg:grid-cols-12" data-stagger>
            <article class="rounded-[1.4rem] bg-navy p-8 text-white sm:p-10 lg:col-span-5">
                <p class="kicker kicker-light">Why SARVIX</p>
                <p class="text-[11px] font-bold uppercase tracking-[0.18em] text-gold">From first website</p>
                <h2 class="mt-3 text-[2rem] font-extrabold leading-[1.12] tracking-tight sm:text-[2.4rem]">
                    To complete automation
                </h2>
                <p class="mt-5 text-[17px] leading-relaxed text-white/70">
                    {{ config('sarvix.positioning') }}
                </p>
                <p class="mt-4 text-[15.5px] leading-relaxed text-white/55">
                    SARVIX grows with your business. You don't need different vendors for your website, store, software, automation and digital growth.
                </p>
            </article>

            <div class="grid gap-4 sm:grid-cols-2 lg:col-span-7">
                @foreach (config('sarvix.why') as $index => $item)
                    <article class="lift-card card-surface p-6 sm:p-7">
                        <span class="icon-tile mb-4">
                            <x-icon :name="$whyIcons[$index] ?? 'check'" class="h-5 w-5" />
                        </span>
                        <h3 class="text-lg font-bold text-navy">{{ $item['title'] }}</h3>
                        <p class="mt-2 text-[15px] leading-relaxed text-muted">{{ $item['text'] }}</p>
                    </article>
                @endforeach
            </div>
        </div>
    </div>
</section>
