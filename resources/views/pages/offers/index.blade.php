@extends('layouts.app')
@section('title', 'Offers | CEBINOVA Technologies')
@section('description', 'Current and upcoming CEBINOVA offers for website and marketing packages.')
@section('content')
@php
    $tz = config('app.timezone');
    $at = fn (?string $iso) => $iso ? \Illuminate\Support\Carbon::parse($iso)->timezone($tz) : null;
    $count = $live->count();
@endphp

<x-page-hero
    eyebrow="Offers"
    title="Festival savings on websites and marketing."
    :text="$count > 0
        ? ($count === 1 ? '1 offer is running now.' : $count.' offers are running now.').' Discounts are applied automatically to eligible packages - the price you see is the price you get.'
        : 'Seasonal discounts on CEBINOVA website and marketing packages, published here the moment they go live.'"
>
    <div class="flex flex-col gap-3 sm:flex-row sm:flex-wrap">
        <x-button href="{{ route('pricing') }}">See website pricing</x-button>
        <x-button href="{{ route('marketing-packages') }}" variant="outline">Marketing packages</x-button>
    </div>
</x-page-hero>

<section class="section-pad bg-white">
    <div class="container-wide">
        @if ($live->isNotEmpty())
            <div class="offers-head">
                <p class="offers-eyebrow"><span class="offers-live-dot" aria-hidden="true"></span>Running now</p>
                <h2>{{ $count === 1 ? 'Current offer' : 'Current offers' }}</h2>
            </div>

            <div class="offers-grid {{ $count === 1 ? 'is-single' : '' }}">
                @foreach ($live as $offer)
                    @php
                        $ends = $at($offer['ends_at']);
                        $daysLeft = $ends ? (int) ceil(now()->diffInHours($ends, false) / 24) : null;
                        $theme = in_array($offer['theme'], ['garba', 'crackers'], true) ? $offer['theme'] : 'plain';
                    @endphp
                    <article class="offer-tile is-{{ $theme }}">
                        <div class="offer-tile-band">
                            @if ($theme === 'crackers')
                                <span class="offer-sparks" aria-hidden="true"><i></i><i></i><i></i><i></i><i></i><i></i></span>
                            @endif
                            <div class="offer-tile-top">
                                <p class="offer-tile-kicker">{{ $offer['festival_name'] ?: 'Special offer' }}</p>
                                @if ($daysLeft !== null && $daysLeft <= 7)
                                    <span class="offer-tile-flag">{{ $daysLeft <= 1 ? 'Last day' : 'Ends in '.$daysLeft.' days' }}</span>
                                @endif
                            </div>
                            <p class="offer-tile-off">
                                <b>{{ $offer['discount_label'] }}</b>
                                <span>off</span>
                                @if ($theme === 'garba')
                                    <span class="promo-dandiya is-mini" aria-hidden="true" style="--d:0s"><i class="is-left"></i><i class="is-right"></i><b></b></span>
                                @endif
                            </p>
                        </div>

                        <div class="offer-tile-body">
                            <h3>{{ $offer['headline'] }}</h3>
                            @if ($offer['subheadline'])
                                <p class="offer-tile-sub">{{ $offer['subheadline'] }}</p>
                            @endif

                            @if ($offer['covers'] !== [])
                                <ul class="offer-tile-covers" aria-label="Applies to">
                                    @foreach ($offer['covers'] as $cover)
                                        <li><x-mark name="check" class="h-3.5 w-3.5" />{{ $cover }}</li>
                                    @endforeach
                                </ul>
                            @endif

                            <div class="offer-tile-meta">
                                <div>
                                    <p class="offer-tile-label">Offer ends in</p>
                                    @if ($offer['show_countdown'])
                                        <div class="promo-timer offer-tile-timer" data-promo-countdown data-promo-until="{{ $offer['ends_at'] }}" aria-live="off">
                                            <span><b data-d>00</b><small>Days</small></span>
                                            <span><b data-h>00</b><small>Hrs</small></span>
                                            <span><b data-m>00</b><small>Min</small></span>
                                            <span><b data-s>00</b><small>Sec</small></span>
                                        </div>
                                    @endif
                                    <p class="offer-tile-date">Until {{ $ends?->format('j M Y, g:i A') }}</p>
                                </div>
                                <div>
                                    <p class="offer-tile-label">{{ $offer['coupon_required'] ? 'Code required' : 'Coupon code' }}</p>
                                    @if ($offer['coupon_code'])
                                        <button type="button" class="offer-tile-code" data-copy-code="{{ $offer['coupon_code'] }}">
                                            <span>{{ $offer['coupon_code'] }}</span>
                                            <small data-copy-label>Copy</small>
                                        </button>
                                    @endif
                                    <p class="offer-tile-date">{{ $offer['coupon_required'] ? 'Mention this code when you enquire.' : 'Not needed - the discount applies automatically.' }}</p>
                                </div>
                            </div>

                            <div class="offer-tile-actions">
                                <x-button href="{{ route('offers.show', $offer['slug']) }}">View offer prices</x-button>
                                <x-button href="{{ in_array('Website packages', $offer['covers'], true) || $offer['covers'] === [] ? route('pricing') : route('marketing-packages') }}" variant="outline">Choose a package</x-button>
                            </div>
                        </div>
                    </article>
                @endforeach
            </div>
        @else
            <div class="offers-empty">
                <span class="offers-empty-icon" aria-hidden="true"><x-mark name="spark" class="h-6 w-6" /></span>
                <h2>There is no active offer right now.</h2>
                <p>Package prices on the pricing pages are the regular prices. {{ $upcoming->isNotEmpty() ? 'The next offer is listed below.' : 'Message us on WhatsApp and we will tell you when the next festival offer opens.' }}</p>
                <div class="offers-empty-actions">
                    <x-button href="{{ route('pricing') }}">See website pricing</x-button>
                    <x-button href="{{ whatsapp_url() }}" variant="outline">Ask on WhatsApp</x-button>
                </div>
            </div>
        @endif

        @if ($upcoming->isNotEmpty())
            <div class="offers-head offers-head--next">
                <p class="offers-eyebrow">Coming soon</p>
                <h2>Next {{ $upcoming->count() === 1 ? 'offer' : 'offers' }}</h2>
            </div>
            <div class="offers-next">
                @foreach ($upcoming as $offer)
                    @php $starts = $at($offer['starts_at']); @endphp
                    <a href="{{ route('offers.show', $offer['slug']) }}" class="offer-next">
                        <span class="offer-next-off"><b>{{ $offer['discount_label'] }}</b><small>off</small></span>
                        <span class="offer-next-copy">
                            <span class="offer-next-kicker">{{ $offer['festival_name'] ?: 'Special offer' }} · Starts {{ $starts?->format('j M') }}</span>
                            <strong>{{ $offer['headline'] }}</strong>
                            <span class="offer-next-count">Starts in <span class="promo-countdown" data-compact="1" data-promo-countdown data-promo-until="{{ $offer['starts_at'] }}">{{ $starts?->diffForHumans(null, true) }}</span></span>
                        </span>
                        <x-mark name="arrow" class="offer-next-arrow h-5 w-5" />
                    </a>
                @endforeach
            </div>
        @endif
    </div>
</section>

<section class="section-pad offers-how">
    <div class="container-wide">
        <div class="offers-head">
            <p class="offers-eyebrow">How it works</p>
            <h2>Claim an offer in three steps</h2>
        </div>
        <ol class="offers-steps">
            <li>
                <span class="offers-step-no">01</span>
                <h3>Pick a package</h3>
                <p>Choose a website package or a marketing plan on the pricing pages.</p>
            </li>
            <li>
                <span class="offers-step-no">02</span>
                <h3>See the offer price</h3>
                <p>Eligible packages already show the discounted price. Add a coupon code if the offer asks for one.</p>
            </li>
            <li>
                <span class="offers-step-no">03</span>
                <h3>Send your enquiry</h3>
                <p>Our team contacts you with the offer price for your package before the offer ends.</p>
            </li>
        </ol>
        <p class="offers-fine">One offer per package. When more than one offer applies, the bigger discount is used automatically. Offers apply only while they are running.</p>
    </div>
</section>

<section class="section-pad bg-white">
    <div class="container-wide">
        <div class="offers-cta">
            <div>
                <h2>Not sure which package fits?</h2>
                <p>Tell us how your business works and we will suggest the right package - with the offer price included.</p>
            </div>
            <div class="offers-cta-actions">
                <x-button href="{{ route('contact') }}" variant="light">Get free consultation</x-button>
                <x-button href="{{ whatsapp_url() }}" variant="whatsapp">
                    <x-mark name="whatsapp" class="h-4 w-4 text-[#25D366]" />
                    Ask on WhatsApp
                </x-button>
            </div>
        </div>
    </div>
</section>
@endsection

@push('scripts')
<style>
.offers-head{margin-bottom:1.6rem}
.offers-head h2{margin:.45rem 0 0;color:#013a9d;font-family:"Plus Jakarta Sans",Outfit,sans-serif;font-size:clamp(1.6rem,3vw,2.2rem);font-weight:800;line-height:1.15}
.offers-head--next{margin-top:4rem}
.offers-eyebrow{display:inline-flex;align-items:center;gap:.5rem;margin:0;color:#c48e08;font-size:.72rem;font-weight:800;letter-spacing:.16em;text-transform:uppercase}
.offers-live-dot{width:.55rem;height:.55rem;border-radius:50%;background:#16a34a;box-shadow:0 0 0 0 rgba(22,163,74,.5);animation:offers-live 1.8s ease-out infinite}
@keyframes offers-live{0%{box-shadow:0 0 0 0 rgba(22,163,74,.5)}70%,100%{box-shadow:0 0 0 .55rem rgba(22,163,74,0)}}
.offers-grid{display:grid;gap:1.5rem}
.offer-tile{display:flex;flex-direction:column;min-width:0;overflow:hidden;border:1px solid #e2e8f0;background:#fff;box-shadow:0 18px 44px rgba(1,58,157,.08);transition:transform .4s cubic-bezier(.22,1,.36,1),box-shadow .4s ease;animation:offer-rise .55s cubic-bezier(.22,1,.36,1) backwards}
.offer-tile:nth-child(2){animation-delay:.08s}
.offer-tile:hover{transform:translateY(-4px);box-shadow:0 26px 60px rgba(1,58,157,.14)}
.offer-tile-band{position:relative;overflow:hidden;padding:1.4rem 1.5rem 1.5rem;background:linear-gradient(135deg,#013a9d,#010b24);color:#fff}
.offer-tile-top{position:relative;z-index:1;display:flex;flex-wrap:wrap;align-items:center;justify-content:space-between;gap:.6rem}
.offer-tile-kicker{margin:0;color:#fbb50b;font-size:.72rem;font-weight:800;letter-spacing:.16em;text-transform:uppercase}
.offer-tile-flag{padding:.25rem .6rem;background:#fff;color:#b91c1c;font-size:.68rem;font-weight:800;letter-spacing:.06em;text-transform:uppercase}
.offer-tile-off{position:relative;z-index:1;display:flex;align-items:baseline;gap:.5rem;margin:.9rem 0 0}
.offer-tile-off b{font-family:"Plus Jakarta Sans",Outfit,sans-serif;font-size:clamp(3.4rem,7vw,4.6rem);font-weight:800;line-height:.85;letter-spacing:-.04em;color:#fbb50b}
.offer-tile-off>span{color:#fff;font-size:.9rem;font-weight:800;letter-spacing:.16em;text-transform:uppercase}
.offer-tile-off .promo-dandiya.is-mini{--s:1.35;position:absolute;right:0;bottom:0;margin:0}
.offer-tile-off .promo-dandiya.is-mini b{border-color:#fff4c2}
.offer-tile.is-garba .offer-tile-band{background:radial-gradient(circle,rgba(255,255,255,.18) 0 2px,transparent 2.5px) 0 0/22px 22px,linear-gradient(120deg,#9c27b0,#e91e63 48%,#ff9800)}
.offer-tile.is-garba .offer-tile-band::before{content:"";position:absolute;inset:0 0 auto;height:5px;background:linear-gradient(90deg,#e91e63,#ff9800,#ffc107,#4caf50,#00bcd4,#9c27b0,#e91e63)}
.offer-tile.is-garba .offer-tile-kicker{color:#fff4c2}
.offer-tile.is-garba .offer-tile-off b{color:#fff}
.offer-tile.is-crackers .offer-tile-band{background:radial-gradient(ellipse 60% 80% at 100% 0%,rgba(251,181,11,.35),transparent 60%),linear-gradient(135deg,#0b2c5f,#010b24)}
.offer-sparks i{position:absolute;width:.4rem;height:.4rem;border-radius:50%;background:#fbb50b;box-shadow:0 0 10px 2px rgba(251,181,11,.7);animation:offers-twinkle 2.4s ease-in-out infinite}
.offer-sparks i:nth-child(1){top:18%;right:12%}
.offer-sparks i:nth-child(2){top:52%;right:24%;animation-delay:.4s;background:#ff8a1e}
.offer-sparks i:nth-child(3){top:30%;right:38%;animation-delay:.9s;background:#fff4c2}
.offer-sparks i:nth-child(4){top:72%;right:8%;animation-delay:1.3s}
.offer-sparks i:nth-child(5){top:12%;right:52%;animation-delay:1.7s;background:#ff5a36}
.offer-sparks i:nth-child(6){top:64%;right:46%;animation-delay:2s;background:#fff}
@keyframes offers-twinkle{0%,100%{opacity:.15;transform:scale(.5)}50%{opacity:1;transform:scale(1.4)}}
.offer-tile-body{display:flex;flex:1;flex-direction:column;padding:1.4rem 1.5rem 1.5rem}
.offer-tile-body h3{margin:0;color:#013a9d;font-family:"Plus Jakarta Sans",Outfit,sans-serif;font-size:1.45rem;font-weight:800;line-height:1.2}
.offer-tile-sub{margin:.45rem 0 0;color:#5b6573;line-height:1.5}
.offer-tile-covers{display:flex;flex-wrap:wrap;gap:.45rem;margin:1rem 0 0;padding:0;list-style:none}
.offer-tile-covers li{display:inline-flex;align-items:center;gap:.35rem;padding:.3rem .65rem;border:1px solid rgba(1,58,157,.14);background:#f4f7fb;color:#013a9d;font-size:.8rem;font-weight:700}
.offer-tile-covers svg{color:#16a34a}
.offer-tile-meta{display:grid;gap:1.1rem;margin-top:1.3rem;padding-top:1.2rem;border-top:1px dashed #d5deea}
.offer-tile-label{margin:0 0 .5rem;color:#5c6168;font-size:.66rem;font-weight:800;letter-spacing:.14em;text-transform:uppercase}
.offer-tile-timer b{font-size:1.3rem}
.offer-tile-timer span{padding:.55rem .1rem .45rem}
.offer-tile-date{margin:.5rem 0 0;color:#5c6168;font-size:.8rem;line-height:1.4}
.offer-tile-code{display:flex;align-items:center;justify-content:space-between;gap:.75rem;width:100%;padding:.7rem .9rem;border:1px dashed rgba(196,142,8,.75);background:rgba(251,181,11,.12);color:#010b24;cursor:pointer;transition:background-color .25s ease,border-color .25s ease,transform .25s ease}
.offer-tile-code span{font-family:"Plus Jakarta Sans",Outfit,sans-serif;font-size:1.15rem;font-weight:800;letter-spacing:.06em}
.offer-tile-code small{color:#013a9d;font-size:.66rem;font-weight:800;letter-spacing:.12em;text-transform:uppercase}
.offer-tile-code:hover{border-style:solid;border-color:#fbb50b;background:#fbb50b;transform:translateY(-2px)}
.offer-tile-actions{display:flex;flex-wrap:wrap;gap:.65rem;margin-top:auto;padding-top:1.4rem}
.offers-empty{display:grid;justify-items:center;max-width:40rem;margin:0 auto;padding:2.5rem 1.5rem;border:1px dashed #d5deea;background:#f7faff;text-align:center}
.offers-empty-icon{display:grid;place-items:center;width:3.2rem;height:3.2rem;background:#013a9d;color:#fbb50b}
.offers-empty h2{margin:1rem 0 0;color:#013a9d;font-size:1.5rem;font-weight:800}
.offers-empty p{margin:.6rem 0 0;color:#5b6573;line-height:1.55}
.offers-empty-actions{display:flex;flex-wrap:wrap;justify-content:center;gap:.65rem;margin-top:1.4rem}
.offers-next{display:grid;gap:1rem}
.offer-next{display:flex;align-items:center;gap:1rem;padding:1rem 1.15rem;border:1px solid #e2e8f0;border-left:3px solid #fbb50b;background:#fff;color:inherit;text-decoration:none;transition:transform .3s ease,box-shadow .3s ease,border-color .3s ease}
.offer-next:hover{transform:translateY(-3px);border-color:rgba(1,58,157,.25);border-left-color:#fbb50b;box-shadow:0 16px 36px rgba(1,58,157,.1)}
.offer-next-off{display:grid;flex-shrink:0;place-items:center;width:4.4rem;height:4.4rem;background:#013a9d;color:#fbb50b;line-height:1}
.offer-next-off b{font-size:1.35rem;font-weight:800}
.offer-next-off small{margin-top:-.6rem;color:#fff;font-size:.6rem;font-weight:800;letter-spacing:.14em;text-transform:uppercase}
.offer-next-copy{display:grid;flex:1;gap:.2rem;min-width:0}
.offer-next-kicker{color:#c48e08;font-size:.68rem;font-weight:800;letter-spacing:.14em;text-transform:uppercase}
.offer-next-copy strong{color:#013a9d;font-size:1.15rem;font-weight:800}
.offer-next-count{color:#5c6168;font-size:.85rem}
.offer-next-count .promo-countdown{color:#013a9d;font-weight:700}
.offer-next-arrow{flex-shrink:0;color:#013a9d;transition:transform .3s ease}
.offer-next:hover .offer-next-arrow{transform:translateX(4px)}
.offers-how{background:#f4f7fb}
.offers-steps{display:grid;gap:1rem;margin:0;padding:0;list-style:none;counter-reset:none}
.offers-steps li{position:relative;padding:1.4rem 1.4rem 1.5rem;border:1px solid #e2e8f0;background:#fff}
.offers-step-no{display:inline-grid;place-items:center;width:2.4rem;height:2.4rem;background:#fbb50b;color:#010b24;font-weight:800}
.offers-steps h3{margin:1rem 0 0;color:#013a9d;font-size:1.15rem;font-weight:800}
.offers-steps p{margin:.45rem 0 0;color:#5b6573;line-height:1.55}
.offers-fine{margin:1.4rem 0 0;color:#5c6168;font-size:.85rem;line-height:1.55}
.offers-cta{display:grid;gap:1.4rem;align-items:center;padding:2rem 1.6rem;background:radial-gradient(ellipse 50% 90% at 100% 0%,rgba(251,181,11,.22),transparent 60%),linear-gradient(135deg,#013a9d,#010b24);color:#fff}
.offers-cta h2{margin:0;font-family:"Plus Jakarta Sans",Outfit,sans-serif;font-size:clamp(1.5rem,3vw,2rem);font-weight:800}
.offers-cta p{margin:.5rem 0 0;max-width:36rem;color:#d5deea;line-height:1.55}
.offers-cta-actions{display:flex;flex-wrap:wrap;gap:.65rem}
@keyframes offer-rise{from{opacity:0;transform:translateY(14px)}to{opacity:1;transform:none}}
@media(min-width:640px){.offer-tile-meta{grid-template-columns:1.25fr 1fr}}
@media(min-width:900px){
.offers-grid{grid-template-columns:repeat(2,minmax(0,1fr))}
.offers-grid.is-single{grid-template-columns:minmax(0,46rem)}
.offers-next{grid-template-columns:repeat(2,minmax(0,1fr))}
.offers-steps{grid-template-columns:repeat(3,minmax(0,1fr))}
.offers-cta{grid-template-columns:1fr auto;padding:2.4rem 2.6rem}
}
@media(prefers-reduced-motion:reduce){
.offer-tile,.offers-live-dot,.offer-sparks i{animation:none}
.offer-tile:hover,.offer-next:hover,.offer-tile-code:hover{transform:none}
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
