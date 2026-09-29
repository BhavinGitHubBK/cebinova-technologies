@extends('layouts.admin')
@section('title', $option->exists ? 'Edit pricing option' : 'Add pricing option')
@section('breadcrumb', 'Admin / Pricing / Options')
@section('heading', $option->exists ? 'Edit pricing option' : 'Add pricing option')
@section('content')
<form class="admin-card" method="POST" action="{{ $option->exists ? route('admin.pricing-options.update', $option) : route('admin.pricing-options.store') }}">
@csrf @if($option->exists) @method('PUT') @endif
<div class="admin-field"><label class="admin-label">Type</label><select class="admin-select" name="type" required>@foreach($types as $key => $label)<option value="{{ $key }}" @selected(old('type', $option->type) === $key)>{{ $label }}</option>@endforeach</select></div>
<div class="admin-field"><label class="admin-label">Name</label><input class="admin-input" name="name" value="{{ old('name', $option->name) }}" required></div>
<div class="admin-field"><label class="admin-label">Slug</label><input class="admin-input" name="slug" value="{{ old('slug', $option->slug) }}"></div>
<div class="admin-field"><label class="admin-label">Description</label><textarea class="admin-textarea" name="description">{{ old('description', $option->description) }}</textarea></div>
<div class="admin-field"><label class="admin-label">Price (INR)</label><input class="admin-input" type="number" step="0.01" min="0" name="price" value="{{ old('price', $option->price ?? 0) }}" required></div>
<div class="admin-field"><label class="admin-label">Billing period</label><select class="admin-select" name="billing_period">@foreach(\App\Models\PricingOption::BILLING_PERIODS as $key => $label)<option value="{{ $key }}" @selected(old('billing_period', $option->billing_period ?: 'one_time') === $key)>{{ $label }}</option>@endforeach</select></div>
<div class="admin-field"><label class="admin-label">Sort order</label><input class="admin-input" type="number" min="0" name="sort_order" value="{{ old('sort_order', $option->sort_order ?? 0) }}"></div>
<input type="hidden" name="is_recommended" value="0"><label style="display:flex;gap:.4rem;margin-bottom:.5rem"><input type="checkbox" name="is_recommended" value="1" @checked(old('is_recommended', $option->is_recommended))> Recommended</label>
<input type="hidden" name="is_active" value="0"><label style="display:flex;gap:.4rem;margin-bottom:1rem"><input type="checkbox" name="is_active" value="1" @checked(old('is_active', $option->exists ? $option->is_active : true))> Active</label>
<button class="admin-btn admin-btn-primary" type="submit">Save option</button>
</form>
@endsection
