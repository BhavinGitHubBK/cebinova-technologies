@php
    $growth = \App\Support\MarketingPackages::packageArray('growth') ?? ['plans' => [], 'badge' => null, 'heading' => '', 'title' => '', 'service' => 'Complete Growth'];
    $defaultPlan = $growth['plans']['yearly'] ?? reset($growth['plans']) ?: [];
@endphp

<article class="mkt-page-growth-card">
    <div class="mkt-page-growth-grid">
        <div class="mkt-page-growth-pricing">
            <p class="mkt-page-growth-flag">{{ $growth['badge'] }} · Most Popular</p>
            <h2 class="section-title mkt-page-growth-title">{{ $growth['heading'] }}</h2>
            <p class="mkt-page-growth-lead">{{ $growth['subheading'] }}</p>
            <p class="mkt-page-growth-why">{{ $growth['why'] }}</p>

            <div class="mkt-page-growth-formula">
                <div class="mkt-page-growth-formula-item">
                    <p>Regular Marketing</p>
                </div>
                <span class="mkt-page-growth-formula-op" aria-hidden="true">+</span>
                <div class="mkt-page-growth-formula-item">
                    <p>Festival Marketing</p>
                </div>
                <span class="mkt-page-growth-formula-op" aria-hidden="true">=</span>
                <div class="mkt-page-growth-formula-item mkt-page-growth-formula-item--result">
                    <p>Complete Growth</p>
                </div>
            </div>

            <div class="mkt-page-growth-durations" role="tablist" aria-label="Complete Growth durations">
                @foreach ($growth['plans'] as $plan)
                    <button
                        type="button"
                        class="growth-duration js-growth-duration {{ $plan['label'] === 'Yearly' ? 'is-active' : '' }}"
                        data-url="{{ package_enquiry_url($growth['service'], $plan['label']) }}"
                        data-whatsapp="{{ package_whatsapp_url($growth['service'], $plan['label']) }}"
                        data-price="{{ cebinova_inr($plan['price']) }}"
                        data-period="{{ $plan['period'] }}"
                        data-duration="{{ $plan['label'] }}"
                        data-stack="{{ \App\Support\MarketingPackages::priceHeadline($growth['service'], $plan['label']) }}"
                        data-sticky-name="Complete Growth Â· {{ $plan['label'] }}"
                        data-sticky-price="{{ \App\Support\MarketingPackages::priceHeadline($growth['service'], $plan['label']) }}"
                        data-sticky-cta="{{ package_enquiry_url($growth['service'], $plan['label']) }}"
                        data-sticky-wa="{{ package_whatsapp_url($growth['service'], $plan['label']) }}"
                    >
                        <span class="growth-duration-label">{{ $plan['label'] }}</span>
                        <span class="growth-duration-price">{{ cebinova_inr($plan['price']) }}</span>
                        @if ($plan['badge'] ?? null)
                            <span class="growth-duration-badge">{{ $plan['badge'] }}</span>
                        @endif
                    </button>
                @endforeach
            </div>

            <p class="js-growth-price mkt-page-growth-price">{{ cebinova_inr($defaultPlan['price']) }}</p>
            <p class="js-growth-period mkt-page-growth-period">{{ $defaultPlan['period'] }}</p>
            <p class="js-growth-stack mkt-page-growth-stack">{{ \App\Support\MarketingPackages::priceHeadline($growth['service'], 'Yearly') }}</p>
            <p class="js-growth-benefit mkt-page-growth-benefit">{{ $growth['plans']['yearly']['note'] }}</p>

            <div class="mkt-page-growth-actions">
                <x-button href="{{ package_enquiry_url($growth['service'], 'Yearly') }}" size="lg" class="js-growth-cta w-full sm:w-auto">Get This Plan</x-button>
                <x-button href="{{ package_whatsapp_url($growth['service'], 'Yearly') }}" variant="light" size="lg" class="js-growth-whatsapp w-full sm:w-auto">Ask on WhatsApp</x-button>
            </div>
        </div>

        <div class="mkt-page-growth-details">
            <p class="mkt-page-plan-section-label mkt-page-plan-section-label--light">What you get every month on Yearly</p>
            <ul class="mkt-page-plan-list mkt-page-plan-list--light">
                @foreach ($growth['plans']['yearly']['monthly_pace'] as $item)
                    <li>{{ $item }}</li>
                @endforeach
            </ul>
            <p class="mkt-page-plan-section-label mkt-page-plan-section-label--light-muted">Included in every Complete Growth plan</p>
            <ul class="mkt-page-plan-list mkt-page-plan-list--light">
                @foreach ($growth['includes'] as $item)
                    <li>{{ $item }}</li>
                @endforeach
            </ul>
            <div class="mkt-page-plan-notes mkt-page-plan-notes--light">
                <p>{{ config('cebinova.marketing.notes.tax') }}</p>
                <p>{{ config('cebinova.marketing.notes.timeline') }}</p>
                <p>{{ config('cebinova.marketing.notes.payment') }}</p>
                <p>{{ config('cebinova.marketing.notes.support') }}</p>
                <p>{{ config('cebinova.marketing.notes.reels') }}</p>
                <p>{{ config('cebinova.marketing.notes.third_party') }}</p>
            </div>
        </div>
    </div>
</article>
