<footer class="relative overflow-hidden bg-navy-deep text-white">
    <div class="pointer-events-none absolute inset-x-0 top-0 h-px bg-gradient-to-r from-transparent via-gold/50 to-transparent"></div>
    <div class="container-wide grid gap-12 py-16 sm:grid-cols-2 lg:grid-cols-12 lg:gap-10 lg:py-20">
        <div class="lg:col-span-4">
            <x-sarvix-logo variant="full" tone="dark" class="max-w-full" />
            <p class="mt-4 text-[15px] font-semibold text-gold/90">{{ config('sarvix.tagline') }}</p>
            <p class="mt-3 max-w-sm text-[15px] leading-relaxed text-white/55">{{ config('sarvix.positioning') }}</p>
        </div>

        <div class="lg:col-span-2">
            <h2 class="text-[12px] font-bold uppercase tracking-[0.18em] text-gold">Quick Links</h2>
            <ul class="mt-5 space-y-3 text-[15px] text-white/75">
                <li><a class="transition hover:text-gold" href="{{ route('services.index') }}">Services</a></li>
                <li><a class="transition hover:text-gold" href="{{ route('solutions') }}">Solutions</a></li>
                <li><a class="transition hover:text-gold" href="{{ route('marketing-packages') }}">Marketing Packages</a></li>
                <li><a class="transition hover:text-gold" href="{{ route('pricing') }}">Pricing</a></li>
                <li><a class="transition hover:text-gold" href="{{ route('demos') }}">Demos</a></li>
                <li><a class="transition hover:text-gold" href="{{ route('contact') }}">Contact</a></li>
            </ul>
        </div>

        <div class="lg:col-span-3">
            <h2 class="text-[12px] font-bold uppercase tracking-[0.18em] text-gold">Services</h2>
            <ul class="mt-5 space-y-3 text-[15px] text-white/75">
                <li><a class="transition hover:text-gold" href="{{ route('services.show', 'web-development') }}">Website Development</a></li>
                <li><a class="transition hover:text-gold" href="{{ route('services.show', 'ecommerce') }}">eCommerce</a></li>
                <li><a class="transition hover:text-gold" href="{{ route('services.show', 'custom-software') }}">Custom Software</a></li>
                <li><a class="transition hover:text-gold" href="{{ route('services.show', 'ai-automation') }}">AI &amp; Automation</a></li>
                <li><a class="transition hover:text-gold" href="{{ route('services.show', 'digital-growth') }}">Digital Growth</a></li>
            </ul>
        </div>

        <div class="lg:col-span-3">
            <h2 class="text-[12px] font-bold uppercase tracking-[0.18em] text-gold">Contact</h2>
            <ul class="mt-5 space-y-3.5 text-[15px] text-white/75">
                @if (sarvix_phone())
                    <li class="flex items-start gap-3">
                        <x-icon name="phone" class="mt-0.5 h-4 w-4 shrink-0 text-gold" />
                        <a href="tel:{{ preg_replace('/\s+/', '', sarvix_phone()) }}" class="hover:text-gold">{{ sarvix_phone() }}</a>
                    </li>
                @else
                    <li class="flex items-start gap-3">
                        <x-icon name="phone" class="mt-0.5 h-4 w-4 shrink-0 text-gold" />
                        <span>Phone - available on request</span>
                    </li>
                @endif
                <li class="flex items-start gap-3">
                    <x-icon name="mail" class="mt-0.5 h-4 w-4 shrink-0 text-gold" />
                    <a href="mailto:{{ config('sarvix.contact.email') }}" class="break-all hover:text-gold">{{ config('sarvix.contact.email') }}</a>
                </li>
                <li class="flex items-start gap-3">
                    <x-icon name="whatsapp" class="mt-0.5 h-4 w-4 shrink-0 text-gold" />
                    <a href="{{ whatsapp_url() }}" class="hover:text-gold" @if (filled(config('sarvix.contact.whatsapp'))) target="_blank" rel="noopener noreferrer" @endif>WhatsApp</a>
                </li>
                <li class="flex items-start gap-3">
                    <x-icon name="pin" class="mt-0.5 h-4 w-4 shrink-0 text-gold" />
                    <span>{{ config('sarvix.contact.address') }}</span>
                </li>
            </ul>
            <h2 class="mt-8 text-[12px] font-bold uppercase tracking-[0.18em] text-gold">Social Links</h2>
            <div class="mt-4 flex gap-3">
                @foreach (['linkedin' => 'linkedin', 'instagram' => 'instagram', 'facebook' => 'facebook'] as $network => $icon)
                    <a href="{{ config('sarvix.contact.social.'.$network) }}" class="inline-flex h-10 w-10 items-center justify-center rounded-full border border-white/15 text-white/70 transition hover:border-gold hover:text-gold" aria-label="{{ ucfirst($network) }}">
                        <x-icon :name="$icon" class="h-4 w-4" />
                    </a>
                @endforeach
            </div>
        </div>
    </div>
    <div class="border-t border-white/10">
        <div class="container-wide flex flex-col gap-3 py-5 text-[14px] text-white/45 sm:flex-row sm:items-center sm:justify-between">
            <p>&copy; {{ date('Y') }} SARVIX Technologies. All Rights Reserved.</p>
            <div class="flex gap-5">
                <a href="{{ route('privacy') }}" class="hover:text-gold">Privacy Policy</a>
                <a href="{{ route('terms') }}" class="hover:text-gold">Terms</a>
            </div>
        </div>
    </div>
</footer>
