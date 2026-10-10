<footer class="site-footer" aria-label="Site footer">
    <div class="site-footer-top">
        <div class="container-wide site-footer-grid">
            <div class="site-footer-brand">
                <x-cebinova-logo variant="full" tone="dark" class="max-w-full" />
                <p class="site-footer-position">{{ config('cebinova.positioning') }}</p>
                <p class="site-footer-hours">Mon–Sat · 10 AM – 7 PM</p>
                <a class="site-footer-note-link" href="{{ route('contact') }}">
                    Send your requirement
                    <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M5 12h14M13 6l6 6-6 6"/></svg>
                </a>
                @php
                    $siteSettings = \App\Models\SiteSetting::current();
                    $socialLinks = collect([
                        'linkedin' => 'LinkedIn',
                        'instagram' => 'Instagram',
                        'facebook' => 'Facebook',
                        'youtube' => 'YouTube',
                        'twitter' => 'X',
                    ])->map(function (string $label, string $network) use ($siteSettings) {
                        $saved = trim((string) $siteSettings->{$network});
                        $url = $saved !== '' ? $saved : trim((string) config('cebinova.contact.social.'.$network));

                        return [
                            'network' => $network,
                            'label' => $label,
                            'url' => $url,
                        ];
                    })->filter(fn (array $link) => $link['url'] !== '' && $link['url'] !== '#' && str_starts_with($link['url'], 'http'));
                @endphp
                @if ($socialLinks->isNotEmpty())
                    <div class="site-footer-social">
                        @foreach ($socialLinks as $link)
                            <a href="{{ $link['url'] }}" class="site-footer-social-link" data-network="{{ $link['network'] }}" aria-label="{{ $link['label'] }}" title="{{ $link['label'] }}" target="_blank" rel="noopener noreferrer">
                                <svg viewBox="0 0 24 24" aria-hidden="true">
                                    @switch($link['network'])
                                        @case('linkedin')
                                            <path d="M5.7 9.2H3.2V20h2.5V9.2zM4.45 3.4a1.55 1.55 0 1 0 0 3.1 1.55 1.55 0 0 0 0-3.1zM20.8 20h-2.5v-5.25c0-1.25-.02-2.86-1.74-2.86-1.75 0-2.02 1.36-2.02 2.77V20h-2.5V9.2h2.4v1.48h.03c.33-.63 1.15-1.3 2.37-1.3 2.54 0 3.01 1.67 3.01 3.85V20z"/>
                                            @break
                                        @case('instagram')
                                            <path fill-rule="evenodd" d="M8 3.2h8A4.8 4.8 0 0 1 20.8 8v8a4.8 4.8 0 0 1-4.8 4.8H8A4.8 4.8 0 0 1 3.2 16V8A4.8 4.8 0 0 1 8 3.2zm4 4.1a4.7 4.7 0 1 0 0 9.4 4.7 4.7 0 0 0 0-9.4z"/>
                                            <circle cx="12" cy="12" r="2.15"/>
                                            <circle cx="17.15" cy="6.85" r="1.05"/>
                                            @break
                                        @case('facebook')
                                            <path d="M14.2 8.4V6.3c0-.55.28-.8.9-.8H16.6V3.2h-2.05C12.15 3.2 11 4.55 11 6.7v1.7H9v2.45h2V21h2.55v-9.15h2.05l.35-2.45h-2.4z"/>
                                            @break
                                        @case('youtube')
                                            <path fill-rule="evenodd" d="M21.6 8.15a2.4 2.4 0 0 0-1.7-1.7C18.3 6.1 12 6.1 12 6.1s-6.3 0-7.9.35a2.4 2.4 0 0 0-1.7 1.7A25 25 0 0 0 2 12a25 25 0 0 0 .4 3.85 2.4 2.4 0 0 0 1.7 1.7c1.6.35 7.9.35 7.9.35s6.3 0 7.9-.35a2.4 2.4 0 0 0 1.7-1.7A25 25 0 0 0 22 12a25 25 0 0 0-.4-3.85zM10.2 15.15V8.85L15.5 12l-5.3 3.15z"/>
                                            @break
                                        @default
                                            <path d="M14.7 3.6h2.55L12.2 10.4 18.4 20.4h-4.85l-3.75-4.9-4.35 4.9H2.9l5.35-6.05L3.55 3.6h4.95l3.4 4.5 2.8-4.5zm-.95 15.1h1.4L8.15 5.05H6.65l7.1 13.65z"/>
                                    @endswitch
                                </svg>
                            </a>
                        @endforeach
                    </div>
                @endif
            </div>

            <nav class="site-footer-col" aria-label="Quick links">
                <p class="site-footer-heading">
                    <span class="site-footer-dot" aria-hidden="true"></span>
                    Quick Links
                </p>
                <ul class="site-footer-links is-split">
                    <li><a href="{{ route('services.index') }}">Services</a></li>
                    <li><a href="{{ route('solutions') }}">Solutions</a></li>
                    <li><a href="{{ route('marketing-packages') }}">Marketing Packages</a></li>
                    <li><a href="{{ route('demos') }}">Demos</a></li>
                    <li><a href="{{ route('pricing') }}">Pricing</a></li>
                    <li><a href="{{ route('about') }}">About</a></li>
                    <li><a href="{{ route('industries') }}">Industries</a></li>
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
                                <span class="site-footer-icon" aria-hidden="true"><x-mark name="phone" class="h-3.5 w-3.5" /></span>
                                <span>{{ cebinova_phone() }}</span>
                            </a>
                        </li>
                    @endif
                    <li>
                        <a href="mailto:{{ config('cebinova.contact.email') }}">
                            <span class="site-footer-icon" aria-hidden="true"><x-mark name="mail" class="h-3.5 w-3.5" /></span>
                            <span>{{ config('cebinova.contact.email') }}</span>
                        </a>
                    </li>
                    <li>
                        <a href="{{ whatsapp_url() }}" @if (filled(config('cebinova.contact.whatsapp'))) target="_blank" rel="noopener noreferrer" @endif>
                            <span class="site-footer-icon" aria-hidden="true"><x-mark name="whatsapp" class="h-3.5 w-3.5" /></span>
                            <span>WhatsApp</span>
                        </a>
                    </li>
                    <li>
                        <span class="site-footer-static">
                            <span class="site-footer-icon" aria-hidden="true"><x-mark name="pin" class="h-3.5 w-3.5" /></span>
                            <span>{{ config('cebinova.contact.address') }}</span>
                        </span>
                    </li>
                </ul>
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
