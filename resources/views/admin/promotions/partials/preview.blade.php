@php
    $coupon = $campaign->relationLoaded('coupons')
        ? $campaign->coupons->firstWhere('is_active', true)
        : $campaign->coupons()->where('is_active', true)->first();
    $state = $campaign->state();
    $upcoming = $state === 'scheduled';
    $until = $upcoming ? $campaign->starts_at : $campaign->ends_at;
    $left = $until ? max(0, $until->getTimestamp() - now()->getTimestamp()) : 0;
    $clock = [
        'd' => str_pad((string) intdiv($left, 86400), 2, '0', STR_PAD_LEFT),
        'h' => str_pad((string) intdiv($left % 86400, 3600), 2, '0', STR_PAD_LEFT),
        'm' => str_pad((string) intdiv($left % 3600, 60), 2, '0', STR_PAD_LEFT),
        's' => str_pad((string) ($left % 60), 2, '0', STR_PAD_LEFT),
    ];
    $kicker = $upcoming ? 'Coming soon' : ($campaign->festival_name ?: ($campaign->campaign_type?->label() ?: 'Offer'));
    $headline = $campaign->headline ?: $campaign->name;
    $subheadline = $campaign->subheadline ?: 'Website pricing and marketing packages';
    $note = $upcoming
        ? 'Regular prices stay in place until this offer starts.'
        : 'Applied on website and marketing prices until the timer ends.';
    $offerUrl = $campaign->cta_url ?: ($campaign->slug ? route('offers.show', $campaign->slug) : route('offers.index'));
    $hasSide = $campaign->show_countdown || $coupon;
    $design = \App\Enums\PromotionPopupDesign::tryFrom((string) ($campaign->popup_design?->value ?? $campaign->popup_design))?->value ?? 'split';
    $stateLabel = match ($state) {
        'active' => 'Live',
        'scheduled' => 'Scheduled',
        'expired' => 'Ended',
        'disabled' => 'Turned off',
        default => 'Draft',
    };
@endphp
<div class="offer-preview">
    <header class="offer-preview-head">
        <div>
            <p class="offer-preview-kicker">{{ $campaign->campaign_type?->label() ?: 'Offer' }}</p>
            <h2>{{ $campaign->name }}</h2>
            <p class="offer-preview-meta">
                <span class="promo-pill is-{{ $state }}">{{ $stateLabel }}</span>
                <span>{{ $campaign->discountLabel() }} off</span>
                @if ($campaign->starts_at && $campaign->ends_at)
                    <span>{{ $campaign->starts_at->timezone('Asia/Kolkata')->format('j M Y') }} – {{ $campaign->ends_at->timezone('Asia/Kolkata')->format('j M Y') }}</span>
                @endif
            </p>
        </div>
        <div class="offer-preview-actions">
            @can('admin.promotions.manage')
                <a class="admin-btn" href="{{ route('admin.promotions.campaigns.edit', $campaign) }}">Edit campaign</a>
            @endcan
            <a class="admin-btn" href="{{ $offerUrl }}" target="_blank" rel="noopener">View offer</a>
        </div>
    </header>
    <p class="promo-admin-note">Preview only. Nothing here is published, and no price changes.</p>

    <section class="offer-preview-block">
        <h3>Top bar</h3>
        @if ($campaign->show_announcement_bar)
            <div class="promo-dock" aria-label="Top bar preview">
                <p class="promo-dock-kicker">{{ $upcoming ? 'Offer coming soon' : 'Discount available' }}</p>
                <strong>{{ $headline }} · {{ $campaign->discountLabel() }}{{ $upcoming ? '' : ' off' }}</strong>
                @if ($coupon)
                    <span class="promo-code-chip">Coupon {{ $coupon->code }}</span>
                @endif
                @if ($campaign->show_countdown && $until)
                    <span class="promo-countdown" data-compact="1" data-preview-until="{{ $until->toIso8601String() }}">{{ intdiv($left, 86400) }}d {{ $clock['h'] }}h {{ $clock['m'] }}m {{ $clock['s'] }}s</span>
                @endif
            </div>
        @else
            <p class="offer-preview-off">Top bar is turned off.</p>
        @endif
    </section>

    <section class="offer-preview-block">
        <h3>Price badge</h3>
        @if ($campaign->show_pricing_badge)
            <div class="offer-preview-price">
                <span>Shown beside a package price</span>
                <span class="promo-badge">{{ $campaign->badge_text ?: $campaign->name }} · {{ $campaign->discountLabel() }}</span>
            </div>
        @else
            <p class="offer-preview-off">Price badge is turned off.</p>
        @endif
    </section>

    <section class="offer-preview-block">
        <h3>Popup</h3>
        @if ($campaign->show_popup)
            <div class="offer-preview-stage">
                <div @class(['promo-dialog', 'promo-design-'.$design, 'is-festival' => $campaign->campaign_type?->value === 'festival'])>
                    <div class="promo-dialog-card" role="group" aria-label="Popup preview">
                        <span class="promo-close" aria-hidden="true">
                            <svg viewBox="0 0 24 24" width="16" height="16"><path d="M6 6l12 12M18 6L6 18" fill="none" stroke="currentColor" stroke-width="1.8"/></svg>
                        </span>
                        <div @class(['promo-dialog-body', 'is-single' => ! $hasSide])>
                            <div class="promo-dialog-main">
                                <p class="promo-kicker"><span class="promo-kicker-dot" aria-hidden="true"></span><span>{{ $kicker }}</span></p>
                                <h2>{{ $headline }}</h2>
                                <p class="promo-sub">{{ $subheadline }}</p>
                                <p class="promo-off"><b>{{ $campaign->discountLabel() }}</b><span>{{ $upcoming ? 'starts soon' : 'off' }}</span></p>
                                <p class="promo-note">{{ $note }}</p>
                                <div class="promo-actions">
                                    <a class="promo-cta" href="{{ $offerUrl }}" target="_blank" rel="noopener">{{ $campaign->cta_text ?: 'Explore Offer' }}</a>
                                    <a class="promo-brochure" href="{{ $offerUrl }}" target="_blank" rel="noopener">View offer</a>
                                </div>
                            </div>
                            @if ($hasSide)
                                <div class="promo-dialog-side">
                                    @if ($campaign->show_countdown)
                                        <p class="promo-timer-label">{{ $upcoming ? 'Starts in' : 'Offer ends in' }}</p>
                                        <div class="promo-timer" @if ($until) data-preview-until="{{ $until->toIso8601String() }}" @endif>
                                            <span><b data-d>{{ $clock['d'] }}</b><small>Days</small></span>
                                            <span><b data-h>{{ $clock['h'] }}</b><small>Hours</small></span>
                                            <span><b data-m>{{ $clock['m'] }}</b><small>Minutes</small></span>
                                            <span><b data-s>{{ $clock['s'] }}</b><small>Seconds</small></span>
                                        </div>
                                    @endif
                                    @if ($coupon)
                                        <button type="button" class="promo-ticket" data-copy-code="{{ $coupon->code }}">Use code {{ $coupon->code }}</button>
                                    @endif
                                </div>
                            @endif
                        </div>
                    </div>
                </div>
            </div>
        @else
            <p class="offer-preview-off">Popup is turned off.</p>
        @endif
    </section>
</div>
<style>
.offer-preview-head{display:flex;flex-wrap:wrap;gap:1rem 1.5rem;align-items:flex-end;justify-content:space-between;margin:0 0 1rem}
.offer-preview-kicker{margin:0 0 .35rem;color:#c48e08;font-size:.72rem;font-weight:800;letter-spacing:.14em;text-transform:uppercase}
.offer-preview-head h2{margin:0;color:#013a9d;font-size:1.65rem;font-weight:800;line-height:1.15}
.offer-preview-meta{display:flex;flex-wrap:wrap;gap:.55rem .8rem;align-items:center;margin:.7rem 0 0;color:#5c6168;font-weight:700}
.offer-preview-actions{display:flex;flex-wrap:wrap;gap:.5rem}
.offer-preview-block{margin:0 0 1.25rem}
.offer-preview-block h3{margin:0 0 .65rem;color:#013a9d;font-size:.72rem;font-weight:800;letter-spacing:.12em;text-transform:uppercase}
.offer-preview-off{margin:0;padding:.85rem 1rem;border:1px dashed #d7dee8;background:#fff;color:#5c6168}
.offer-preview .promo-dock{position:relative;inset:auto;top:auto;z-index:1;justify-content:flex-start;overflow:auto}
.offer-preview-price{display:flex;flex-wrap:wrap;gap:.75rem 1rem;align-items:center;padding:1rem 1.1rem;background:#fff;border:1px solid #e2e8f0;color:#5c6168;font-weight:700}
.offer-preview-price .promo-badge{margin:0}
.offer-preview-stage{padding:1.75rem 1rem;background:
  radial-gradient(ellipse 50% 80% at 80% 0%,rgba(251,181,11,.16),transparent 55%),
  #182033}
.offer-preview .promo-dialog{position:relative;inset:auto;z-index:1;display:block;padding:0;background:transparent;animation:none}
.offer-preview .promo-dialog-card{width:min(52rem,100%);max-height:none;margin:0 auto;overflow:visible}
.offer-preview .promo-dialog-body.is-single{grid-template-columns:1fr}
.offer-preview .promo-close{pointer-events:none}
</style>
<script>
document.querySelectorAll("[data-preview-until]").forEach(function (node) {
    var end = Date.parse(node.getAttribute("data-preview-until"));
    if (!end) return;
    function pad(value) { return String(value).padStart(2, "0"); }
    function tick() {
        var total = Math.max(0, Math.floor((end - Date.now()) / 1000));
        var days = Math.floor(total / 86400);
        var hours = Math.floor((total % 86400) / 3600);
        var minutes = Math.floor((total % 3600) / 60);
        var seconds = total % 60;
        if (node.classList.contains("promo-timer")) {
            node.querySelector("[data-d]").textContent = pad(days);
            node.querySelector("[data-h]").textContent = pad(hours);
            node.querySelector("[data-m]").textContent = pad(minutes);
            node.querySelector("[data-s]").textContent = pad(seconds);
        } else {
            node.textContent = days + "d " + pad(hours) + "h " + pad(minutes) + "m " + pad(seconds) + "s";
        }
        if (total > 0) window.setTimeout(tick, 1000);
    }
    tick();
});
document.querySelectorAll("[data-copy-code]").forEach(function (button) {
    button.addEventListener("click", function () {
        var code = button.getAttribute("data-copy-code");
        if (navigator.clipboard) navigator.clipboard.writeText(code);
        button.textContent = "Copied " + code;
    });
});
</script>
