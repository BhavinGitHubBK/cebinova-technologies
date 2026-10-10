@extends('layouts.admin')
@section('title', $option->exists ? 'Edit extra charge' : 'Add extra charge')
@section('breadcrumb', 'Admin / Extra charges / Form')
@section('heading', $option->exists ? 'Edit extra charge' : 'Add extra charge')
@section('content')
<form class="admin-card admin-form" method="POST" action="{{ $option->exists ? route('admin.pricing-options.update', $option) : route('admin.pricing-options.store') }}">
    @csrf
    @if($option->exists) @method('PUT') @endif

    <section class="form-block">
        <h2>Option</h2>
        <div class="form-grid">
            <div class="admin-field">
                <label class="admin-label" for="option-type">Type</label>
                <select id="option-type" class="admin-select" name="type" required>
                    @foreach($types as $key => $label)
                        <option value="{{ $key }}" @selected(old('type', $option->type) === $key)>{{ $label }}</option>
                    @endforeach
                </select>
                <p class="form-hint">Used by the pricing calculator. This is not a website or marketing plan.</p>
            </div>
            <div class="admin-field">
                <label class="admin-label" for="option-name">Name</label>
                <input id="option-name" class="admin-input" name="name" value="{{ old('name', $option->name) }}" required>
            </div>
            <div class="admin-field">
                <label class="admin-label" for="option-slug">Slug</label>
                <input id="option-slug" class="admin-input" name="slug" value="{{ old('slug', $option->slug) }}">
            </div>
            <div class="admin-field">
                <label class="admin-label" for="option-price">Price (INR)</label>
                <input id="option-price" class="admin-input" type="number" step="0.01" min="0" name="price" value="{{ old('price', $option->price ?? 0) }}" required>
            </div>
            <div class="admin-field">
                <label class="admin-label" for="option-billing">Billing period</label>
                <select id="option-billing" class="admin-select" name="billing_period" required>
                    @foreach(\App\Models\PricingOption::BILLING_PERIODS as $key => $label)
                        <option value="{{ $key }}" @selected(old('billing_period', $option->billing_period ?: 'one_time') === $key)>{{ $label }}</option>
                    @endforeach
                </select>
            </div>
            <div class="admin-field">
                <label class="admin-label" for="option-order">Sort order</label>
                <input id="option-order" class="admin-input" type="number" min="0" name="sort_order" value="{{ old('sort_order', $option->sort_order ?? 0) }}">
            </div>
            <div class="admin-field is-wide">
                <label class="admin-label" for="option-description">Description</label>
                <textarea id="option-description" class="admin-textarea" name="description" rows="{{ admin_rows(old('description', $option->description), 3) }}">{{ old('description', $option->description) }}</textarea>
            </div>
        </div>
    </section>

    <div class="form-footer">
        <input type="hidden" name="is_recommended" value="0">
        <label class="admin-check"><input type="checkbox" name="is_recommended" value="1" @checked(old('is_recommended', $option->is_recommended))> Recommended</label>
        <input type="hidden" name="is_active" value="0">
        <label class="admin-check"><input type="checkbox" name="is_active" value="1" @checked(old('is_active', $option->exists ? $option->is_active : true))> Active</label>
        <button class="admin-btn admin-btn-primary" type="submit">Save charge</button>
    </div>
</form>
@endsection
