@props([
    'id' => 'enquiry-form',
])

@php
    $selectedService = request('service');
    $selectedCategory = request('package_category');
    $selectedDuration = request('plan_duration');
    $selectedBusinessType = request('business_type');
    $selectedMessage = request('message');
    $viaWhatsapp = request('via') === 'whatsapp';
    $packageLocked = \App\Support\MarketingPackages::isValid((string) $selectedCategory, (string) $selectedDuration);
    $packageServices = config('sarvix.form.package_services');
    $showPackageFields = $packageLocked || in_array($selectedService, $packageServices, true);
    $selectedPrice = $packageLocked ? \App\Support\MarketingPackages::formattedPrice($selectedCategory, $selectedDuration) : null;
    $allowedSources = config('sarvix.leads.sources');
    $requestedSource = request('source');
    $selectedSource = $packageLocked
        ? $selectedCategory
        : (in_array($requestedSource, $allowedSources, true) ? $requestedSource : ($viaWhatsapp ? 'WhatsApp' : 'Website Contact'));
    if ($packageLocked) {
        $selectedService = \App\Support\MarketingPackages::SERVICE;
    }
    $priceMap = \App\Support\MarketingPackages::priceMap();
@endphp

<form id="{{ $id }}" class="js-lead-form sx-form relative card-surface p-6 sm:p-8 lg:p-9" method="POST" action="{{ route('contact.store') }}" novalidate>
    @csrf
    <input type="text" name="website" tabindex="-1" autocomplete="off" class="absolute left-[-9999px] h-0 w-0 overflow-hidden" aria-hidden="true">
    <input type="hidden" name="source" class="js-lead-source" value="{{ $selectedSource }}">

    <div class="js-form-success hidden rounded-xl border border-gold/30 bg-mist p-6" hidden>
        <p class="text-lg font-bold text-navy">Thank you for contacting SARVIX Technologies.</p>
        <p class="js-form-success-text mt-2 text-sm leading-relaxed text-muted">Our team will review your requirement and get in touch with you shortly.</p>
    </div>

    <div class="js-form-fields space-y-5">
        @if ($viaWhatsapp)
            <p class="rounded-lg bg-mist px-4 py-3 text-sm text-navy">Prefer WhatsApp? Send this enquiry and we will continue the conversation there.</p>
        @endif

        @if (request('solution'))
            <div class="rounded-xl border border-gold/25 bg-mist px-4 py-4">
                <p class="text-[11px] font-bold uppercase tracking-[0.16em] text-navy/45">Selected solution</p>
                <p class="mt-1 text-[15px] font-semibold text-navy">{{ request('solution') }}{{ request('plan') ? ' · '.request('plan') : '' }}</p>
                @if (request('price'))
                    <p class="mt-1 text-sm text-navy/60">Indicative price: {{ request('price') }}</p>
                @endif
            </div>
        @endif

        <div class="grid gap-5 sm:grid-cols-2">
            <label class="block">
                <span class="mb-1.5 block text-sm font-semibold text-navy">Full Name *</span>
                <input name="name" type="text" required autocomplete="name">
                <span class="js-error mt-1 block text-xs text-red-600" data-error="name"></span>
            </label>
            <label class="block">
                <span class="mb-1.5 block text-sm font-semibold text-navy">Business Name</span>
                <input name="business_name" type="text">
                <span class="js-error mt-1 block text-xs text-red-600" data-error="business_name"></span>
            </label>
        </div>

        <div class="grid gap-5 sm:grid-cols-2">
            <label class="block">
                <span class="mb-1.5 block text-sm font-semibold text-navy">Mobile Number *</span>
                <input name="phone" type="tel" required autocomplete="tel" inputmode="tel">
                <span class="js-error mt-1 block text-xs text-red-600" data-error="phone"></span>
            </label>
            <label class="block">
                <span class="mb-1.5 block text-sm font-semibold text-navy">WhatsApp Number</span>
                <input name="whatsapp" type="tel" inputmode="tel">
                <span class="js-error mt-1 block text-xs text-red-600" data-error="whatsapp"></span>
            </label>
        </div>

        <div class="grid gap-5 sm:grid-cols-2">
            <label class="block">
                <span class="mb-1.5 block text-sm font-semibold text-navy">Email</span>
                <input name="email" type="email" autocomplete="email">
                <span class="js-error mt-1 block text-xs text-red-600" data-error="email"></span>
            </label>
            <label class="block">
                <span class="mb-1.5 block text-sm font-semibold text-navy">City</span>
                <input name="city" type="text" autocomplete="address-level2">
                <span class="js-error mt-1 block text-xs text-red-600" data-error="city"></span>
            </label>
        </div>

        <div class="grid gap-5 sm:grid-cols-2">
            <label class="block">
                <span class="mb-1.5 block text-sm font-semibold text-navy">Business Type</span>
                <select name="business_type">
                    <option value="">Select</option>
                    @foreach (config('sarvix.form.business_types') as $type)
                        <option value="{{ $type }}" @selected($selectedBusinessType === $type)>{{ $type }}</option>
                    @endforeach
                </select>
                <span class="js-error mt-1 block text-xs text-red-600" data-error="business_type"></span>
            </label>
            <label class="block">
                <span class="mb-1.5 block text-sm font-semibold text-navy">Service Interested In *</span>
                @if ($packageLocked)
                    <input type="hidden" name="service" class="js-service-select" value="{{ \App\Support\MarketingPackages::SERVICE }}">
                    <p class="rounded-lg border border-line bg-mist px-3 py-2.5 text-sm font-semibold text-navy">{{ \App\Support\MarketingPackages::SERVICE }}</p>
                @else
                    <select name="service" required class="js-service-select">
                        <option value="">Select</option>
                        @foreach (config('sarvix.form.services') as $service)
                            <option value="{{ $service }}" @selected($selectedService === $service)>{{ $service }}</option>
                        @endforeach
                    </select>
                @endif
                <span class="js-error mt-1 block text-xs text-red-600" data-error="service"></span>
            </label>
        </div>

        <div class="js-package-fields space-y-5 {{ $showPackageFields ? '' : 'hidden' }}" data-package-services='@json($packageServices)' data-package-prices='@json($priceMap)' data-locked="{{ $packageLocked ? '1' : '0' }}" @if (! $showPackageFields) hidden @endif>
            <div class="js-package-summary rounded-xl border border-gold/25 bg-mist px-4 py-4 {{ $selectedPrice ? '' : 'hidden' }}" @if (! $selectedPrice) hidden @endif>
                <p class="text-[11px] font-bold uppercase tracking-[0.16em] text-navy/45">Selected plan</p>
                <p class="js-package-summary-text mt-1 text-[15px] font-semibold text-navy">
                    {{ $packageLocked ? $selectedCategory.' · '.$selectedDuration : '' }}
                </p>
                <p class="mt-1 text-sm text-navy/60">{{ $packageLocked ? \App\Support\MarketingPackages::SERVICE : '' }}</p>
                <p class="js-package-summary-price mt-2 whitespace-nowrap text-xl font-extrabold text-navy">{{ $selectedPrice }}</p>
                <p class="mt-1 text-sm font-semibold text-navy/55">Selected Package Price</p>
            </div>

            @if ($packageLocked)
                <input type="hidden" name="package_category" class="js-package-category" value="{{ $selectedCategory }}">
                <input type="hidden" name="plan_duration" class="js-plan-duration" value="{{ $selectedDuration }}">
            @else
                <div class="grid gap-5 sm:grid-cols-2">
                    <label class="block">
                        <span class="mb-1.5 block text-sm font-semibold text-navy">Package Category</span>
                        <select name="package_category" class="js-package-category">
                            <option value="">Select</option>
                            @foreach (\App\Support\MarketingPackages::categories() as $category)
                                <option value="{{ $category }}" @selected($selectedCategory === $category)>{{ $category }}</option>
                            @endforeach
                        </select>
                        <span class="js-error mt-1 block text-xs text-red-600" data-error="package_category"></span>
                    </label>
                    <label class="block">
                        <span class="mb-1.5 block text-sm font-semibold text-navy">Plan Duration</span>
                        <select name="plan_duration" class="js-plan-duration">
                            <option value="">Select</option>
                            @foreach (\App\Support\MarketingPackages::durations() as $duration)
                                <option value="{{ $duration }}" @selected($selectedDuration === $duration)>{{ $duration }}</option>
                            @endforeach
                        </select>
                        <span class="js-error mt-1 block text-xs text-red-600" data-error="plan_duration"></span>
                    </label>
                </div>
            @endif
        </div>

        <label class="block">
            <span class="mb-1.5 block text-sm font-semibold text-navy">Budget Range</span>
            <select name="budget">
                @foreach (config('sarvix.form.budgets') as $budget)
                    <option value="{{ $budget }}">{{ $budget }}</option>
                @endforeach
            </select>
            <span class="js-error mt-1 block text-xs text-red-600" data-error="budget"></span>
        </label>

        <label class="block">
            <span class="mb-1.5 block text-sm font-semibold text-navy">Message</span>
            <textarea name="message" rows="4" placeholder="Tell us what you need to start, sell, manage or grow.">{{ $selectedMessage }}</textarea>
            <span class="js-error mt-1 block text-xs text-red-600" data-error="message"></span>
        </label>

        <label class="flex items-start gap-3 text-sm text-navy">
            <input type="checkbox" name="consultation" value="1" @checked($selectedSource === 'Free Consultation' || $selectedSource !== 'WhatsApp') class="mt-1 h-4 w-4 rounded border-line text-gold focus:ring-gold">
            <span>I would like a free consultation.</span>
        </label>

        <p class="js-form-error hidden text-sm text-red-600" hidden></p>

        <x-button type="submit" class="js-submit w-full sm:w-auto">
            <span class="js-submit-label">Send enquiry</span>
            <span class="js-submit-loading hidden">Sending…</span>
        </x-button>
    </div>
</form>
