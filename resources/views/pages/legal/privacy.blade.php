@extends('layouts.app')

@section('title', 'Privacy Policy | CEBINOVA Technologies')
@section('description', 'How CEBINOVA Technologies handles enquiry information submitted through this website.')

@section('content')
@php
    $hero = \App\Support\CmsContent::copy('privacy', 'page', [
        'eyebrow' => 'Legal',
        'heading' => 'Privacy Policy',
        'lead' => 'A clear policy for the presentation website. This will be refined before public launch.',
    ]);
    $article = \App\Support\CmsContent::copy('privacy', 'body', [
        'body' => "CEBINOVA Technologies collects the information you submit through the enquiry form - such as name, business name, phone, email and project details - so we can respond to your request.\n\nWe do not sell this information. It is stored to follow up on consultations and related work.\n\nIf you use WhatsApp, that conversation is also subject to WhatsApp’s own terms.",
    ]);
    $paragraphs = preg_split("/\R\s*\R/", trim($article['body'])) ?: [];
@endphp
    <x-page-hero
        :wrap="true"
        eyebrow="{{ $hero['eyebrow'] }}"
        :title="$hero['heading']"
        :text="$hero['lead']"
    >
        @include('sections.page.hero-ctas', [
            'consultHref' => \App\Support\CmsContent::href($hero['cta_url'], consultation_url()),
            'consultLabel' => $hero['cta_label'],
        ])
    </x-page-hero>
    <section class="section-pad bg-white">
        <div class="container-wide max-w-3xl space-y-5 text-base leading-relaxed text-muted">
            @foreach ($paragraphs as $paragraph)
                <p class="break-words">{{ $paragraph }}</p>
            @endforeach
            <p>For privacy questions, email <a class="font-medium text-navy" href="mailto:{{ config('cebinova.contact.email') }}">{{ config('cebinova.contact.email') }}</a>.</p>
            <p>If you allow analytics, the website stores a random first-party identifier in your browser. That identifier is used to understand pages, traffic sources and enquiries. It is not built from your name, email, phone or a device fingerprint. Declining analytics does not block the website. Enquiry details you choose to submit are stored separately so the team can reply.</p>
        </div>
    </section>
@endsection
