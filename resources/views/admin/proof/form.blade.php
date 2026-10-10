@extends('layouts.admin')
@section('title', $record->exists ? 'Edit demo' : 'Add demo')
@section('breadcrumb', 'Admin / Demos / Form')
@section('heading', $record->exists ? 'Edit demo' : 'Add demo')
@section('content')
<form method="POST" enctype="multipart/form-data" action="{{ $record->exists ? route('admin.demos.update', $record) : route('admin.demos.store') }}" class="admin-card admin-form">
    @csrf
    @if($record->exists) @method('PUT') @endif

    <section class="form-block">
        <h2>Basics</h2>
        <p class="form-hint">Changing a title keeps its slug. Changing a slug changes the public URL.</p>
        <div class="form-grid">
            <div class="admin-field">
                <label class="admin-label" for="demo-name">Name</label>
                <input id="demo-name" class="admin-input" name="name" value="{{ old('name', $record->name) }}" required>
            </div>
            <div class="admin-field">
                <label class="admin-label" for="demo-slug">Slug</label>
                <input id="demo-slug" class="admin-input" name="slug" value="{{ old('slug', $record->slug) }}">
            </div>
            <div class="admin-field">
                <label class="admin-label" for="demo-badge">Badge</label>
                <select id="demo-badge" class="admin-select" name="badge">
                    @foreach(['CONCEPT', 'SOLUTION DEMO', 'LIVE DEMO'] as $badge)
                        <option value="{{ $badge }}" @selected(old('badge', $record->badge) === $badge)>{{ $badge }}</option>
                    @endforeach
                </select>
            </div>
            <div class="admin-field">
                <label class="admin-label" for="demo-order">Sort order</label>
                <input id="demo-order" class="admin-input" type="number" min="0" name="sort_order" value="{{ old('sort_order', $record->sort_order ?? 0) }}">
            </div>
            <div class="admin-field is-wide">
                <label class="admin-label" for="demo-short">Short description</label>
                <textarea id="demo-short" class="admin-textarea" name="short_description" rows="{{ admin_rows(old('short_description', $record->short_description), 2) }}" required>{{ old('short_description', $record->short_description) }}</textarea>
            </div>
            <div class="admin-field is-wide">
                <label class="admin-label" for="demo-description">Description</label>
                <textarea id="demo-description" class="admin-textarea" name="description" rows="{{ admin_rows(old('description', $record->description), 4) }}">{{ old('description', $record->description) }}</textarea>
            </div>
        </div>
    </section>

    <section class="form-block">
        <h2>Media</h2>
        <div class="form-grid">
            <div class="admin-field">
                <label class="admin-label" for="demo-thumb">Thumbnail / hero image</label>
                <input id="demo-thumb" class="admin-input" type="file" name="thumbnail_upload" accept="image/jpeg,image/png,image/webp">
                <p class="form-hint">JPG, PNG, or WebP. Maximum 4 MB.</p>
                @if($record->thumbnail)
                    <img src="{{ asset($record->thumbnail) }}" alt="Current image" width="160">
                @endif
            </div>
            <div class="admin-field">
                <label class="admin-label" for="demo-live">External live URL</label>
                <input id="demo-live" class="admin-input" name="live_url" type="url" value="{{ old('live_url', $record->live_url) }}">
                @if($record->local_demo)
                    <p class="form-hint">Preserved live demo: <a href="{{ $record->liveLink() }}">{{ $record->liveLink() }}</a></p>
                @endif
            </div>
            <div class="admin-field is-wide">
                <label class="admin-label" for="demo-tech">Technologies</label>
                <textarea id="demo-tech" class="admin-textarea" name="technologies_text" rows="{{ admin_rows(old('technologies_text', implode("\n", $record->technologies ?? [])), 3) }}">{{ old('technologies_text', implode("\n", $record->technologies ?? [])) }}</textarea>
                <p class="form-hint">One technology per line.</p>
            </div>
            <div class="admin-field is-wide">
                <label class="admin-label" for="demo-shots">Add screenshots</label>
                <input id="demo-shots" class="admin-input" type="file" name="screenshots_upload[]" multiple accept="image/jpeg,image/png,image/webp">
            </div>
        </div>
        @if(count($record->screenshots ?? []))
            <div class="form-stack">
                @foreach($record->screenshots as $index => $shot)
                    <div class="plan-row">
                        <img src="{{ asset($shot['image']) }}" alt="{{ $shot['alt_text'] }}" width="160">
                        <div class="form-grid">
                            <div class="admin-field"><label class="admin-label">Alt text</label><input class="admin-input" name="screenshots[{{ $index }}][alt_text]" value="{{ old('screenshots.'.$index.'.alt_text', $shot['alt_text']) }}"></div>
                            <div class="admin-field"><label class="admin-label">Caption</label><input class="admin-input" name="screenshots[{{ $index }}][caption]" value="{{ old('screenshots.'.$index.'.caption', $shot['caption']) }}"></div>
                            <div class="admin-field"><label class="admin-label">Order</label><input class="admin-input" type="number" min="0" name="screenshots[{{ $index }}][sort_order]" value="{{ old('screenshots.'.$index.'.sort_order', $shot['sort_order']) }}"></div>
                            <div class="admin-field"><label class="admin-check"><input type="checkbox" name="screenshots[{{ $index }}][remove]" value="1"> Remove from gallery</label></div>
                        </div>
                    </div>
                @endforeach
            </div>
        @endif
    </section>

    <section class="form-block">
        <h2>Features</h2>
        <p class="form-hint">Blank names are ignored on save.</p>
        @php
            $featureRows = array_values(old('features', $record->features ?? []) ?: []);
            $featureRows[] = ['name' => '', 'description' => '', 'is_active' => true, 'sort_order' => count($featureRows)];
        @endphp
        <div class="feature-list" data-feature-list>
            <div class="feature-line feature-line-head">
                <span>Name</span>
                <span>Description</span>
                <span>Order</span>
                <span>Active</span>
                <span></span>
            </div>
            @foreach($featureRows as $index => $feature)
                <div class="feature-line" data-feature-row>
                    <input class="admin-input" name="features[{{ $index }}][name]" value="{{ $feature['name'] ?? '' }}" placeholder="Feature name">
                    <textarea class="admin-textarea" name="features[{{ $index }}][description]" rows="{{ admin_rows($feature['description'] ?? '', 1) }}" placeholder="Short note">{{ $feature['description'] ?? '' }}</textarea>
                    <input class="admin-input" type="number" min="0" name="features[{{ $index }}][sort_order]" value="{{ $feature['sort_order'] ?? $index }}">
                    <label class="admin-check">
                        <input type="hidden" name="features[{{ $index }}][is_active]" value="0">
                        <input type="checkbox" name="features[{{ $index }}][is_active]" value="1" @checked($feature['is_active'] ?? true)>
                    </label>
                    <button type="button" class="plan-remove" data-feature-remove>Remove</button>
                </div>
            @endforeach
        </div>
        <button type="button" class="plan-add" data-feature-add>Add feature</button>
    </section>

    <section class="form-block">
        <h2>Links</h2>
        <div class="form-stack">
            @foreach(['service' => $services, 'solution' => $solutions, 'industry' => $industries] as $key => $options)
                @php($relation = $key === 'industry' ? 'industries' : $key.'s')
                <div class="admin-field">
                    <span class="admin-label">{{ $key === 'service' ? 'Services' : ($key === 'solution' ? 'Solutions' : 'Industries') }}</span>
                    <div class="choice-grid">
                        @foreach($options as $option)
                            <label>
                                <input type="checkbox" name="{{ $key }}_ids[]" value="{{ $option->id }}" @checked(in_array($option->id, old($key.'_ids', $record->exists ? $record->$relation->modelKeys() : [])))>
                                {{ $option->name }}
                            </label>
                        @endforeach
                    </div>
                </div>
            @endforeach
        </div>
    </section>

    <div class="form-footer">
        @foreach(['is_featured' => 'Featured', 'is_active' => 'Active'] as $field => $label)
            <input type="hidden" name="{{ $field }}" value="0">
            <label class="admin-check"><input type="checkbox" name="{{ $field }}" value="1" @checked(old($field, $record->$field))> {{ $label }}</label>
        @endforeach
        <button class="admin-btn admin-btn-primary" type="submit">Save</button>
    </div>
</form>
<template id="feature-row-template">
    <div class="feature-line" data-feature-row>
        <input class="admin-input" name="features[__INDEX__][name]" value="" placeholder="Feature name">
        <textarea class="admin-textarea" name="features[__INDEX__][description]" rows="1" placeholder="Short note"></textarea>
        <input class="admin-input" type="number" min="0" name="features[__INDEX__][sort_order]" value="__INDEX__">
        <label class="admin-check">
            <input type="hidden" name="features[__INDEX__][is_active]" value="0">
            <input type="checkbox" name="features[__INDEX__][is_active]" value="1" checked>
        </label>
        <button type="button" class="plan-remove" data-feature-remove>Remove</button>
    </div>
</template>
@endsection
