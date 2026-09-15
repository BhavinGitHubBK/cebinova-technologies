@extends('layouts.app')

@section('title', 'Industries | SARVIX Technologies')
@section('description', 'Technology solutions for kirana stores, dairy, restaurants, retail, manufacturing, healthcare, education, real estate and more.')

@section('content')
    <x-page-hero
        :wrap="true"
        eyebrow="Industries"
        title="Technology for Every Business."
        text="Sample starting solutions for the kinds of businesses we are built to help. If you do not see yours, tell us how you operate."
    >
        @include('sections.page.hero-ctas', [
            'consultHref' => consultation_url(),
        ])
    </x-page-hero>

    @php
        $icons = [
            'kirana' => 'bag', 'dairy' => 'home', 'food' => 'store', 'retail' => 'cart',
            'manufacturing' => 'factory', 'wholesale' => 'layers', 'export' => 'globe',
            'professional' => 'briefcase', 'healthcare' => 'heart', 'education' => 'book',
            'real-estate' => 'building', 'startups' => 'rocket', 'services' => 'users',
        ];
    @endphp

    <section class="section-pad section-soft">
        <div class="container-wide grid gap-5 md:grid-cols-2" data-stagger>
            @foreach (config('sarvix.industries') as $industry)
                <article id="{{ $industry['slug'] }}" class="lift-card card-surface scroll-mt-28 p-7 sm:p-8">
                    <div class="flex items-start gap-4">
                        <span class="icon-tile shrink-0">
                            <x-icon :name="$icons[$industry['slug']] ?? 'globe'" class="h-5 w-5" />
                        </span>
                        <div class="min-w-0">
                            <h2 class="text-xl text-navy">{{ $industry['title'] }}</h2>
                            <p class="mt-3 text-[15.5px] leading-relaxed text-muted">{{ $industry['need'] }}</p>
                        </div>
                    </div>
                    <p class="mt-6"><x-badge tone="blue">Sample solutions</x-badge></p>
                    <ul class="mt-3 flex flex-wrap gap-2">
                        @foreach ($industry['solutions'] as $item)
                            <li class="sx-chip">{{ $item }}</li>
                        @endforeach
                    </ul>
                </article>
            @endforeach
        </div>
        <div class="container-wide mt-10 flex flex-col gap-3 sm:flex-row">
            <x-button href="{{ consultation_url() }}">Tell Us About Your Business</x-button>
            <x-button href="{{ whatsapp_url() }}" variant="outline">Ask on WhatsApp</x-button>
        </div>
    </section>

    @include('sections.page.how-it-works')
    @include('sections.page.faq')
    @include('sections.home.cta')
@endsection
