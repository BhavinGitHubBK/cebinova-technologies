<section class="section-pad-lg section-soft">
    <div class="container-wide">
        <div data-reveal>
            <x-section-heading eyebrow="How we work" title="Simple Process. Powerful Results.">
                A clear path from conversation to launch - without unnecessary complexity.
            </x-section-heading>
        </div>
        <div class="relative mt-14" id="process">
            <div class="pointer-events-none absolute left-[4%] right-[4%] top-[22px] hidden h-[2px] bg-line xl:block">
                <div id="process-progress" class="h-full origin-left scale-x-0 bg-gold"></div>
            </div>
            <ol class="relative grid gap-0 sm:grid-cols-2 sm:gap-10 xl:grid-cols-5 xl:gap-6">
                @foreach (config('sarvix.process') as $item)
                    <li class="relative flex gap-4 sm:block {{ $loop->last ? '' : 'pb-8 sm:pb-0' }}">
                        <div class="flex flex-col items-center sm:mb-6 sm:flex-row sm:justify-start">
                            <span class="relative z-[1] flex h-11 w-11 shrink-0 items-center justify-center rounded-full border-2 border-gold bg-white text-sm font-extrabold text-navy">{{ $item['step'] }}</span>
                            @if (! $loop->last)
                                <span class="mt-1 w-px flex-1 bg-gold/30 sm:hidden" aria-hidden="true"></span>
                            @endif
                        </div>
                        <div class="min-w-0 pt-1 sm:pt-0">
                            <p class="text-[2.4rem] font-extrabold leading-none text-gold/25">{{ $item['step'] }}</p>
                            <h3 class="mt-2 text-xl font-extrabold text-navy">{{ $item['title'] }}</h3>
                            <p class="mt-2 text-[15.5px] leading-relaxed text-muted">{{ $item['text'] }}</p>
                        </div>
                    </li>
                @endforeach
            </ol>
        </div>
    </div>
</section>
