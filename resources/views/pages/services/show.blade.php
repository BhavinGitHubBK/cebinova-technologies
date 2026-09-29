@php
    $guide = $service;
    $nextHref = page_next_url($guide, $service['title']);
    $nextLabel = $service['next_label'] ?? 'Get Free Consultation';
    $whatsapp = page_whatsapp_url($service['title']);
    $faqs = $service['faq'] ?? [];
    $process = collect($service['sections'] ?? [])->firstWhere('type', 'process');
@endphp

@extends('layouts.app')

@section('title', $service['seo_title'] ?: $service['title'].' | CEBINOVA Technologies')
@section('description', $service['seo_description'] ?: $service['short'])

@section('content')
    <x-page-hero
        :wrap="true"
        :eyebrow="$service['category']"
        :title="$service['hero_title']"
        :text="$service['hero_subtitle']"
    >
        @include('sections.page.hero-ctas', [
            'consultHref' => service_enquiry_url($service),
            'whatsappHref' => $whatsapp,
            'extraHref' => ($guide['next_route'] ?? null) ? $nextHref : null,
            'extraLabel' => ($guide['next_route'] ?? null) ? $nextLabel : null,
        ])
    </x-page-hero>

    <section class="section-pad section-soft">
        <div class="container-wide grid gap-10 lg:grid-cols-12">
            <div class="lg:col-span-7">
                @if (! empty($service['audience']))
                    <p class="text-[12px] font-bold uppercase tracking-[0.16em] text-gold-dark">Who this is for</p>
                    <p class="mt-3 text-[17px] leading-relaxed text-navy">{{ $service['audience'] }}</p>
                @endif

                <h2 class="mt-10 text-2xl font-extrabold text-navy">What this includes</h2>
                <ul class="mt-6 grid gap-3 sm:grid-cols-2">
                    @foreach ($service['includes'] as $item)
                        <li class="flex items-center gap-2.5 rounded-xl border border-line bg-white px-4 py-3.5 text-[15px] font-medium leading-none text-navy">
                            <span class="h-1.5 w-1.5 shrink-0 rounded-full bg-gold"></span>
                            <span>{{ $item }}</span>
                        </li>
                    @endforeach
                </ul>
            </div>
            <aside class="lg:col-span-5">
                <div class="card-surface p-7 sm:p-8">
                    <x-badge>Next step</x-badge>
                    <h2 class="mt-4 text-xl font-bold text-navy">{{ $service['outcome'] ?? 'We map this to your current stage.' }}</h2>
                    <p class="mt-3 text-[15.5px] leading-relaxed text-muted">Tell us how the business operates today. We will recommend the right starting point - not a larger stack than you need.</p>
                    <div class="mt-6 flex flex-col gap-3">
                        <x-button href="{{ $guide['next_route'] ? $nextHref : service_enquiry_url($service) }}" class="w-full">{{ $nextLabel }}</x-button>
                        <x-button href="{{ $whatsapp }}" variant="outline" class="w-full">Ask on WhatsApp</x-button>
                    </div>
                    <p class="mt-5 text-[13px] leading-relaxed text-muted">GST extra, if applicable. No payment required to start.</p>
                </div>
            </aside>
        </div>
    </section>

    @foreach (collect($service['sections'] ?? [])->whereIn('type', ['benefits', 'use_cases', 'technologies', 'custom']) as $section)
        @include('sections.services.dynamic-section', ['section' => $section])
    @endforeach

    @if ($process)
        @include('sections.services.dynamic-process', ['section' => $process, 'topic' => $service['title']])
    @endif

    @include('sections.page.faq', [
        'faqItems' => $faqs,
        'topic' => $service['title'],
        'consultHref' => service_enquiry_url($service),
    ])

    <section class="section-pad bg-white">
        <div class="container-wide">
            <x-section-heading eyebrow="Also useful" title="Related services">
                Start with this page. Add the next service when the business is ready.
            </x-section-heading>
            <div class="mt-8 grid gap-5 md:grid-cols-3">
                @foreach ($relatedServices as $related)
                    <x-service-card
                        :href="route('services.show', $related['slug'])"
                        :icon="$related['icon']"
                        :title="$related['title']"
                        :text="$related['short']"
                    />
                @endforeach
            </div>
        </div>
    </section>

    @include('sections.home.cta')
@endsection
