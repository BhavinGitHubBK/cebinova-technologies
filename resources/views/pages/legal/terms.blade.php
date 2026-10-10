@extends('layouts.app')

@section('title', 'Terms | CEBINOVA Technologies')
@section('description', 'Website terms for CEBINOVA Technologies.')

@section('content')
@php
    $hero = \App\Support\CmsContent::copy('terms', 'page', [
        'eyebrow' => 'Legal',
        'heading' => 'Terms',
        'lead' => 'These terms cover use of this website. Project work is scoped separately in a proposal or agreement.',
    ]);
    $article = \App\Support\CmsContent::copy('terms', 'body', [
        'body' => "This website describes services and demo solutions for CEBINOVA Technologies. Content is for information and presentation. It is not a binding offer until we confirm scope and pricing in writing.\n\nDemo projects are labelled as concept / demo solutions and should not be read as live client case studies.\n\nEnquiry submissions do not create a contract. We will confirm next steps after reviewing your business needs.",
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
        </div>
    </section>
@endsection
