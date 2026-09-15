@extends('layouts.app')

@section('title', 'Privacy Policy | SARVIX Technologies')
@section('description', 'How SARVIX Technologies handles enquiry information submitted through this website.')

@section('content')
    <x-page-hero
        :wrap="true"
        eyebrow="Legal"
        title="Privacy Policy"
        text="A clear policy for the presentation website. This will be refined before public launch."
    >
        @include('sections.page.hero-ctas', [
            'consultHref' => consultation_url(),
        ])
    </x-page-hero>
    <section class="section-pad bg-white">
        <div class="container-wide max-w-3xl space-y-5 text-base leading-relaxed text-muted">
            <p>SARVIX Technologies collects the information you submit through the enquiry form - such as name, business name, phone, email and project details - so we can respond to your request.</p>
            <p>We do not sell this information. It is stored to follow up on consultations and related work.</p>
            <p>If you use WhatsApp, that conversation is also subject to WhatsApp’s own terms.</p>
            <p>For privacy questions, email <a class="font-medium text-navy" href="mailto:{{ config('sarvix.contact.email') }}">{{ config('sarvix.contact.email') }}</a>.</p>
            <div class="pt-4">
                <x-button href="{{ consultation_url() }}">Get Free Consultation</x-button>
            </div>
        </div>
    </section>
@endsection
