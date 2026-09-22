@extends('layouts.admin')
@section('title', $package->exists ? 'Edit package' : 'Create package')
@section('breadcrumb', 'Admin / Packages / Form')
@section('heading', $package->exists ? 'Edit package' : 'Create package')
@section('content')
<form method="POST" action="{{ $package->exists ? route('admin.packages.update', $package) : route('admin.packages.store') }}" class="admin-card">
@csrf
@if($package->exists) @method('PUT') @endif
<div class="admin-field"><label class="admin-label">Category</label>
<select class="admin-select" name="category" required>
@foreach($categories as $key => $label)<option value="{{ $key }}" @selected(old('category', $package->category)===$key)>{{ $label }}</option>@endforeach
</select></div>
<div class="admin-field"><label class="admin-label">Name</label><input class="admin-input" name="name" value="{{ old('name', $package->name) }}" required></div>
<div class="admin-field"><label class="admin-label">Slug</label><input class="admin-input" name="slug" value="{{ old('slug', $package->slug) }}"></div>
<div class="admin-field"><label class="admin-label">Key</label><input class="admin-input" name="key" value="{{ old('key', $package->key) }}" placeholder="regular / festival / growth / starter"></div>
<div class="admin-field"><label class="admin-label">Heading</label><input class="admin-input" name="heading" value="{{ old('heading', $package->heading) }}"></div>
<div class="admin-field"><label class="admin-label">Teaser</label><input class="admin-input" name="teaser" value="{{ old('teaser', $package->teaser) }}"></div>
<div class="admin-field"><label class="admin-label">Short description</label><textarea class="admin-textarea" name="short_description" rows="2">{{ old('short_description', $package->short_description) }}</textarea></div>
<div class="admin-field"><label class="admin-label">Subheading</label><textarea class="admin-textarea" name="subheading" rows="2">{{ old('subheading', $package->subheading) }}</textarea></div>
<div class="admin-field"><label class="admin-label">Best if</label><input class="admin-input" name="best_if" value="{{ old('best_if', $package->best_if) }}"></div>
<div class="admin-field"><label class="admin-label">Service label</label><input class="admin-input" name="service_label" value="{{ old('service_label', $package->service_label) }}"></div>
<div class="admin-field"><label class="admin-label">Badge</label><input class="admin-input" name="badge" value="{{ old('badge', $package->badge) }}"></div>
<div class="admin-field"><label class="admin-label">CTA</label><input class="admin-input" name="cta_label" value="{{ old('cta_label', $package->cta_label) }}"></div>
<div class="admin-field"><label class="admin-label">Secondary CTA</label><input class="admin-input" name="secondary_cta" value="{{ old('secondary_cta', $package->secondary_cta) }}"></div>
<div class="admin-field"><label class="admin-label">Why</label><textarea class="admin-textarea" name="why" rows="2">{{ old('why', $package->why) }}</textarea></div>
<div class="admin-field"><label class="admin-label">Includes (one per line)</label><textarea class="admin-textarea" name="includes_text" rows="4">{{ old('includes_text', implode("\n", $package->includes ?? [])) }}</textarea></div>
<div class="admin-field"><label class="admin-label">Sort order</label><input class="admin-input" type="number" name="sort_order" value="{{ old('sort_order', $package->sort_order ?? 0) }}"></div>
<label style="display:flex;gap:.4rem;margin-bottom:.5rem;"><input type="checkbox" name="is_highlighted" value="1" @checked(old('is_highlighted', $package->is_highlighted))> Highlighted</label>
<label style="display:flex;gap:.4rem;margin-bottom:.5rem;"><input type="checkbox" name="is_active" value="1" @checked(old('is_active', $package->is_active ?? true))> Active</label>

<h3 style="margin:1.2rem 0 .6rem;">Plans</h3>
@php $planRows = old('plans', $plans instanceof \Illuminate\Support\Collection ? $plans->values()->all() : ($plans ?: [['label'=>'','price'=>'']])); @endphp
<div data-plan-list>
@foreach($planRows as $i => $plan)
@php $plan = (array) $plan; @endphp
<div class="admin-card" data-plan-row style="margin-bottom:.75rem;background:#f8fafc;">
<div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:.5rem;">
<strong style="font-size:.85rem;color:#334155;">Plan</strong>
<button type="button" class="admin-btn" data-plan-remove style="padding:.25rem .6rem;font-size:.8rem;">Remove</button>
</div>
<div class="admin-field"><label class="admin-label">Label</label><input class="admin-input" name="plans[{{ $i }}][label]" value="{{ $plan['label'] ?? '' }}"></div>
<div class="admin-field"><label class="admin-label">Key</label><input class="admin-input" name="plans[{{ $i }}][key]" value="{{ $plan['key'] ?? '' }}"></div>
<div class="admin-field"><label class="admin-label">Price (INR)</label><input class="admin-input" type="number" name="plans[{{ $i }}][price]" value="{{ $plan['price'] ?? '' }}"></div>
<div class="admin-field"><label class="admin-label">Original price</label><input class="admin-input" type="number" name="plans[{{ $i }}][original_price]" value="{{ $plan['original_price'] ?? '' }}"></div>
<div class="admin-field"><label class="admin-label">Duration</label><input class="admin-input" name="plans[{{ $i }}][duration]" value="{{ $plan['duration'] ?? '' }}"></div>
<div class="admin-field"><label class="admin-label">Period</label><input class="admin-input" name="plans[{{ $i }}][period]" value="{{ $plan['period'] ?? '' }}"></div>
<div class="admin-field"><label class="admin-label">Badge</label><input class="admin-input" name="plans[{{ $i }}][badge]" value="{{ $plan['badge'] ?? '' }}"></div>
<div class="admin-field"><label class="admin-label">Features (one per line)</label><textarea class="admin-textarea" name="plans[{{ $i }}][features_text]" rows="3">{{ is_array($plan['features'] ?? null) ? implode("\n", $plan['features']) : ($plan['features_text'] ?? '') }}</textarea></div>
</div>
@endforeach
</div>
<p style="margin:.5rem 0 1rem;display:flex;gap:.5rem;align-items:center;">
    <button type="button" class="admin-btn" data-plan-add>Add plan</button>
    <span style="font-size:.82rem;color:#64748b;">Empty rows are ignored on save.</span>
</p>
<button class="admin-btn admin-btn-primary" type="submit">Save package</button>
</form>

<template id="plan-row-template">
<div class="admin-card" data-plan-row style="margin-bottom:.75rem;background:#f8fafc;">
<div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:.5rem;">
<strong style="font-size:.85rem;color:#334155;">Plan</strong>
<button type="button" class="admin-btn" data-plan-remove style="padding:.25rem .6rem;font-size:.8rem;">Remove</button>
</div>
<div class="admin-field"><label class="admin-label">Label</label><input class="admin-input" name="plans[__INDEX__][label]" value=""></div>
<div class="admin-field"><label class="admin-label">Key</label><input class="admin-input" name="plans[__INDEX__][key]" value=""></div>
<div class="admin-field"><label class="admin-label">Price (INR)</label><input class="admin-input" type="number" name="plans[__INDEX__][price]" value=""></div>
<div class="admin-field"><label class="admin-label">Original price</label><input class="admin-input" type="number" name="plans[__INDEX__][original_price]" value=""></div>
<div class="admin-field"><label class="admin-label">Duration</label><input class="admin-input" name="plans[__INDEX__][duration]" value=""></div>
<div class="admin-field"><label class="admin-label">Period</label><input class="admin-input" name="plans[__INDEX__][period]" value=""></div>
<div class="admin-field"><label class="admin-label">Badge</label><input class="admin-input" name="plans[__INDEX__][badge]" value=""></div>
<div class="admin-field"><label class="admin-label">Features (one per line)</label><textarea class="admin-textarea" name="plans[__INDEX__][features_text]" rows="3"></textarea></div>
</div>
</template>
@endsection
