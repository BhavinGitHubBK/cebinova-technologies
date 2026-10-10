@extends('layouts.admin')
@section('title', $coupon->exists ? 'Edit coupon' : 'New coupon')
@section('heading', $coupon->exists ? $coupon->code : 'New coupon')
@section('breadcrumb', 'Admin / Promotions / Coupon')
@section('content')
@include('admin.promotions.partials.nav')
@php
    $when = fn ($value) => $value ? $value->timezone(config('app.timezone'))->format('Y-m-d\TH:i') : '';
    $active = session()->hasOldInput()
        ? (bool) old('is_active')
        : ($coupon->exists ? (bool) $coupon->is_active : true);
@endphp
<form class="admin-card admin-form coupon-form" method="POST" action="{{ $coupon->exists ? route('admin.promotions.coupons.update', $coupon) : route('admin.promotions.coupons.store') }}">
    @csrf
    @if ($coupon->exists) @method('PUT') @endif

    @if ($coupon->exists)
        <div class="coupon-status">
            <span class="admin-badge {{ $coupon->is_active ? 'is-won' : 'is-off' }}">{{ $coupon->is_active ? 'Active' : 'Off' }}</span>
            <p>{{ $coupon->campaign?->name ?: 'Standalone. It can apply to any current quote.' }}</p>
        </div>
    @endif

    <section class="form-block">
        <h2>The coupon</h2>
        <p class="form-hint">The code is what a visitor types. The name is only for this admin list.</p>
        <div class="form-grid">
            <div class="admin-field">
                <label class="admin-label" for="coupon-code">Code</label>
                <input id="coupon-code" class="admin-input" name="code" value="{{ old('code', $coupon->code) }}" placeholder="STARTUP10" required>
                <p class="form-hint">Letters, numbers, or hyphens. Saved in capitals.</p>
            </div>
            <div class="admin-field">
                <label class="admin-label" for="coupon-name">Name</label>
                <input id="coupon-name" class="admin-input" name="name" value="{{ old('name', $coupon->name) }}" placeholder="New business 10">
            </div>
        </div>
        <div class="coupon-picks">
            <label class="coupon-pick">
                <input type="checkbox" name="is_active" value="1" @checked($active)>
                <span><strong>Active</strong><small>Visitors can use this code</small></span>
            </label>
        </div>
    </section>

    <section class="form-block">
        <h2>Where it applies</h2>
        <div class="form-grid">
            <div class="admin-field">
                <label class="admin-label" for="coupon-campaign">Campaign</label>
                <select id="coupon-campaign" class="admin-select" name="promotion_id">
                    <option value="">Standalone</option>
                    @foreach ($campaigns as $row)
                        <option value="{{ $row->id }}" @selected((string) old('promotion_id', $coupon->promotion_id) === (string) $row->id)>{{ $row->name }}</option>
                    @endforeach
                </select>
                <p class="form-hint">Standalone uses the live offer. A campaign limits this code to that offer.</p>
            </div>
        </div>
    </section>

    <section class="form-block">
        <h2>Discount</h2>
        <p class="form-hint">Leave the type empty to use the campaign discount.</p>
        <div class="form-grid">
            <div class="admin-field">
                <label class="admin-label" for="coupon-discount-type">Own discount type</label>
                <select id="coupon-discount-type" class="admin-select" name="discount_type">
                    <option value="">Use campaign discount</option>
                    @foreach (\App\Enums\PromotionDiscountType::options() as $value => $label)
                        <option value="{{ $value }}" @selected(old('discount_type', $coupon->discount_type?->value) === $value)>{{ $label }}</option>
                    @endforeach
                </select>
            </div>
            <div class="admin-field">
                <label class="admin-label" for="coupon-discount-value">Own discount value</label>
                <input id="coupon-discount-value" class="admin-input" type="number" step="0.01" min="0" name="discount_value" value="{{ old('discount_value', $coupon->discount_value) }}">
            </div>
            <div class="admin-field">
                <label class="admin-label" for="coupon-minimum">Minimum amount</label>
                <input id="coupon-minimum" class="admin-input" type="number" step="0.01" min="0" name="minimum_amount" value="{{ old('minimum_amount', $coupon->minimum_amount) }}">
                <p class="form-hint">Quote must reach this amount before the code works. Empty means no minimum.</p>
            </div>
            <div class="admin-field">
                <label class="admin-label" for="coupon-maximum">Maximum discount</label>
                <input id="coupon-maximum" class="admin-input" type="number" step="0.01" min="0" name="maximum_discount_amount" value="{{ old('maximum_discount_amount', $coupon->maximum_discount_amount) }}">
                <p class="form-hint">Caps the rupee amount taken off. Empty means no cap.</p>
            </div>
        </div>
    </section>

    <section class="form-block">
        <h2>Limits and dates</h2>
        <div class="form-grid">
            <div class="admin-field">
                <label class="admin-label" for="coupon-usage">Usage limit</label>
                <input id="coupon-usage" class="admin-input" type="number" min="1" name="usage_limit" value="{{ old('usage_limit', $coupon->usage_limit) }}">
                <p class="form-hint">How many times this code can be used in total. Empty means unlimited.</p>
            </div>
            <div class="admin-field">
                <label class="admin-label" for="coupon-per-customer">Limit per customer</label>
                <input id="coupon-per-customer" class="admin-input" type="number" min="1" name="usage_limit_per_customer" value="{{ old('usage_limit_per_customer', $coupon->usage_limit_per_customer) }}">
            </div>
            <div class="admin-field">
                <label class="admin-label" for="coupon-starts">Starts</label>
                <input id="coupon-starts" class="admin-input" type="datetime-local" name="starts_at" value="{{ old('starts_at', $when($coupon->starts_at)) }}">
            </div>
            <div class="admin-field">
                <label class="admin-label" for="coupon-ends">Ends</label>
                <input id="coupon-ends" class="admin-input" type="datetime-local" name="ends_at" value="{{ old('ends_at', $when($coupon->ends_at)) }}">
                <p class="form-hint">Empty dates follow the campaign schedule.</p>
            </div>
        </div>
    </section>

    @can('admin.promotions.manage')
        <div class="form-footer">
            <button class="admin-btn admin-btn-accent" type="submit">Save coupon</button>
        </div>
    @endcan
</form>
@if ($coupon->exists)
    @can('admin.promotions.manage')
        <form class="coupon-delete" method="POST" action="{{ route('admin.promotions.coupons.destroy', $coupon) }}" data-skip-ajax="1">
            @csrf
            @method('DELETE')
            <button class="admin-btn admin-btn-danger" type="submit" data-confirm="Delete this coupon? Visitors will no longer be able to use it.">Delete coupon</button>
        </form>
    @endcan
@endif
<style>
.coupon-status{display:flex;align-items:center;gap:.75rem;margin:0;padding:.7rem .9rem;border:1px solid #e2e8f0;border-top:3px solid #fbb50b;background:#fff}
.coupon-status p{margin:0;color:#334155;font-weight:650}
.coupon-form .form-block h2{color:#013a9d;margin-bottom:1rem}
.coupon-form .form-block + .form-block{margin-top:.25rem;padding-top:1.45rem}
.coupon-form .form-hint{max-width:42rem;margin:0 0 1rem}
.coupon-form .admin-field .form-hint{margin:.5rem 0 0}
.coupon-form .form-grid{gap:1.35rem 1.5rem}
.coupon-form .form-grid + .coupon-picks,.coupon-form .coupon-picks{margin-top:1.45rem}
.coupon-picks{display:grid;grid-template-columns:repeat(auto-fit,minmax(220px,1fr));gap:1rem;margin:0}
.coupon-pick{display:flex;align-items:flex-start;gap:.65rem;margin:0;padding:.75rem .8rem;border:1px solid #e2e8f0;background:#fff;cursor:pointer;transition:border-color .25s ease,background-color .25s ease,transform .25s ease,box-shadow .25s ease}
.coupon-pick:hover{border-color:#013a9d;transform:translateY(-2px);box-shadow:0 10px 22px rgba(1,58,157,.12)}
.coupon-pick:has(:checked){border-color:#013a9d;background:#f4f7ff;box-shadow:inset 3px 0 0 #fbb50b}
.coupon-pick span{display:grid;gap:.1rem}
.coupon-pick strong{color:#010b24;font-size:.92rem}
.coupon-pick small{color:#5c6168;font-weight:500}
.coupon-delete{display:flex;justify-content:flex-end;margin-top:.75rem}
</style>
@endsection
