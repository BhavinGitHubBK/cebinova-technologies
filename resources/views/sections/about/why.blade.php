@php
    $whyIcons = ['nodes', 'layers', 'briefcase', 'trend', 'check', 'shield'];
@endphp

<section class="about-page-why section-pad-lg" aria-label="Why CEBINOVA">
    <div class="container-wide">
        <div class="about-page-head" data-reveal>
            <p class="about-page-kicker">
                <span class="about-page-dot" aria-hidden="true"></span>
                Why CEBINOVA
            </p>
            <h2 class="section-title">Why businesses work with one technology partner.</h2>
            <p class="section-support">Clear reasons. Practical outcomes. No vendor switching at every stage.</p>
        </div>

        <div class="about-page-why-grid" data-stagger>
            @foreach (config('cebinova.why') as $index => $item)
                <article class="about-page-card">
                    <span class="about-page-icon">
                        <x-mark :name="$whyIcons[$index] ?? 'check'" class="h-5 w-5" />
                    </span>
                    <h3 class="about-page-card-title">{{ $item['title'] }}</h3>
                    <p class="about-page-card-text">{{ $item['text'] }}</p>
                </article>
            @endforeach
        </div>
    </div>
</section>
