@if (! empty($section['items']) || filled($section['content']))
<section class="section-pad section-soft">
    <div class="container-wide">
        <x-section-heading :title="$section['title'] ?: ucfirst(str_replace('_', ' ', $section['type']))">
            {{ $section['subtitle'] }}
        </x-section-heading>
        @if (filled($section['content']))
            <p class="mt-6 max-w-3xl text-[16px] leading-relaxed text-muted">{{ $section['content'] }}</p>
        @endif
        <div class="mt-8 grid gap-5 md:grid-cols-2 lg:grid-cols-3">
            @foreach ($section['items'] as $item)
                <article class="card-surface p-6">
                    <h3 class="text-lg font-extrabold text-navy">{{ $item['title'] }}</h3>
                    @if ($item['description'])<p class="mt-2 text-[15px] leading-relaxed text-muted">{{ $item['description'] }}</p>@endif
                </article>
            @endforeach
        </div>
    </div>
</section>
@endif
