@php
    $questions = collect(config('cebinova.service_guides', []))
        ->map(function ($guide, $slug) {
            $first = $guide['faq'][0] ?? null;
            if (! is_array($first)) {
                return null;
            }

            return [
                'q' => $first['q'],
                'a' => $first['a'],
                'slug' => $slug,
            ];
        })
        ->filter()
        ->values();
@endphp

@if ($questions->isNotEmpty())
<section class="section-pad bg-white" aria-label="Service questions">
    <div class="container-wide max-w-3xl">
        <x-section-heading eyebrow="Questions" title="Questions people ask before they start.">
            A short answer for each service. The service page has the rest.
        </x-section-heading>
        <div class="mt-8 grid gap-4">
            @foreach ($questions as $item)
                <article class="card-surface p-6">
                    <h3 class="text-lg font-extrabold text-navy">{{ $item['q'] }}</h3>
                    <p class="mt-2 text-[15px] leading-relaxed text-muted">{{ $item['a'] }}</p>
                </article>
            @endforeach
        </div>
    </div>
</section>
@endif
