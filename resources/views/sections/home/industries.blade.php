@php
    $icons = [
        'kirana' => 'bag', 'dairy' => 'home', 'food' => 'store', 'retail' => 'cart',
        'manufacturing' => 'factory', 'wholesale' => 'layers', 'export' => 'globe',
        'professional' => 'briefcase', 'healthcare' => 'heart', 'education' => 'book',
        'real-estate' => 'building', 'startups' => 'rocket', 'services' => 'users',
    ];
    $preview = collect(config('sarvix.nav_solutions.industries'));
    $industries = collect(config('sarvix.industries'))->keyBy('slug');
@endphp

<section class="section-pad-lg section-soft">
    <div class="container-wide">
        <div data-reveal>
            <x-section-heading eyebrow="Industries" title="Technology for Every Business.">
                A concise look at the kinds of businesses SARVIX is built to help.
            </x-section-heading>
        </div>
        <div class="mt-10 flex gap-4 overflow-x-auto pb-2 sm:grid sm:grid-cols-2 sm:overflow-visible lg:grid-cols-3 xl:grid-cols-4" data-stagger>
            @foreach ($preview as $item)
                @php $industry = $industries[$item['slug']] ?? null; @endphp
                @if ($industry)
                    <a href="{{ route('industries') }}#{{ $industry['slug'] }}" class="lift-card card-surface flex min-w-[16.5rem] flex-col p-6 sm:min-w-0">
                        <span class="icon-tile mb-4">
                            <x-icon :name="$icons[$industry['slug']] ?? 'globe'" class="h-4 w-4" />
                        </span>
                        <h3 class="text-[16px] font-extrabold text-navy">{{ $item['label'] }}</h3>
                        <p class="mt-2 text-[14.5px] leading-relaxed text-muted">{{ $industry['need'] }}</p>
                    </a>
                @endif
            @endforeach
        </div>
        <div class="mt-8">
            <x-button href="{{ route('industries') }}" variant="outline">View All Industries</x-button>
        </div>
    </div>
</section>
