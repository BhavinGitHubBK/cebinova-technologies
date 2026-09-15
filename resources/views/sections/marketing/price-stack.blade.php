@php
    $service = $service ?? '';
    $plan = $plan ?? [];
    $tone = $tone ?? 'light';
    $saved = \App\Support\MarketingPackages::savingsVsMonthly($service, $plan['label'] ?? null);
    $effective = \App\Support\MarketingPackages::effectiveMonthly($service, $plan['label'] ?? null);
    $monthly = \App\Support\MarketingPackages::priceAmount($service, 'Monthly');
    $months = \App\Support\MarketingPackages::months($plan['label'] ?? null);
    $isLight = $tone === 'light';
@endphp
<p class="whitespace-nowrap text-[2rem] font-extrabold leading-none tracking-tight {{ $isLight ? 'text-navy' : 'text-white' }} sm:text-[2.35rem]">{{ sarvix_inr($plan['price']) }}</p>
<p class="mt-1.5 text-[15px] font-semibold {{ $isLight ? 'text-navy/55' : 'text-white/60' }}">{{ $plan['period'] }}</p>
@if ($effective && $saved && $monthly && $months > 1)
    <p class="mt-3 text-[15px] font-semibold leading-snug {{ $isLight ? 'text-navy' : 'text-white' }}">
        Just {{ sarvix_inr($effective) }}/month · Save {{ sarvix_inr($saved) }}
    </p>
    <p class="mt-1 text-[13px] leading-snug {{ $isLight ? 'text-navy/50' : 'text-white/50' }}">Monthly plan for {{ $months }} months: {{ sarvix_inr($monthly) }} × {{ $months }} = {{ sarvix_inr($monthly * $months) }}</p>
@elseif ($saved)
    <p class="mt-3 inline-flex rounded-full bg-gold/20 px-3 py-1 text-[13px] font-bold text-navy">Save {{ sarvix_inr($saved) }} vs monthly</p>
@endif
