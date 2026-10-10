@extends('layouts.app')
@section('title', ($campaign->seo_title ?: $campaign->name).' | CEBINOVA Technologies')
@section('description', $campaign->seo_description ?: ($campaign->subheadline ?: $campaign->name))
@if ($state === 'expired')
    @section('robots', 'noindex')
@endif
@section('content')
@php
    $offerUrl = route('offers.show', $campaign->slug);
    $ctaUrl = $presentation['cta_url'] ?? $offerUrl;
    $ctaPointsHere = rtrim((string) $ctaUrl, '/') === rtrim($offerUrl, '/');
    $durationOrder = ['Monthly' => 1, 'Quarterly' => 2, 'Half-Yearly' => 3, 'Yearly' => 4];
    $website = collect($packages)->where('kind', 'Website package')->values();
    $marketing = collect($packages)->where('kind', 'Marketing package')->groupBy(function (array $row) {
        return trim(explode(' · ', $row['name'], 2)[0]);
    })->map(function ($items) use ($durationOrder) {
        return $items->map(function (array $row) {
            $parts = explode(' · ', $row['name'], 2);
            $row['duration'] = trim($parts[1] ?? $row['name']);
            return $row;
        })->sortBy(fn (array $row) => $durationOrder[$row['duration']] ?? 99)->values();
    });
    $extras = collect($packages)->reject(fn (array $row) => in_array($row['kind'], ['Website package', 'Marketing package'], true))->values();
@endphp

<x-page-hero
    :eyebrow="$presentation['type_label'] ?? 'Offer'"
    :title="$presentation['headline']"
    :text="$state === 'active' ? ($presentation['subheadline'] ?: null) : null"
>
    <div class="flex flex-col gap-3 sm:flex-row sm:flex-wrap">
        @if ($state === 'expired')
            <x-button href="{{ route('pricing') }}">View current pricing</x-button>
        @elseif (! $ctaPointsHere)
            <x-button href="{{ $ctaUrl }}">{{ $presentation['cta_text'] }}</x-button>
        @else
            <x-button href="{{ route('pricing') }}">Website pricing</x-button>
            <x-button href="{{ route('marketing-packages') }}" variant="outline">Marketing packages</x-button>
        @endif
    </div>
</x-page-hero>

<section class="section-pad bg-white">
    <div class="container-wide">
        @if ($state === 'expired')
            <p class="max-w-2xl text-lg text-muted">This offer has ended. Current prices are on the pricing page.</p>
        @elseif ($state === 'scheduled')
            <p class="max-w-2xl text-lg text-muted">{{ $campaign->name }} starts {{ $campaign->starts_at?->timezone(config('app.timezone'))->format('d M Y, h:i A') }}. The discount is not applied before then.</p>
            <p class="mt-6 text-[12px] font-bold uppercase tracking-[0.14em] text-muted">Starts in</p>
            <div class="promo-timer mt-3 max-w-xl" data-promo-countdown data-promo-until="{{ $campaign->starts_at?->toIso8601String() }}" aria-live="off">
                <span><b data-d>00</b><small>Days</small></span>
                <span><b data-h>00</b><small>Hours</small></span>
                <span><b data-m>00</b><small>Minutes</small></span>
                <span><b data-s>00</b><small>Seconds</small></span>
            </div>
        @else
            <div class="offer-facts">
                <div class="offer-fact">
                    <p class="offer-fact-label"><span class="offer-fact-dot" aria-hidden="true"></span>Discount</p>
                    <p class="offer-fact-value">{{ $presentation['discount_label'] }} <span>off</span></p>
                </div>
                <div class="offer-fact">
                    <p class="offer-fact-label">{{ $presentation['coupon_required'] ? 'Code required' : 'Coupon' }}</p>
                    @if ($presentation['coupon_code'])
                        <button type="button" class="offer-code" data-copy-code="{{ $presentation['coupon_code'] }}">
                            <span>{{ $presentation['coupon_code'] }}</span>
                            <small data-copy-label>Copy</small>
                        </button>
                    @else
                        <p class="offer-fact-plain">Applied automatically</p>
                    @endif
                </div>
                <div class="offer-fact">
                    <p class="offer-fact-label">Ends in</p>
                    @if ($presentation['show_countdown'])
                        <div class="promo-timer offer-timer" data-promo-countdown data-promo-until="{{ $campaign->ends_at?->toIso8601String() }}" aria-live="off">
                            <span><b data-d>00</b><small>Days</small></span>
                            <span><b data-h>00</b><small>Hours</small></span>
                            <span><b data-m>00</b><small>Min</small></span>
                            <span><b data-s>00</b><small>Sec</small></span>
                        </div>
                    @else
                        <p class="offer-fact-plain">{{ $campaign->ends_at?->timezone(config('app.timezone'))->format('d M Y, h:i A') }}</p>
                    @endif
                </div>
            </div>
            @if ($presentation['description'])
                <p class="offer-note text-sm leading-relaxed text-muted">{{ $presentation['description'] }}</p>
            @endif

            @if ($website->isNotEmpty())
                <div class="offer-block">
                    <h2>Website packages</h2>
                    <div class="offer-cards">
                        @foreach ($website as $package)
                            <article class="offer-card flex flex-col border border-line bg-white p-5">
                                <span class="offer-rule"></span>
                                <h3 class="text-lg font-extrabold text-navy">{{ $package['name'] }}</h3>
                                @if ($package['original'])
                                    <p class="mt-auto pt-6 text-sm text-muted line-through">{{ cebinova_inr((int) round((float) $package['original'])) }}</p>
                                    <p class="font-brand text-3xl font-extrabold leading-none text-navy">{{ cebinova_inr((int) round((float) $package['final'])) }}</p>
                                    <p class="mt-2 text-sm font-bold text-gold-dark">Save {{ cebinova_inr((int) round((float) $package['savings'])) }}</p>
                                @endif
                            </article>
                        @endforeach
                    </div>
                </div>
            @endif

            @if ($marketing->isNotEmpty())
                <div class="offer-block">
                    <h2>Marketing packages</h2>
                    <div class="offer-plans">
                        @foreach ($marketing as $name => $plans)
                            <article class="offer-plan border border-line bg-white">
                                <h3 class="border-b border-line px-4 py-3 text-base font-extrabold text-navy">{{ $name }}</h3>
                                <table class="w-full text-left text-sm">
                                    <thead class="text-[11px] font-bold uppercase tracking-[0.08em] text-muted">
                                        <tr>
                                            <th class="px-4 py-2 font-bold">Term</th>
                                            <th class="px-2 py-2 text-right font-bold">Was</th>
                                            <th class="px-4 py-2 text-right font-bold">Now</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @foreach ($plans as $plan)
                                            <tr class="border-t border-line">
                                                <td class="px-4 py-2.5 font-semibold text-navy">{{ $plan['duration'] }}</td>
                                                <td class="px-2 py-2.5 text-right text-muted line-through">{{ $plan['original'] ? cebinova_inr((int) round((float) $plan['original'])) : '—' }}</td>
                                                <td class="px-4 py-2.5 text-right font-extrabold text-navy">{{ $plan['final'] ? cebinova_inr((int) round((float) $plan['final'])) : '—' }}</td>
                                            </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                            </article>
                        @endforeach
                    </div>
                </div>
            @endif

            @if ($extras->isNotEmpty())
                <div class="mt-12">
                    <h2 class="text-[12px] font-bold uppercase tracking-[0.14em] text-gold-dark">Also included</h2>
                    <ul class="mt-4 grid gap-3 sm:grid-cols-2">
                        @foreach ($extras as $package)
                            <li class="border border-line bg-white px-4 py-3 font-semibold text-navy">{{ $package['name'] }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

            @if ($campaign->terms)
                <p class="mt-10 max-w-3xl text-sm leading-relaxed text-muted">{{ $campaign->terms }}</p>
            @endif

        @endif
    </div>
</section>
@endsection

@push('scripts')
<style>
.offer-facts{display:grid;grid-template-columns:1fr;border:1px solid #e2e8f0;border-top:3px solid #fbb50b;background:radial-gradient(ellipse 42% 90% at 0% 0%,rgba(251,181,11,.14),transparent 62%),#fff}
.offer-fact{padding:1.35rem 1.4rem 1.4rem}
.offer-fact+.offer-fact{border-top:1px solid #e2e8f0}
.offer-fact-label{display:inline-flex;align-items:center;gap:.45rem;margin:0;color:#c48e08;font-size:.68rem;font-weight:800;letter-spacing:.14em;line-height:1.3;text-transform:uppercase}
.offer-fact-dot{width:.42rem;height:.42rem;border-radius:999px;background:radial-gradient(circle at 30% 30%,#ffe08a,#fbb50b 55%,#c48e08);box-shadow:0 0 0 4px rgba(251,181,11,.16)}
.offer-fact-value{margin:.75rem 0 0;color:#013a9d;font-family:"Plus Jakarta Sans",Outfit,sans-serif;font-size:3rem;font-weight:800;line-height:.9;letter-spacing:-.03em}
.offer-fact-value span{margin-left:.15rem;color:#c48e08;font-size:.78rem;letter-spacing:.14em;text-transform:uppercase}
.offer-fact-plain{margin:.85rem 0 0;color:#013a9d;font-weight:700}
.offer-code{display:flex;align-items:center;justify-content:space-between;gap:.75rem;width:100%;margin-top:.8rem;padding:.8rem .95rem;border:1px dashed rgba(196,142,8,.75);background:rgba(251,181,11,.12);color:#010b24;cursor:pointer;transition:background-color .25s ease,border-color .25s ease,transform .25s ease}
.offer-code span{font-family:"Plus Jakarta Sans",Outfit,sans-serif;font-size:1.35rem;font-weight:800;letter-spacing:.06em}
.offer-code small{color:#013a9d;font-size:.68rem;font-weight:800;letter-spacing:.12em;text-transform:uppercase}
.offer-code:hover{border-style:solid;border-color:#fbb50b;background:#fbb50b;transform:translateY(-2px)}
.offer-facts .offer-timer{margin-top:.8rem}
.offer-facts .offer-timer span{border-color:transparent;background:#f4f7fb;transition:background-color .25s ease,transform .25s ease}
.offer-facts .offer-timer b{font-size:1.45rem}
.offer-facts .offer-timer span:hover{background:#fff;transform:translateY(-2px);box-shadow:0 8px 18px rgba(1,58,157,.08)}
.offer-note{margin:1.5rem 0 0;max-width:46rem}
.offer-block{margin-top:3.5rem}
.offer-block>h2{margin:0 0 1.25rem;color:#c48e08;font-size:.75rem;font-weight:800;letter-spacing:.14em;line-height:1.4;text-transform:uppercase}
.offer-cards,.offer-plans{display:grid;gap:1rem}
.offer-card,.offer-plan{min-width:0;transition:transform .4s cubic-bezier(.22,1,.36,1),box-shadow .4s cubic-bezier(.22,1,.36,1),border-color .4s ease;animation:offer-rise .55s cubic-bezier(.22,1,.36,1) backwards}
.offer-card:nth-child(2),.offer-plan:nth-child(2){animation-delay:.08s}
.offer-card:nth-child(3),.offer-plan:nth-child(3){animation-delay:.16s}
.offer-card:hover,.offer-plan:hover{transform:translateY(-4px);border-color:rgba(1,58,157,.28);box-shadow:0 18px 44px rgba(1,58,157,.12)}
.offer-rule{display:block;width:2.5rem;height:3px;margin-bottom:1rem;background:#fbb50b;transition:width .4s cubic-bezier(.22,1,.36,1)}
.offer-card:hover .offer-rule{width:100%}
.offer-plan tr{transition:background-color .25s ease}
.offer-plan tbody tr:hover{background:rgba(251,181,11,.1)}
@keyframes offer-rise{from{opacity:0;transform:translateY(14px)}to{opacity:1;transform:none}}
@media(min-width:800px){
.offer-facts{grid-template-columns:repeat(3,minmax(0,1fr))}
.offer-fact+.offer-fact{border-top:0;border-left:1px solid #e2e8f0}
.offer-cards{grid-template-columns:repeat(3,minmax(0,1fr))}
}
@media(min-width:1024px){.offer-plans{grid-template-columns:repeat(3,minmax(0,1fr))}}
@media(prefers-reduced-motion:reduce){
.offer-card,.offer-plan{animation:none}
.offer-card:hover,.offer-plan:hover{transform:none}
}
</style>
<script>
document.querySelectorAll('[data-copy-code]').forEach(function (button) {
    button.addEventListener('click', function () {
        var code = button.getAttribute('data-copy-code');
        if (navigator.clipboard) navigator.clipboard.writeText(code);
        var label = button.querySelector('[data-copy-label]');
        if (label) label.textContent = 'Copied';
    });
});
</script>
@endpush
