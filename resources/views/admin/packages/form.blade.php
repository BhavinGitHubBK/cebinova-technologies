@extends('layouts.admin')
@section('title', $package->exists ? 'Edit package' : 'Create package')
@section('breadcrumb', $package->isMarketing() ? 'Admin / Marketing packages / Form' : 'Admin / Website packages / Form')
@section('heading', $package->exists ? 'Edit package' : 'Create package')
@section('content')
<form method="POST" action="{{ $package->exists ? route('admin.packages.update', $package) : route('admin.packages.store') }}" class="admin-card admin-form" data-package-group="{{ $package->isMarketing() ? 'marketing' : 'website' }}">
    @csrf
    @if($package->exists) @method('PUT') @endif

    <section class="form-block">
        <h2>Basics</h2>
        <div class="form-grid">
            <div class="admin-field">
                <label class="admin-label" for="package-category">Category</label>
                <select id="package-category" class="admin-select" name="category" required>
                    @foreach($categories as $key => $label)
                        <option value="{{ $key }}" @selected(old('category', $package->category) === $key)>{{ $label }}</option>
                    @endforeach
                </select>
                <p class="form-hint">Website plans stay on Website Packages. Marketing plans stay on Marketing Packages. App, domain, and hosting stay on Extra Charges.</p>
            </div>
            <div class="admin-field">
                <label class="admin-label" for="package-name">Name</label>
                <input id="package-name" class="admin-input" name="name" value="{{ old('name', $package->name) }}" required>
            </div>
            <div class="admin-field">
                <label class="admin-label" for="package-slug">Slug</label>
                <input id="package-slug" class="admin-input" name="slug" value="{{ old('slug', $package->slug) }}">
            </div>
            <div class="admin-field">
                <label class="admin-label" for="package-key">Key</label>
                <input id="package-key" class="admin-input" name="key" value="{{ old('key', $package->key) }}" placeholder="regular / festival / growth / starter">
            </div>
            <div class="admin-field">
                <label class="admin-label" for="package-heading">Heading</label>
                <input id="package-heading" class="admin-input" name="heading" value="{{ old('heading', $package->heading) }}">
            </div>
            <div class="admin-field">
                <label class="admin-label" for="package-teaser">Teaser</label>
                <input id="package-teaser" class="admin-input" name="teaser" value="{{ old('teaser', $package->teaser) }}" required>
            </div>
            <div class="admin-field">
                <label class="admin-label" for="package-best">Best if</label>
                <input id="package-best" class="admin-input" name="best_if" value="{{ old('best_if', $package->best_if) }}">
            </div>
            <div class="admin-field">
                <label class="admin-label" for="package-service">Service label</label>
                <input id="package-service" class="admin-input" name="service_label" value="{{ old('service_label', $package->service_label) }}">
            </div>
            <div class="admin-field">
                <label class="admin-label" for="package-badge">Badge</label>
                <input id="package-badge" class="admin-input" name="badge" value="{{ old('badge', $package->badge) }}">
            </div>
            <div class="admin-field">
                <label class="admin-label" for="package-order">Sort order</label>
                <input id="package-order" class="admin-input" type="number" name="sort_order" value="{{ old('sort_order', $package->sort_order ?? 0) }}">
            </div>
        </div>
    </section>

    <section class="form-block">
        <h2>Copy</h2>
        <div class="form-grid">
            <div class="admin-field">
                <label class="admin-label" for="package-short">Short description</label>
                <textarea id="package-short" class="admin-textarea" name="short_description" rows="{{ admin_rows(old('short_description', $package->short_description), 3) }}">{{ old('short_description', $package->short_description) }}</textarea>
            </div>
            <div class="admin-field">
                <label class="admin-label" for="package-sub">Subheading</label>
                <textarea id="package-sub" class="admin-textarea" name="subheading" rows="{{ admin_rows(old('subheading', $package->subheading), 3) }}">{{ old('subheading', $package->subheading) }}</textarea>
            </div>
            <div class="admin-field">
                <label class="admin-label" for="package-why">Why</label>
                <textarea id="package-why" class="admin-textarea" name="why" rows="{{ admin_rows(old('why', $package->why), 3) }}">{{ old('why', $package->why) }}</textarea>
            </div>
            <div class="admin-field">
                <label class="admin-label" for="package-includes">Includes</label>
                <textarea id="package-includes" class="admin-textarea" name="includes_text" rows="{{ admin_rows(old('includes_text', implode("\n", $package->includes ?? [])), 3) }}">{{ old('includes_text', implode("\n", $package->includes ?? [])) }}</textarea>
                <p class="form-hint">One item per line.</p>
            </div>
            <div class="admin-field">
                <label class="admin-label" for="package-cta">CTA</label>
                <input id="package-cta" class="admin-input" name="cta_label" value="{{ old('cta_label', $package->cta_label) }}">
            </div>
            <div class="admin-field">
                <label class="admin-label" for="package-cta2">Secondary CTA</label>
                <input id="package-cta2" class="admin-input" name="secondary_cta" value="{{ old('secondary_cta', $package->secondary_cta) }}">
            </div>
        </div>
    </section>

    <section class="form-block">
        <h2>Plans</h2>
        <p class="form-hint">Empty rows are ignored on save.</p>
        @php
            $planRows = old('plans');
            if (! is_array($planRows)) {
                $planRows = collect($plans ?? [])->map(function ($plan) {
                    $row = $plan instanceof \Illuminate\Database\Eloquent\Model ? $plan->attributesToArray() : (array) $plan;
                    $features = $row['features'] ?? [];
                    $row['features_text'] = $row['features_text'] ?? collect(is_array($features) ? $features : [])
                        ->map(fn ($item) => is_array($item) ? ($item['name'] ?? $item['title'] ?? '') : $item)
                        ->filter()
                        ->implode("\n");
                    return $row;
                })->values()->all();
            }
            if ($planRows === []) {
                $planRows = [['label' => '', 'price' => '']];
            }
        @endphp
        <div class="form-stack" data-plan-list>
            @foreach($planRows as $i => $plan)
                @include('admin.packages.partials.plan-row', ['index' => $i, 'number' => $loop->iteration, 'plan' => $plan])
            @endforeach
        </div>
        <button type="button" class="plan-add" data-plan-add>Add plan</button>
    </section>

    <div class="form-footer">
        <label class="admin-check"><input type="checkbox" name="is_highlighted" value="1" @checked(old('is_highlighted', $package->is_highlighted))> Highlighted</label>
        <label class="admin-check"><input type="checkbox" name="is_active" value="1" @checked(old('is_active', $package->is_active ?? true))> Active</label>
        <button class="admin-btn admin-btn-primary" type="submit">Save package</button>
    </div>
</form>

<template id="plan-row-template">
    @include('admin.packages.partials.plan-row', ['index' => '__INDEX__', 'number' => 0, 'plan' => []])
</template>
@endsection
