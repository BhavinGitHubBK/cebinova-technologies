@extends('layouts.app')

@section('title', 'Marketing Packages | SARVIX Technologies')
@section('description', 'Your complete marketing team starting at ₹4,999/month. Compare Regular, Festival and Complete Growth. Clear prices and WhatsApp start.')

@section('content')
    <x-page-hero
        eyebrow="Marketing Packages"
        title="Your Complete Marketing Team - Starting at ₹4,999/month."
        text="We plan, design and send ready-to-post content. You approve. Customers keep seeing your business."
        :wrap="true"
    />

    @include('sections.marketing.explorer')

    <section id="complete-growth" class="section-soft py-12 lg:py-16">
        <div class="container-wide">
            @include('sections.marketing.growth')
        </div>
    </section>

    @include('sections.marketing.comparison')
    @include('sections.marketing.how-it-works')
    @include('sections.marketing.trust')
    @include('sections.marketing.faq')
    @include('sections.marketing.addons')
    @include('sections.home.cta')
    @include('sections.marketing.sticky')
@endsection
