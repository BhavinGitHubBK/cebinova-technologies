@extends('layouts.app')

@section('title', 'Contact | SARVIX Technologies')
@section('description', 'Get a free consultation from SARVIX Technologies. Tell us about your business and we will recommend the right next step.')

@section('content')
    <x-page-hero
        :wrap="true"
        eyebrow="Contact"
        title="Tell us about your business."
        text="Share where you are today. We will help you identify the right technology for the next stage of growth."
    >
        @include('sections.page.hero-ctas', [
            'consultHref' => '#enquiry-form',
            'whatsappHref' => \App\Support\MarketingPackages::isValid((string) request('package_category'), (string) request('plan_duration'))
                ? package_whatsapp_url((string) request('package_category'), (string) request('plan_duration'))
                : whatsapp_url(),
        ])
    </x-page-hero>

    <section class="section-pad section-soft">
        <div class="container-wide grid gap-8 lg:grid-cols-12">
            <div class="lg:col-span-7">
                <x-lead-form />
            </div>
            <aside class="space-y-4 lg:col-span-5">
                <div class="lift-card card-surface p-7 sm:p-8">
                    <x-badge tone="blue">Talk to SARVIX</x-badge>
                    <h2 class="mt-4 text-navy">Prefer a conversation?</h2>
                    <p class="mt-3 text-[15.5px] leading-relaxed text-muted">Use WhatsApp for a quicker introduction, or email if you would rather send documents.</p>
                    <div class="mt-6">
                        @php
                            $contactWhatsapp = \App\Support\MarketingPackages::isValid((string) request('package_category'), (string) request('plan_duration'))
                                ? package_whatsapp_url((string) request('package_category'), (string) request('plan_duration'))
                                : whatsapp_url();
                        @endphp
                        <x-button href="{{ $contactWhatsapp }}" variant="whatsapp" class="w-full group">
                            <x-icon name="whatsapp" class="h-4 w-4 text-[#25D366]" />
                            Talk to SARVIX
                            <svg class="arrow-shift h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M5 12h14M13 6l6 6-6 6"/></svg>
                        </x-button>
                    </div>
                </div>

                @if (sarvix_phone())
                    <div class="lift-card card-surface p-6">
                        <p class="text-[12px] font-bold uppercase tracking-[0.12em] text-navy/40">Phone</p>
                        <a class="mt-2 inline-flex items-center gap-3 font-semibold text-navy" href="tel:{{ preg_replace('/\s+/', '', sarvix_phone()) }}">
                            <x-icon name="phone" class="h-4 w-4 text-gold" />
                            {{ sarvix_phone() }}
                        </a>
                    </div>
                @endif
                <div class="lift-card card-surface p-6">
                    <p class="text-[12px] font-bold uppercase tracking-[0.12em] text-navy/40">Email</p>
                    <a class="mt-2 inline-flex items-center gap-3 font-semibold text-navy" href="mailto:{{ config('sarvix.contact.email') }}">
                        <x-icon name="mail" class="h-4 w-4 text-gold" />
                        {{ config('sarvix.contact.email') }}
                    </a>
                </div>
                <div class="lift-card card-surface p-6">
                    <p class="text-[12px] font-bold uppercase tracking-[0.12em] text-navy/40">Address</p>
                    <p class="mt-2 inline-flex items-center gap-3 font-semibold text-navy">
                        <x-icon name="pin" class="h-4 w-4 text-gold" />
                        {{ config('sarvix.contact.address') }}
                    </p>
                </div>
                <div class="lift-card card-surface p-6">
                    <p class="text-[12px] font-bold uppercase tracking-[0.12em] text-navy/40">Working hours</p>
                    <p class="mt-2 font-semibold text-navy">Mon–Sat · 10 AM – 7 PM</p>
                    <p class="mt-1 text-sm text-muted">Free consultation. No payment required to start.</p>
                </div>
            </aside>
        </div>
    </section>

    @include('sections.page.how-it-works')
    @include('sections.page.faq')
@endsection
