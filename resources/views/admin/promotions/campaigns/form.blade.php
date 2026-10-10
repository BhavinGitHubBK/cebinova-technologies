@extends('layouts.admin')
@section('title', $campaign->exists ? 'Edit campaign' : 'New campaign')
@section('heading', $campaign->exists ? $campaign->name : 'New campaign')
@section('breadcrumb', 'Admin / Promotions / Campaign')
@section('content')
@include('admin.promotions.partials.nav')
@php
    $flag = fn (string $name, bool $default = false) => old($name, $campaign->exists ? $campaign->{$name} : $default);
    $has = function (string $type, string $mode = 'include', ?int $id = null, ?string $key = null) use ($campaign) {
        if (! $campaign->exists) {
            return false;
        }
        return $campaign->targets->contains(function ($target) use ($type, $mode, $id, $key) {
            return $target->mode === $mode
                && $target->target_type?->value === $type
                && ($id === null || (int) $target->target_id === $id)
                && ($key === null || $target->target_key === $key);
        });
    };
    $when = fn ($value) => $value ? $value->timezone(config('app.timezone'))->format('Y-m-d\TH:i') : '';
@endphp
@if ($campaign->exists)
    <div class="campaign-status">
        <span class="promo-pill is-{{ $campaign->state() }}">{{ ucfirst($campaign->state()) }}</span>
        @php
            $offerAmount = rtrim(rtrim(number_format((float) $campaign->discount_value, 2, '.', ''), '0'), '.');
            $offerLabel = $campaign->discount_type?->value === 'percentage' ? $offerAmount.'% off' : '₹'.$offerAmount.' off';
        @endphp
        <p>{{ $offerLabel }} · {{ $campaign->starts_at?->timezone(config('app.timezone'))->format('d M Y, h:i A') }} to {{ $campaign->ends_at?->timezone(config('app.timezone'))->format('d M Y, h:i A') }}</p>
    </div>
    <div class="promo-admin-actions">
        <a class="admin-btn" href="{{ route('admin.promotions.campaigns.preview', $campaign) }}">Preview</a>
        @can('admin.promotions.manage')
            <form method="POST" action="{{ route('admin.promotions.campaigns.duplicate', $campaign) }}" data-skip-ajax="1">@csrf<button class="admin-btn" type="submit">Duplicate as draft</button></form>
            <form method="POST" action="{{ route('admin.promotions.campaigns.destroy', $campaign) }}" data-skip-ajax="1">@csrf @method('DELETE')<button class="admin-btn admin-btn-danger" type="submit" data-confirm="Delete this campaign? It leaves the site and prices go back to normal.">Delete</button></form>
        @endcan
        @can('admin.promotions.publish')
            <form method="POST" action="{{ route('admin.promotions.campaigns.publish', $campaign) }}" data-skip-ajax="1">@csrf<button class="admin-btn admin-btn-accent" type="submit">Publish</button></form>
            <form method="POST" action="{{ route('admin.promotions.campaigns.disable', $campaign) }}" data-skip-ajax="1">@csrf<button class="admin-btn" type="submit">Disable</button></form>
        @endcan
    </div>
@endif
<form class="admin-card admin-form campaign-form" method="POST" action="{{ $campaign->exists ? route('admin.promotions.campaigns.update', $campaign) : route('admin.promotions.campaigns.store') }}">
    @csrf
    @if ($campaign->exists) @method('PUT') @endif

    <section class="form-block">
        <h2>The offer</h2>
        <p class="form-hint">Name it, set the discount, and choose which packages it covers.</p>
        <div class="form-grid">
            <div class="admin-field">
                <label class="admin-label">Campaign name</label>
                <input class="admin-input" name="name" value="{{ old('name', $campaign->name) }}" required>
            </div>
            <div class="admin-field">
                <label class="admin-label">Headline visitors see</label>
                <input class="admin-input" name="headline" value="{{ old('headline', $campaign->headline) }}" placeholder="Diwali Special">
                <p class="form-hint">Used in the popup and the top bar. The campaign name is used if this is empty.</p>
            </div>
            <div class="admin-field is-wide">
                <label class="admin-label">Offer page text</label>
                <textarea class="admin-input" name="description" rows="2">{{ old('description', $campaign->description) }}</textarea>
            </div>
            <div class="admin-field">
                <label class="admin-label">Discount type</label>
                <select class="admin-select" name="discount_type">
                    @foreach ($discounts as $value => $label)
                        <option value="{{ $value }}" @selected(old('discount_type', $campaign->discount_type?->value ?: 'percentage') === $value)>{{ $label }}</option>
                    @endforeach
                </select>
            </div>
            <div class="admin-field">
                <label class="admin-label">Discount value</label>
                <input class="admin-input" type="number" step="0.01" min="0.01" name="discount_value" value="{{ old('discount_value', $campaign->discount_value) }}" required>
            </div>
        </div>
        <p class="admin-label">Applies to</p>
        <div class="campaign-picks">
            <label class="campaign-pick">
                <input type="checkbox" name="include_all_website" value="1" @checked(old('include_all_website', $has('all_website_packages')))>
                <span><strong>Website packages</strong><small>Every website plan</small></span>
            </label>
            <label class="campaign-pick">
                <input type="checkbox" name="include_all_marketing" value="1" @checked(old('include_all_marketing', $has('all_marketing_packages')))>
                <span><strong>Marketing packages</strong><small>Every marketing plan</small></span>
            </label>
        </div>
    </section>

    <section class="form-block">
        <h2>When it runs</h2>
        <p class="form-hint">Times are IST. A published campaign only discounts between these times.</p>
        <div class="form-grid">
            <div class="admin-field">
                <label class="admin-label">Starts</label>
                <input class="admin-input" type="datetime-local" name="starts_at" value="{{ old('starts_at', $when($campaign->starts_at)) }}" required>
            </div>
            <div class="admin-field">
                <label class="admin-label">Ends</label>
                <input class="admin-input" type="datetime-local" name="ends_at" value="{{ old('ends_at', $when($campaign->ends_at)) }}" required>
            </div>
            <div class="admin-field">
                <label class="admin-label">Status</label>
                <select class="admin-select" name="publication_status">
                    @foreach (['draft' => 'Draft', 'published' => 'Published', 'disabled' => 'Disabled'] as $value => $label)
                        <option value="{{ $value }}" @selected(old('publication_status', $campaign->publication_status?->value ?: 'draft') === $value)>{{ $label }}</option>
                    @endforeach
                </select>
            </div>
        </div>
    </section>

    <section class="form-block">
        <h2>What visitors see</h2>
        <p class="form-hint">Turn on the places this offer should appear.</p>
        <div class="campaign-picks">
            <label class="campaign-pick"><input type="checkbox" name="show_popup" value="1" @checked($flag('show_popup'))><span><strong>Popup</strong><small>Opens on the site</small></span></label>
            <label class="campaign-pick"><input type="checkbox" name="show_announcement_bar" value="1" @checked($flag('show_announcement_bar'))><span><strong>Top bar</strong><small>Under the browser edge</small></span></label>
            <label class="campaign-pick"><input type="checkbox" name="show_pricing_badge" value="1" @checked($flag('show_pricing_badge', true))><span><strong>Price badge</strong><small>On package prices</small></span></label>
            <label class="campaign-pick"><input type="checkbox" name="show_countdown" value="1" @checked($flag('show_countdown'))><span><strong>Countdown</strong><small>Time left on the offer</small></span></label>
            <label class="campaign-pick"><input type="checkbox" name="coupon_required" value="1" @checked($flag('coupon_required'))><span><strong>Coupon required</strong><small>Discount only with a code</small></span></label>
        </div>
        <p class="admin-label">Popup design</p>
        <div class="design-list">
            @foreach ($designs as $value => $meta)
                <label class="design-card">
                    <input type="radio" name="popup_design" value="{{ $value }}" @checked(old('popup_design', $campaign->popup_design?->value ?: 'split') === $value)>
                    <span class="design-thumb design-thumb-{{ $value }}" aria-hidden="true"><i></i><i></i><i></i></span>
                    <span><strong>{{ $meta['label'] }}</strong><small>{{ $meta['detail'] }}</small></span>
                </label>
            @endforeach
        </div>
        <div class="form-grid">
            <div class="admin-field">
                <label class="admin-label">Show the popup again after (minutes)</label>
                <input class="admin-input" type="number" min="0" max="1440" name="popup_repeat_minutes" value="{{ old('popup_repeat_minutes', $campaign->popup_repeat_minutes ?? 1) }}">
                <p class="form-hint">1 shows it again every minute after they close it. 0 shows it once per visit.</p>
            </div>
            <div class="admin-field">
                <label class="admin-label">Button label</label>
                <input class="admin-input" name="cta_text" value="{{ old('cta_text', $campaign->cta_text) }}" placeholder="Explore Offer">
            </div>
        </div>
    </section>

    <section class="form-block">
        <h2>Extra wording</h2>
        <div class="form-grid">
            <div class="admin-field"><label class="admin-label">Slug</label><input class="admin-input" name="slug" value="{{ old('slug', $campaign->slug) }}"><p class="form-hint">Filled from the name if empty.</p></div>
            <div class="admin-field"><label class="admin-label">Type</label><select class="admin-select" name="campaign_type">@foreach ($types as $value => $label)<option value="{{ $value }}" @selected(old('campaign_type', $campaign->campaign_type?->value ?: 'festival') === $value)>{{ $label }}</option>@endforeach</select></div>
            <div class="admin-field"><label class="admin-label">Occasion</label><input class="admin-input" name="festival_name" value="{{ old('festival_name', $campaign->festival_name) }}" placeholder="Diwali"><p class="form-hint">Optional. Use for a festival or any other offer.</p></div>
            <div class="admin-field"><label class="admin-label">Subheadline</label><input class="admin-input" name="subheadline" value="{{ old('subheadline', $campaign->subheadline) }}"></div>
            <div class="admin-field"><label class="admin-label">Announcement text</label><input class="admin-input" name="announcement_text" value="{{ old('announcement_text', $campaign->announcement_text) }}"></div>
            <div class="admin-field"><label class="admin-label">Badge text</label><input class="admin-input" name="badge_text" value="{{ old('badge_text', $campaign->badge_text) }}"></div>
            <div class="admin-field"><label class="admin-label">Button link</label><input class="admin-input" name="cta_url" value="{{ old('cta_url', $campaign->cta_url) }}"><p class="form-hint">Empty uses the offer page.</p></div>
            <div class="admin-field is-wide"><label class="admin-label">Terms</label><textarea class="admin-input" name="terms" rows="3">{{ old('terms', $campaign->terms) }}</textarea></div>
        </div>
    </section>

    <section class="form-block">
        <h2>Popup timing</h2>
        <div class="form-grid">
            <div class="admin-field"><label class="admin-label">Show this many days before start</label><input class="admin-input" type="number" min="0" name="preview_days" value="{{ old('preview_days', $campaign->preview_days ?? 0) }}"></div>
            <div class="admin-field"><label class="admin-label">Popup delay (seconds)</label><input class="admin-input" type="number" name="popup_delay_seconds" value="{{ old('popup_delay_seconds', $campaign->popup_delay_seconds ?? 4) }}"></div>
            <div class="admin-field"><label class="admin-label">Scroll trigger (%)</label><input class="admin-input" type="number" name="popup_scroll_percent" value="{{ old('popup_scroll_percent', $campaign->popup_scroll_percent) }}"></div>
            <div class="admin-field"><label class="admin-label">Frequency</label><select class="admin-select" name="popup_frequency">@foreach (['session' => 'Every session', 'daily' => 'Once per day', 'campaign' => 'Once per campaign', 'cooldown' => 'Custom cooldown'] as $value => $label)<option value="{{ $value }}" @selected(old('popup_frequency', $campaign->popup_frequency ?: 'session') === $value)>{{ $label }}</option>@endforeach</select><p class="form-hint">“Show again after” only applies to Every session.</p></div>
            <div class="admin-field"><label class="admin-label">Cooldown hours</label><input class="admin-input" type="number" name="popup_cooldown_hours" value="{{ old('popup_cooldown_hours', $campaign->popup_cooldown_hours ?? 24) }}"></div>
        </div>
        <div class="campaign-picks">
            <label class="campaign-pick"><input type="checkbox" name="show_before_start" value="1" @checked($flag('show_before_start'))><span><strong>Show before start</strong><small>No discount until it starts</small></span></label>
            <label class="campaign-pick"><input type="checkbox" name="announcement_dismissible" value="1" @checked($flag('announcement_dismissible', true))><span><strong>Bar can be closed</strong><small>Visitor can dismiss it</small></span></label>
            <label class="campaign-pick"><input type="checkbox" name="popup_exit_intent" value="1" @checked($flag('popup_exit_intent'))><span><strong>Exit intent</strong><small>Desktop only</small></span></label>
        </div>
    </section>

    <section class="form-block">
        <h2>Search</h2>
        <div class="form-grid">
            <div class="admin-field"><label class="admin-label">SEO title</label><input class="admin-input" name="seo_title" value="{{ old('seo_title', $campaign->seo_title) }}"></div>
            <div class="admin-field"><label class="admin-label">SEO description</label><input class="admin-input" name="seo_description" value="{{ old('seo_description', $campaign->seo_description) }}"></div>
        </div>
    </section>

    @can('admin.promotions.manage')
        <div class="form-footer">
            <button class="admin-btn admin-btn-accent" type="submit">Save campaign</button>
        </div>
    @endcan
</form>
<style>
.campaign-status{display:flex;align-items:center;gap:.75rem;margin:0 0 .85rem;padding:.7rem .9rem;border:1px solid #e2e8f0;border-top:3px solid #fbb50b;background:#fff}
.campaign-status p{margin:0;color:#334155;font-weight:650}
.campaign-form .form-block h2{color:#013a9d;margin-bottom:1rem}
.campaign-form .form-block + .form-block{margin-top:.25rem;padding-top:1.45rem}
.campaign-form .form-hint{max-width:42rem;margin:0 0 1rem}
.campaign-form .admin-field .form-hint{margin:.5rem 0 0}
.campaign-form .form-grid{gap:1.35rem 1.5rem}
.campaign-form .form-grid + .admin-label,
.campaign-form .campaign-picks + .admin-label{display:block;margin:1.45rem 0 .7rem}
.campaign-form .form-grid + .campaign-picks,
.campaign-form .campaign-picks + .form-grid,
.campaign-form .design-list + .form-grid{margin-top:1.45rem}
.campaign-picks{display:grid;grid-template-columns:repeat(auto-fit,minmax(200px,1fr));gap:1rem;margin:0}
.campaign-pick{display:flex;align-items:flex-start;gap:.65rem;margin:0;padding:.75rem .8rem;border:1px solid #e2e8f0;background:#fff;cursor:pointer;transition:border-color .25s ease,background-color .25s ease,transform .25s ease,box-shadow .25s ease}
.campaign-pick:hover{border-color:#013a9d;transform:translateY(-2px);box-shadow:0 10px 22px rgba(1,58,157,.12)}
.campaign-pick:has(:checked){border-color:#013a9d;background:#f4f7ff;box-shadow:inset 3px 0 0 #fbb50b}
.campaign-pick span{display:grid;gap:.1rem}
.campaign-pick strong{color:#010b24;font-size:.92rem}
.campaign-pick small{color:#5c6168;font-weight:500}
.design-list{display:grid;grid-template-columns:repeat(auto-fit,minmax(220px,1fr));gap:1.35rem 1.5rem;margin:0}
.design-card{display:grid;grid-template-columns:4.6rem 1fr;gap:.75rem;align-items:center;margin:0;padding:.7rem;border:1px solid #e2e8f0;background:#fff;cursor:pointer;transition:border-color .25s ease,transform .25s ease,box-shadow .25s ease}
.design-card:hover{border-color:#013a9d;transform:translateY(-2px);box-shadow:0 10px 22px rgba(1,58,157,.12)}
.design-card:has(:checked){border-color:#013a9d;box-shadow:inset 3px 0 0 #fbb50b}
.design-card input{position:absolute;opacity:0;pointer-events:none}
.design-card strong{display:block;color:#010b24}
.design-card small{display:block;margin-top:.2rem;color:#5c6168;font-weight:500;line-height:1.35}
.design-thumb{display:grid;height:3.4rem;overflow:hidden;border:1px solid #e2e8f0;background:#fff}
.design-thumb i{display:block}
.design-thumb-split{grid-template-columns:1.2fr .8fr}
.design-thumb-split i:first-child{background:#fff;box-shadow:inset 0 3px 0 #fbb50b}
.design-thumb-split i:nth-child(2){background:#f8fafc}
.design-thumb-split i:last-child{display:none}
.design-thumb-night{background:#010b24;border-color:#010b24}
.design-thumb-night i:first-child{height:.45rem;margin:.45rem .4rem 0;background:#fbb50b}
.design-thumb-night i:nth-child(2){height:.35rem;margin:.3rem .4rem 0;background:rgba(255,255,255,.7)}
.design-thumb-night i:last-child{display:none}
.design-thumb-editorial{place-items:center;align-content:center}
.design-thumb-editorial i:first-child{width:1.6rem;height:.4rem;background:#013a9d}
.design-thumb-editorial i:nth-child(2){width:2.2rem;height:.28rem;margin-top:.28rem;background:#fbb50b}
.design-thumb-editorial i:last-child{display:none}
.design-thumb-rail{grid-template-columns:.9rem 1fr}
.design-thumb-rail i:first-child{background:linear-gradient(#ffe08a,#fbb50b)}
.design-thumb-rail i:nth-child(2){background:#fff}
.design-thumb-rail i:last-child{display:none}
.design-thumb-horizon i:first-child{height:1.35rem;background:linear-gradient(135deg,#013a9d,#010b24)}
.design-thumb-horizon i:nth-child(2){height:.4rem;margin:.35rem .4rem 0;background:#013a9d}
.design-thumb-horizon i:last-child{display:none}
@media (max-width:720px){.campaign-status{align-items:flex-start;flex-direction:column}}
</style>
@endsection
