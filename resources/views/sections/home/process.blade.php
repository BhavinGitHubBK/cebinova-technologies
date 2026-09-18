@php
    $icons = ['chat', 'layers', 'cpu', 'rocket', 'trend'];
    $cats = ['Discover', 'Blueprint', 'Deliver', 'Go live', 'Partner'];
@endphp

<section class="home-proc section-pad-lg" id="process" aria-label="How we work">
    <div class="container-wide">
        <div class="home-proc-head" data-reveal>
            <p class="home-proc-kicker">
                <span class="home-proc-dot" aria-hidden="true"></span>
                How we work
            </p>
            <h2 class="section-title">Simple Process. Powerful Results.</h2>
            <p class="section-support">
                A clear path from conversation to launch - without unnecessary complexity.
            </p>
        </div>

        <div class="home-proc-wrap">
            <div class="home-proc-line" aria-hidden="true">
                <span id="process-progress"></span>
            </div>
            <ol class="home-proc-list" data-stagger>
                @foreach (config('cebinova.process') as $index => $item)
                    <li class="home-proc-cell{{ $loop->last ? ' is-last' : '' }}" data-process-node>
                        <span class="home-proc-node" aria-hidden="true"></span>
                        <article class="home-proc-card">
                            <span class="home-proc-icon">
                                <x-mark :name="$icons[$index] ?? 'layers'" class="h-4 w-4" />
                            </span>
                            <span class="home-proc-copy">
                                <span class="home-proc-cat">{{ $cats[$index] ?? 'Step' }}</span>
                                <h3 class="home-proc-title">{{ $item['title'] }}</h3>
                                <p class="home-proc-text">{{ $item['text'] }}</p>
                            </span>
                            @if (! $loop->last)
                                <span class="home-proc-arrow" aria-hidden="true">
                                    <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8"><path stroke-linecap="round" stroke-linejoin="round" d="M5 12h14M13 6l6 6-6 6"/></svg>
                                </span>
                            @endif
                        </article>
                    </li>
                @endforeach
            </ol>
        </div>

        <p class="home-proc-cta">
            <x-button :href="consultation_url()" size="sm">
                Get Free Consultation
                <svg class="arrow-shift h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M5 12h14M13 6l6 6-6 6"/></svg>
            </x-button>
            <x-button :href="route('about')" variant="outline" size="sm">
                Discover CEBINOVA
                <svg class="arrow-shift h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M5 12h14M13 6l6 6-6 6"/></svg>
            </x-button>
        </p>
    </div>
</section>
