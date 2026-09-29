@extends('layouts.admin')
@section('title', $service->exists ? 'Edit service' : 'Create service')
@section('breadcrumb', 'Admin / Services / Form')
@section('heading', $service->exists ? 'Edit service' : 'Create service')
@section('content')
@php
    $section = fn (string $type) => $service->sections?->firstWhere('type', $type);
    $lines = fn (string $type, bool $description = true) => $section($type)?->items->map(fn ($item) => $item->title.($description && $item->description ? ' | '.$item->description : ''))->implode("\n") ?? '';
@endphp
<form method="POST" action="{{ $service->exists ? route('admin.services.update', $service) : route('admin.services.store') }}">
@csrf
@if($service->exists) @method('PUT') @endif

<div class="admin-card" style="margin-bottom:1rem;">
<h2 style="margin-top:0;font-size:1rem;">Basic information</h2>
<div class="admin-field"><label class="admin-label">Name</label><input class="admin-input" name="name" value="{{ old('name', $service->name) }}" required></div>
<div class="admin-field"><label class="admin-label">Slug</label><input class="admin-input" name="slug" value="{{ old('slug', $service->slug) }}" placeholder="web-development"><small>Public URL. Changing this changes the service URL.</small></div>
<div class="admin-field"><label class="admin-label">Category</label><input class="admin-input" name="category" value="{{ old('category', $service->category) }}"></div>
<div class="admin-field"><label class="admin-label">Icon key</label><input class="admin-input" name="icon" value="{{ old('icon', $service->icon) }}"></div>
<div class="admin-field"><label class="admin-label">Short description</label><textarea class="admin-textarea" name="short_description" rows="2">{{ old('short_description', $service->short_description) }}</textarea></div>
<div class="admin-field"><label class="admin-label">Full description</label><textarea class="admin-textarea" name="full_description" rows="4">{{ old('full_description', $service->full_description) }}</textarea></div>
<div class="admin-field"><label class="admin-label">Featured image path/URL</label><input class="admin-input" name="featured_image" value="{{ old('featured_image', $service->featured_image) }}"></div>
</div>

<div class="admin-card" style="margin-bottom:1rem;">
<h2 style="margin-top:0;font-size:1rem;">Hero and positioning</h2>
<div class="admin-field"><label class="admin-label">Hero title</label><input class="admin-input" name="hero_title" value="{{ old('hero_title', $service->hero_title) }}"></div>
<div class="admin-field"><label class="admin-label">Hero subtitle</label><textarea class="admin-textarea" name="hero_subtitle" rows="3">{{ old('hero_subtitle', $service->hero_subtitle) }}</textarea></div>
<div class="admin-field"><label class="admin-label">Badge</label><input class="admin-input" name="badge" value="{{ old('badge', $service->badge) }}"></div>
<div class="admin-field"><label class="admin-label">Who this is for</label><textarea class="admin-textarea" name="audience" rows="3">{{ old('audience', $service->audience) }}</textarea></div>
<div class="admin-field"><label class="admin-label">Outcome</label><textarea class="admin-textarea" name="outcome" rows="3">{{ old('outcome', $service->outcome) }}</textarea></div>
</div>

<div class="admin-card" style="margin-bottom:1rem;">
<h2 style="margin-top:0;font-size:1rem;">Structured content</h2>
<div class="admin-field"><label class="admin-label">Features</label><textarea class="admin-textarea" name="features_text" rows="6">{{ old('features_text', $lines('features', false) ?: implode("\n", $service->features ?? [])) }}</textarea><small>One feature per line.</small></div>
@foreach (['benefits' => 'Benefits', 'use_cases' => 'Business use cases', 'technologies' => 'Technologies'] as $type => $label)
<div class="admin-field"><label class="admin-label">{{ $label }}</label><textarea class="admin-textarea" name="{{ $type }}_text" rows="5">{{ old($type.'_text', $lines($type)) }}</textarea><small>One item per line: Title | Description</small></div>
@endforeach
<div class="admin-field"><label class="admin-label">Process title</label><input class="admin-input" name="process_title" value="{{ old('process_title', $section('process')?->title) }}"></div>
<div class="admin-field"><label class="admin-label">Process subtitle</label><textarea class="admin-textarea" name="process_subtitle" rows="2">{{ old('process_subtitle', $section('process')?->subtitle) }}</textarea></div>
<div class="admin-field"><label class="admin-label">Process steps</label><textarea class="admin-textarea" name="process_text" rows="7">{{ old('process_text', $lines('process')) }}</textarea><small>One step per line: Title | Description</small></div>
</div>

<div class="admin-card" style="margin-bottom:1rem;">
<h2 style="margin-top:0;font-size:1rem;">FAQ and CTA</h2>
<div class="admin-field"><label class="admin-label">FAQs</label><textarea class="admin-textarea" name="faqs_text" rows="8">{{ old('faqs_text', $service->faqs?->map(fn ($faq) => $faq->question.' | '.$faq->answer)->implode("\n")) }}</textarea><small>One FAQ per line: Question | Answer</small></div>
<div class="admin-field"><label class="admin-label">CTA label</label><input class="admin-input" name="cta_label" value="{{ old('cta_label', $service->cta_label) }}"></div>
<div class="admin-field"><label class="admin-label">CTA URL</label><input class="admin-input" name="cta_url" value="{{ old('cta_url', $service->cta_url) }}"></div>
<div class="admin-field"><label class="admin-label">Next-step label</label><input class="admin-input" name="next_label" value="{{ old('next_label', $service->next_label) }}"></div>
<div class="admin-field"><label class="admin-label">Next route name</label><input class="admin-input" name="next_route" value="{{ old('next_route', $service->next_route) }}"></div>
<div class="admin-field"><label class="admin-label">Next route parameter</label><input class="admin-input" name="next_param" value="{{ old('next_param', $service->next_param) }}"></div>
</div>

<div class="admin-card">
<h2 style="margin-top:0;font-size:1rem;">Publishing and SEO</h2>
<div class="admin-field"><label class="admin-label">Sort order</label><input class="admin-input" type="number" name="sort_order" value="{{ old('sort_order', $service->sort_order ?? 0) }}"></div>
<input type="hidden" name="is_featured" value="0"><label style="display:flex;gap:.4rem;align-items:center;margin-bottom:.6rem;"><input type="checkbox" name="is_featured" value="1" @checked(old('is_featured', $service->is_featured))> Featured</label>
<input type="hidden" name="is_active" value="0"><label style="display:flex;gap:.4rem;align-items:center;margin-bottom:.6rem;"><input type="checkbox" name="is_active" value="1" @checked(old('is_active', $service->is_active ?? true))> Active</label>
<div class="admin-field"><label class="admin-label">SEO title</label><input class="admin-input" name="seo_title" value="{{ old('seo_title', $service->seo_title) }}"></div>
<div class="admin-field"><label class="admin-label">SEO description</label><textarea class="admin-textarea" name="seo_description" rows="2">{{ old('seo_description', $service->seo_description) }}</textarea></div>
<button class="admin-btn admin-btn-primary" type="submit">Save service</button>
</div>
</form>
@endsection
