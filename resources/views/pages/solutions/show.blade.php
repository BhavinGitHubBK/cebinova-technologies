@php
    $enquiry = solution_enquiry_url([
        'solution' => $solution['title'],
        'source' => $solution['source'],
        'business_type' => $solution['business_type'],
    ]);
    $guide = config('sarvix.solution_guides.'.$solution['slug'], []);
    $nextHref = page_next_url($guide, $solution['title']);
    $nextLabel = $guide['next_label'] ?? 'Request this solution';
    $whatsapp = page_whatsapp_url($solution['title'].' solution');
    $faqs = config('sarvix.page.faq', []);
@endphp

@extends('layouts.app')

@section('title', 'SARVIX '.$solution['title'].' Solution | SARVIX Technologies')
@section('description', $solution['summary'])

@section('content')
    <x-page-hero
        :wrap="true"
        eyebrow="SARVIX Solution"
        :title="$solution['heading']"
        :text="$solution['summary']"
    >
        <div class="flex flex-col gap-3 sm:flex-row sm:flex-wrap">
            @if ($solution['demo'])
                <x-button href="{{ demo_url($solution['demo']) }}" size="lg" class="group">
                    View Demo
                    <svg class="arrow-shift h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M5 12h14M13 6l6 6-6 6"/></svg>
                </x-button>
            @endif
            <x-button href="{{ $enquiry }}" :variant="$solution['demo'] ? 'outline' : 'primary'" size="lg">Get Free Consultation</x-button>
            <x-button href="{{ $whatsapp }}" variant="outline" size="lg">Ask on WhatsApp</x-button>
        </div>
    </x-page-hero>

    <section class="section-pad section-soft">
        <div class="container-wide grid gap-10 lg:grid-cols-12">
            <div class="lg:col-span-7">
                @if ($solution['preview'])
                    <img src="{{ asset('assets/'.$solution['preview']) }}" alt="" class="w-full rounded-[1.15rem] border border-line object-cover">
                @endif
                @if (! empty($guide['best_for']))
                    <p class="{{ $solution['preview'] ? 'mt-8' : '' }} text-[12px] font-bold uppercase tracking-[0.16em] text-gold-dark">Who this is for</p>
                    <p class="mt-3 text-[17px] leading-relaxed text-navy">{{ $guide['best_for'] }}</p>
                @endif
                <h2 class="mt-8 text-2xl font-extrabold text-navy">What this solution covers</h2>
                <ul class="mt-5 grid gap-3 sm:grid-cols-2">
                    @foreach ($solution['capabilities'] as $capability)
                        <li class="flex items-center gap-2.5 rounded-xl bg-white px-4 py-4 text-[15px] font-medium leading-none text-navy">
                            <span class="h-1.5 w-1.5 shrink-0 rounded-full bg-gold"></span>
                            <span>{{ $capability }}</span>
                        </li>
                    @endforeach
                </ul>
            </div>
            <aside class="lg:col-span-5">
                <div class="card-surface p-7 sm:p-8">
                    <x-badge>Next step</x-badge>
                    <h2 class="mt-4 text-xl font-bold text-navy">{{ $guide['outcome'] ?? 'Talk to SARVIX about this solution' }}</h2>
                    <p class="mt-3 text-[15.5px] leading-relaxed text-muted">Tell us how the business operates today. We will map the right starting point - website, store, software or automation.</p>
                    <div class="mt-6 flex flex-col gap-3">
                        <x-button href="{{ $nextHref }}" class="w-full">{{ $nextLabel }}</x-button>
                        <x-button href="{{ $enquiry }}" variant="outline" class="w-full">Request this solution</x-button>
                        <x-button href="{{ $whatsapp }}" variant="outline" class="w-full">Ask on WhatsApp</x-button>
                    </div>
                    <p class="mt-5 text-[13px] leading-relaxed text-muted">GST extra, if applicable. Demos are examples of what we can build - not live client case studies.</p>
                </div>
            </aside>
        </div>
    </section>

    @include('sections.page.how-it-works', ['topic' => $solution['title']])

    @include('sections.page.faq', [
        'faqItems' => $faqs,
        'topic' => $solution['title'],
    ])

    @include('sections.home.cta')
@endsection
