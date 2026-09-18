<section class="about-page-journey section-pad-lg" aria-label="Business journey">
    <div class="container-wide">
        <div class="about-page-head" data-reveal>
            <p class="about-page-kicker">
                <span class="about-page-dot" aria-hidden="true"></span>
                Business journey
            </p>
            <h2 class="section-title">A connected path from presence to growth.</h2>
            <p class="section-support">Five stages. One partner. You start where the business is today.</p>
        </div>

        <div class="about-page-journey-grid" data-stagger>
            @foreach (config('cebinova.journey') as $item)
                <article class="about-page-journey-card">
                    <p class="about-page-journey-step">{{ $item['step'] }}</p>
                    <h3 class="about-page-journey-title">{{ $item['title'] }}</h3>
                    <ul class="about-page-journey-list">
                        @foreach ($item['items'] as $line)
                            <li>{{ $line }}</li>
                        @endforeach
                    </ul>
                </article>
            @endforeach
        </div>
    </div>
</section>
