@extends('layouts.app')

@section('title', 'Portfolio | CEBINOVA Technologies')
@section('description', 'Concept and demo solutions from CEBINOVA for retail, restaurants, eCommerce, CRM, inventory and AI automation.')

@section('content')
    <x-page-hero
        :wrap="true"
        eyebrow="Portfolio"
        title="Concept / Demo Solution gallery"
        text="These examples show how we approach real business problems. They are labelled as demos and are not claimed as live CEBINOVA client projects."
    >
        @include('sections.page.hero-ctas', [
            'consultHref' => consultation_url(),
            'extraHref' => route('demos'),
            'extraLabel' => 'View Demos',
        ])
    </x-page-hero>

    <section class="section-pad section-soft">
        <div class="container-wide grid gap-5 md:grid-cols-2 xl:grid-cols-3" data-stagger>
            @foreach (\App\Support\CmsContent::portfolio() as $project)
                <article id="{{ $project['slug'] }}" class="demo-card lift-card card-surface flex h-full scroll-mt-28 flex-col overflow-hidden">
                    <div class="relative overflow-hidden">
                        <x-demo-preview :type="$project['preview']" />
                        <div class="absolute left-3 top-3">
                            <x-badge>{{ $project['type'] }}</x-badge>
                        </div>
                    </div>
                    <div class="flex flex-1 flex-col p-6 sm:p-7">
                        <p class="text-[12px] font-semibold uppercase tracking-[0.14em] text-navy/40">{{ $project['type'] }}</p>
                        <h2 class="mt-1 text-lg text-navy sm:text-xl">{{ $project['title'] }}</h2>
                        <p class="mt-2 flex-1 text-[15.5px] leading-relaxed text-muted">{{ $project['summary'] }}</p>
                        <div class="mt-4 flex flex-wrap gap-2">
                            @foreach ($project['tags'] as $tag)
                                <span class="sx-chip">{{ $tag }}</span>
                            @endforeach
                        </div>
                    </div>
                </article>
            @endforeach
        </div>
    </section>

    @include('sections.page.how-it-works')
    @include('sections.page.faq')
    @include('sections.home.cta')
@endsection
