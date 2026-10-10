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
<p class="whitespace-nowrap text-[2rem] font-extrabold leading-none tracking-tight {{ $isLight ? 'text-navy' : 'text-white' }} sm:text-[2.35rem]">
    @if (! empty($plan['promotion']['final']))
        <s class="mr-2 text-[1.05rem] font-semibold opacity-60">{{ cebinova_inr((int) round((float) $plan['price'])) }}</s>{{ cebinova_inr((int) round((float) $plan['promotion']['final'])) }}
    @else
        {{ cebinova_inr($plan['price']) }}
    @endif
</p>
@if (! empty($plan['promotion']['name']))
    <p class="mt-2 text-[13px] font-bold {{ $isLight ? 'text-navy' : 'text-white' }}">{{ $plan['promotion']['name'] }} · Save {{ cebinova_inr((int) round((float) $plan['promotion']['savings'])) }}</p>
@endif
<p class="mt-1.5 text-[15px] font-semibold {{ $isLight ? 'text-navy/55' : 'text-white/60' }}">{{ $plan['period'] }}</p>
@if ($effective && $saved && $monthly && $months > 1)
    <p class="mt-3 text-[15px] font-semibold leading-snug {{ $isLight ? 'text-navy' : 'text-white' }}">
        Just {{ cebinova_inr($effective) }}/month · Save {{ cebinova_inr($saved) }}
    </p>
    <p class="mt-1 text-[13px] leading-snug {{ $isLight ? 'text-navy/50' : 'text-white/50' }}">Monthly plan for {{ $months }} months: {{ cebinova_inr($monthly) }} × {{ $months }} = {{ cebinova_inr($monthly * $months) }}</p>
@elseif ($saved)
    <p class="mt-3 inline-flex rounded-full bg-gold/20 px-3 py-1 text-[13px] font-bold text-navy">Save {{ cebinova_inr($saved) }} vs monthly</p>
@endif
