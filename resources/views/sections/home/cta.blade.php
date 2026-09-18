<<<<<<< Updated upstream
﻿<section class="home-cta section-pad-lg" aria-label="Next step">
=======
<section class="home-cta section-pad-lg" aria-label="Next step">
>>>>>>> Stashed changes
    <div class="pointer-events-none absolute inset-0 bg-dots-light"></div>
    <svg class="home-cta-lines" viewBox="0 0 1200 360" aria-hidden="true">
        <g fill="none" stroke="#FBB50B" stroke-opacity="0.28" stroke-width="1.1">
            <path d="M40 220C220 120 380 280 560 180C740 80 920 240 1160 140"/>
            <circle cx="560" cy="180" r="3.5" fill="#FBB50B"/>
            <circle cx="920" cy="240" r="3" fill="#FBB50B"/>
        </g>
    </svg>

    <div class="container-wide relative">
        <div class="home-cta-layout" data-stagger>
            <div class="home-cta-copy">
                <p class="home-cta-kicker">
                    <span class="home-cta-dot" aria-hidden="true"></span>
                    Next step
                </p>
                <h2 class="section-title text-white">Ready to Take Your Business Forward?</h2>
                <p class="home-cta-support section-support">
                    Start with the solution you need today. CEBINOVA can grow with you tomorrow.
                </p>
                <ol class="home-cta-path" aria-label="Growth path">
                    <li>Website</li>
                    <li>Store</li>
                    <li>Software</li>
                    <li>Automation</li>
                    <li>Growth</li>
                </ol>
                <ul class="home-cta-proof">
                    <li>
                        <span class="home-cta-proof-value">6+</span>
                        <span class="home-cta-proof-label">Solution areas</span>
                    </li>
                    <li>
                        <span class="home-cta-proof-value">ONE</span>
                        <span class="home-cta-proof-label">Technology partner</span>
                    </li>
                    <li>
                        <span class="home-cta-proof-value">E2E</span>
                        <span class="home-cta-proof-label">Digital journey</span>
                    </li>
                </ul>
            </div>

            <aside class="home-cta-panel">
                <p class="home-cta-panel-kicker">Start here</p>
                <p class="home-cta-panel-note">Tell us how the business works today. We will map the first move.</p>
                <p class="home-cta-actions">
                    <x-button href="{{ consultation_url() }}" size="lg" class="group">
                        Get Free Consultation
                        <svg class="arrow-shift h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M5 12h14M13 6l6 6-6 6"/></svg>
                    </x-button>
                    <x-button href="{{ whatsapp_url() }}" variant="light" size="lg">
<<<<<<< Updated upstream
                        <x-mark name="whatsapp" class="h-4 w-4" />
=======
                        <x-icon name="whatsapp" class="h-4 w-4" />
>>>>>>> Stashed changes
                        Talk to CEBINOVA
                    </x-button>
                </p>
                <ul class="home-cta-contacts">
                    <li>
                        <a href="tel:{{ preg_replace('/\s+/', '', (string) config('cebinova.contact.phone')) }}">
<<<<<<< Updated upstream
                            <x-mark name="phone" class="h-3.5 w-3.5" />
=======
                            <x-icon name="phone" class="h-3.5 w-3.5" />
>>>>>>> Stashed changes
                            {{ config('cebinova.contact.phone') }}
                        </a>
                    </li>
                    <li>
                        <a href="mailto:{{ config('cebinova.contact.email') }}">
<<<<<<< Updated upstream
                            <x-mark name="mail" class="h-3.5 w-3.5" />
=======
                            <x-icon name="mail" class="h-3.5 w-3.5" />
>>>>>>> Stashed changes
                            {{ config('cebinova.contact.email') }}
                        </a>
                    </li>
                </ul>
            </aside>
        </div>
    </div>
</section>
