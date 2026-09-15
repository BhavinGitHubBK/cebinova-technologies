@php
    $headline = \App\Support\MarketingPackages::priceHeadline('Regular Marketing', 'Yearly');
@endphp
<div class="js-pack-sticky pack-sticky" data-default-name="Regular Marketing · Yearly" data-default-price="{{ $headline }}">
    <div class="pack-sticky-copy">
        <p class="js-pack-sticky-name pack-sticky-name">Regular Marketing · Yearly</p>
        <p class="js-pack-sticky-price pack-sticky-price">{{ $headline }}</p>
    </div>
    <div class="pack-sticky-actions">
        <a class="js-pack-sticky-cta pack-sticky-cta" href="{{ package_enquiry_url('Regular Marketing', 'Yearly') }}">Get This Plan</a>
        <a class="js-pack-sticky-wa pack-sticky-wa" href="{{ package_whatsapp_url('Regular Marketing', 'Yearly') }}" target="_blank" rel="noopener noreferrer">Ask on WhatsApp</a>
    </div>
</div>
