@php
    $enquiry = solution_enquiry_url([
        'solution' => $industry['title'],
        'source' => 'Industry '.$industry['title'],
        'business_type' => $industry['title'],
    ]);
    $whatsapp = page_whatsapp_url($industry['title'].' solution');
@endphp

@extends('layouts.app')

@section('title', ($industry['seo_title'] ?? null) ?: $industry['title'].' solutions | CEBINOVA Technologies')
@section('description', $industry['seo_description'] ?? $industry['need'])

@section('content')
    <x-page-hero
        :wrap="true"
        eyebrow="Industry"
        :title="$industry['title']"
        :text="$industry['need']"
    >
        <div class="flex flex-col gap-3 sm:flex-row sm:flex-wrap">
            <x-button href="{{ $enquiry }}" size="lg">Get Free Consultation</x-button>
            <x-button href="{{ $whatsapp }}" variant="outline" size="lg">Ask on WhatsApp</x-button>
        </div>
    </x-page-hero>

    <section class="section-pad section-soft">
        <div class="container-wide grid gap-10 lg:grid-cols-12">
            <div class="lg:col-span-7">
                <p class="text-[12px] font-bold uppercase tracking-[0.16em] text-gold-dark">The operating problem</p>
                <p class="mt-3 text-[17px] leading-relaxed text-navy">{{ $industry['description'] ?: $industry['need'] }}</p>

                <h2 class="mt-10 text-2xl font-extrabold text-navy">What we usually build first</h2>
                <ul class="mt-5 grid gap-3 sm:grid-cols-2">
                    @foreach ($industry['solutions'] as $item)
                        <li class="flex items-center gap-2.5 rounded-xl bg-white px-4 py-4 text-[15px] font-medium leading-none text-navy">
                            <span class="h-1.5 w-1.5 shrink-0 rounded-full bg-gold"></span>
                            @if (! empty($item['slug']))
                                <a href="{{ route('solutions.show', $item['slug']) }}">{{ $item['title'] }}</a>
                            @else
                                <span>{{ is_array($item) ? $item['title'] : $item }}</span>
                            @endif
                        </li>
                    @endforeach
                </ul>

                @if ($industry['demos'] !== [])
                    <h2 class="mt-10 text-2xl font-extrabold text-navy">Related demos</h2>
                    <ul class="mt-5 grid gap-3">
                        @foreach ($industry['demos'] as $demo)
                            <li>
                                <a class="font-semibold text-navy" href="{{ $demo['url'] }}">{{ $demo['name'] }}</a>
                            </li>
                        @endforeach
                    </ul>
                @endif
            </div>
            <aside class="lg:col-span-5">
                <div class="card-surface p-7 sm:p-8">
                    <x-badge>How a project starts</x-badge>
                    <ol class="mt-5 grid gap-4 text-[15.5px] leading-relaxed text-navy">
                        <li><strong>1. Understand the workflow.</strong> We look at how you sell, deliver and follow up today.</li>
                        <li><strong>2. Choose the first system.</strong> Website, store, software or automation. One starting point, not everything at once.</li>
                        <li><strong>3. Launch and support.</strong> You get a written scope. GST is extra when it applies.</li>
                    </ol>
                    <div class="mt-6 flex flex-col gap-3">
                        <x-button href="{{ $enquiry }}" class="w-full">Talk about {{ $industry['title'] }}</x-button>
                        <x-button href="{{ route('pricing') }}" variant="outline" class="w-full">See website pricing</x-button>
                    </div>
                    <p class="mt-5 text-[13px] leading-relaxed text-muted">This page describes the kind of work we do for this industry. It is not a client case study.</p>
                </div>
            </aside>
        </div>
    </section>

    @include('sections.home.cta')
@endsection
