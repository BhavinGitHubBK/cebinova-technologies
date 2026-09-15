@extends('layouts.app')

@section('title', 'Terms | SARVIX Technologies')
@section('description', 'Website terms for SARVIX Technologies.')

@section('content')
    <x-page-hero
        :wrap="true"
        eyebrow="Legal"
        title="Terms"
        text="These terms cover use of this website. Project work is scoped separately in a proposal or agreement."
    >
        @include('sections.page.hero-ctas', [
            'consultHref' => consultation_url(),
        ])
    </x-page-hero>
    <section class="section-pad bg-white">
        <div class="container-wide max-w-3xl space-y-5 text-base leading-relaxed text-muted">
            <p>This website describes services and demo solutions for SARVIX Technologies. Content is for information and presentation. It is not a binding offer until we confirm scope and pricing in writing.</p>
            <p>Demo projects are labelled as concept / demo solutions and should not be read as live client case studies.</p>
            <p>Enquiry submissions do not create a contract. We will confirm next steps after reviewing your business needs.</p>
            <div class="pt-4">
                <x-button href="{{ consultation_url() }}">Get Free Consultation</x-button>
            </div>
        </div>
    </section>
@endsection
