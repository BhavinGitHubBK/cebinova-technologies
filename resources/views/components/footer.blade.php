<footer class="site-footer" aria-label="Site footer">    <div class="site-footer-top">
        <div class="container-wide site-footer-grid">
            <div class="site-footer-brand">
                <x-cebinova-logo variant="full" tone="dark" class="max-w-full" />
                <p class="site-footer-position">{{ config('cebinova.positioning') }}</p>
                <p class="site-footer-cta">
                    <x-button href="{{ consultation_url() }}" size="sm">
                        Get Free Consultation
                        <svg class="arrow-shift h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M5 12h14M13 6l6 6-6 6"/></svg>
                    </x-button>
                </p>
            </div>

            <nav class="site-footer-col" aria-label="Quick links">
                <p class="site-footer-heading">
                    <span class="site-footer-dot" aria-hidden="true"></span>
                    Quick Links
                </p>
                <ul class="site-footer-links">
                    <li><a href="{{ route('services.index') }}">Services</a></li>
                    <li><a href="{{ route('solutions') }}">Solutions</a></li>
                    <li><a href="{{ route('marketing-packages') }}">Marketing Packages</a></li>
                    <li><a href="{{ route('pricing') }}">Pricing</a></li>
                    <li><a href="{{ route('demos') }}">Demos</a></li>
                    <li><a href="{{ route('about') }}">About</a></li>
                    <li><a href="{{ route('contact') }}">Contact</a></li>
                </ul>
            </nav>

            <nav class="site-footer-col" aria-label="Services">
                <p class="site-footer-heading">
                    <span class="site-footer-dot" aria-hidden="true"></span>
                    Services
                </p>
                <ul class="site-footer-links">
                    <li><a href="{{ route('services.show', 'web-development') }}">Website Development</a></li>
                    <li><a href="{{ route('services.show', 'ecommerce') }}">eCommerce</a></li>
                    <li><a href="{{ route('services.show', 'custom-software') }}">Custom Software</a></li>
                    <li><a href="{{ route('services.show', 'ai-automation') }}">AI &amp; Automation</a></li>
                    <li><a href="{{ route('services.show', 'digital-growth') }}">Digital Growth</a></li>
                </ul>
            </nav>

            <div class="site-footer-col site-footer-contact">
                <p class="site-footer-heading">
                    <span class="site-footer-dot" aria-hidden="true"></span>
                    Contact
                </p>
                <ul class="site-footer-contacts">
                    @if (cebinova_phone())
                        <li>
                            <a href="tel:{{ preg_replace('/\s+/', '', cebinova_phone()) }}">
                                <span class="site-footer-icon" aria-hidden="true"><x-mark name="phone" class="h-3.5 w-3.5" /></span>                                <span>{{ cebinova_phone() }}</span>
                            </a>
                        </li>
                    @else
                        <li>
                            <span class="site-footer-static">
                                <span class="site-footer-icon" aria-hidden="true"><x-mark name="phone" class="h-3.5 w-3.5" /></span>                                <span>Phone - available on request</span>
                            </span>
                        </li>
                    @endif
                    <li>
                        <a href="mailto:{{ config('cebinova.contact.email') }}">
                            <span class="site-footer-icon" aria-hidden="true"><x-mark name="mail" class="h-3.5 w-3.5" /></span>                            <span>{{ config('cebinova.contact.email') }}</span>
                        </a>
                    </li>
                    <li>
                        <a href="{{ whatsapp_url() }}" @if (filled(config('cebinova.contact.whatsapp'))) target="_blank" rel="noopener noreferrer" @endif>
                            <span class="site-footer-icon" aria-hidden="true"><x-mark name="whatsapp" class="h-3.5 w-3.5" /></span>                            <span>WhatsApp</span>
                        </a>
                    </li>
                    <li>
                        <span class="site-footer-static">
                            <span class="site-footer-icon" aria-hidden="true"><x-mark name="pin" class="h-3.5 w-3.5" /></span>                            <span>{{ config('cebinova.contact.address') }}</span>
                        </span>
                    </li>
                </ul>

                <p class="site-footer-heading site-footer-heading--social">
                    <span class="site-footer-dot" aria-hidden="true"></span>
                    Social Links
                </p>
                <div class="site-footer-social">
                    @foreach (['linkedin' => 'linkedin', 'instagram' => 'instagram', 'facebook' => 'facebook'] as $network => $icon)
                        <a href="{{ config('cebinova.contact.social.'.$network) }}" class="site-footer-social-link" aria-label="{{ ucfirst($network) }}">
                            <x-mark :name="$icon" class="h-4 w-4" />                        </a>
                    @endforeach
                </div>
            </div>
        </div>
    </div>

    <div class="site-footer-bottom">
        <div class="footer-legal container-wide">
            <p>&copy; {{ date('Y') }} CEBINOVA Technologies. All Rights Reserved.</p>
            <div class="site-footer-legal-links">
                <a href="{{ route('privacy') }}">Privacy Policy</a>
                <a href="{{ route('terms') }}">Terms</a>
            </div>
        </div>
    </div>
</footer>
