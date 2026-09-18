<<<<<<< Updated upstream
﻿@php
=======
@php
>>>>>>> Stashed changes
    $contactWhatsapp = \App\Support\MarketingPackages::isValid((string) request('package_category'), (string) request('plan_duration'))
        ? package_whatsapp_url((string) request('package_category'), (string) request('plan_duration'))
        : whatsapp_url();
    $emails = array_values(array_filter([
        config('cebinova.contact.email'),
        config('cebinova.contact.email_alt'),
    ]));
    $emails = array_values(array_unique($emails));
@endphp

<section class="contact-page-panel section-pad-lg" aria-label="Contact form and details">
    <div class="container-wide">
        <div class="contact-page-layout">
            <div class="contact-page-form-col">
                <div class="contact-page-form-head" data-reveal>
                    <p class="contact-page-kicker">
                        <span class="contact-page-dot" aria-hidden="true"></span>
                        Enquiry form
                    </p>
                    <h2 class="section-title">Send your requirement.</h2>
                    <p class="section-support">Fill the form and our team will get back with the right next step.</p>
                </div>
                <x-lead-form />
            </div>

            <aside class="contact-page-aside" aria-label="Contact details">
                <article class="contact-page-card contact-page-card--primary">
                    <p class="contact-page-kicker">
                        <span class="contact-page-dot" aria-hidden="true"></span>
                        Talk to CEBINOVA
                    </p>
                    <h2 class="contact-page-card-title">Prefer a conversation?</h2>
                    <p class="contact-page-card-text">Use WhatsApp for a quicker introduction, or email if you would rather send documents.</p>
                    <div class="contact-page-card-actions">
                        <x-button href="{{ $contactWhatsapp }}" variant="whatsapp" class="w-full group">
<<<<<<< Updated upstream
                            <x-mark name="whatsapp" class="h-4 w-4 text-[#25D366]" />
=======
                            <x-icon name="whatsapp" class="h-4 w-4 text-[#25D366]" />
>>>>>>> Stashed changes
                            Talk to CEBINOVA
                            <svg class="arrow-shift h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M5 12h14M13 6l6 6-6 6"/></svg>
                        </x-button>
                    </div>
                </article>

                @if (cebinova_phone())
                    <article class="contact-page-card">
                        <p class="contact-page-meta-label">Phone</p>
                        <a class="contact-page-meta-link" href="tel:{{ preg_replace('/\s+/', '', cebinova_phone()) }}">
<<<<<<< Updated upstream
                            <x-mark name="phone" class="h-4 w-4" />
=======
                            <x-icon name="phone" class="h-4 w-4" />
>>>>>>> Stashed changes
                            <span>{{ cebinova_phone() }}</span>
                        </a>
                    </article>
                @endif

                <article class="contact-page-card">
                    <p class="contact-page-meta-label">Email</p>
                    <div class="contact-page-meta-stack">
                        @foreach ($emails as $email)
                            <a class="contact-page-meta-link" href="mailto:{{ $email }}">
<<<<<<< Updated upstream
                                <x-mark name="mail" class="h-4 w-4" />
=======
                                <x-icon name="mail" class="h-4 w-4" />
>>>>>>> Stashed changes
                                <span>{{ $email }}</span>
                            </a>
                        @endforeach
                    </div>
                </article>

                <article class="contact-page-card">
                    <p class="contact-page-meta-label">Address</p>
                    <p class="contact-page-meta-address">
<<<<<<< Updated upstream
                        <x-mark name="pin" class="h-4 w-4" />
=======
                        <x-icon name="pin" class="h-4 w-4" />
>>>>>>> Stashed changes
                        <span>{{ config('cebinova.contact.address') }}</span>
                    </p>
                    @if (filled(config('cebinova.contact.maps_url')))
                        <a
                            class="contact-page-map-link"
                            href="{{ config('cebinova.contact.maps_url') }}"
                            target="_blank"
                            rel="noopener noreferrer"
                        >
                            Open in Google Maps
                            <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M5 12h14M13 6l6 6-6 6"/></svg>
                        </a>
                    @endif
                </article>

                <article class="contact-page-card">
                    <p class="contact-page-meta-label">Working hours</p>
<<<<<<< Updated upstream
                    <p class="contact-page-meta-strong">Monâ€“Sat Â· 10 AM â€“ 7 PM</p>
=======
                    <p class="contact-page-meta-strong">Mon–Sat · 10 AM – 7 PM</p>
>>>>>>> Stashed changes
                    <p class="contact-page-card-text">Free consultation. No payment required to start.</p>
                </article>
            </aside>
        </div>
    </div>
</section>
