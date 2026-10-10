<section class="mkt-page-faq section-pad-lg" aria-label="Marketing questions">
    <div class="container-wide">
        <div class="mkt-page-head" data-reveal>
            <p class="mkt-page-kicker">
                <span class="mkt-page-dot" aria-hidden="true"></span>
                Questions
            </p>
            <h2 class="section-title">Questions clients ask before they start.</h2>
        </div>
        <div class="mkt-page-faq-list">
            @foreach (config('cebinova.marketing.faq', []) as $item)
                <details class="mkt-page-faq-item">
                    <summary>{{ $item['q'] }}</summary>
                    <p class="mkt-page-faq-a">{{ $item['a'] }}</p>
                </details>
            @endforeach
        </div>
    </div>
</section>
@if (count(config('cebinova.marketing.faq', [])) > 0)
    @push('head')
        <script type="application/ld+json">
            {!! json_encode([
                '@@context' => 'https://schema.org',
                '@type' => 'FAQPage',
                'mainEntity' => collect(config('cebinova.marketing.faq'))->map(fn ($item) => [
                    '@type' => 'Question',
                    'name' => $item['q'],
                    'acceptedAnswer' => ['@type' => 'Answer', 'text' => $item['a']],
                ])->values()->all(),
            ], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES) !!}
        </script>
    @endpush
@endif
