@extends('layouts.admin')
@section('title', $service->exists ? 'Edit service' : 'Create service')
@section('breadcrumb', 'Admin / Services / Form')
@section('heading', $service->exists ? 'Edit service' : 'Create service')
@section('content')
<form method="POST" action="{{ $service->exists ? route('admin.services.update', $service) : route('admin.services.store') }}" class="admin-card">
@csrf
@if($service->exists) @method('PUT') @endif
<div class="admin-field"><label class="admin-label">Name</label><input class="admin-input" name="name" value="{{ old('name', $service->name) }}" required></div>
<div class="admin-field"><label class="admin-label">Slug</label><input class="admin-input" name="slug" value="{{ old('slug', $service->slug) }}"></div>
<div class="admin-field"><label class="admin-label">Category</label><input class="admin-input" name="category" value="{{ old('category', $service->category) }}"></div>
<div class="admin-field"><label class="admin-label">Icon key</label><input class="admin-input" name="icon" value="{{ old('icon', $service->icon) }}"></div>
<div class="admin-field"><label class="admin-label">Short description</label><textarea class="admin-textarea" name="short_description" rows="2">{{ old('short_description', $service->short_description) }}</textarea></div>
<div class="admin-field"><label class="admin-label">Full description</label><textarea class="admin-textarea" name="full_description" rows="5">{{ old('full_description', $service->full_description) }}</textarea></div>
<div class="admin-field"><label class="admin-label">Features (one per line)</label><textarea class="admin-textarea" name="features_text" rows="5">{{ old('features_text', implode("\n", $service->features ?? [])) }}</textarea></div>
<div class="admin-field"><label class="admin-label">Featured image path/URL</label><input class="admin-input" name="featured_image" value="{{ old('featured_image', $service->featured_image) }}"></div>
<div class="admin-field"><label class="admin-label">CTA label</label><input class="admin-input" name="cta_label" value="{{ old('cta_label', $service->cta_label) }}"></div>
<div class="admin-field"><label class="admin-label">CTA URL</label><input class="admin-input" name="cta_url" value="{{ old('cta_url', $service->cta_url) }}"></div>
<div class="admin-field"><label class="admin-label">Sort order</label><input class="admin-input" type="number" name="sort_order" value="{{ old('sort_order', $service->sort_order ?? 0) }}"></div>
<label style="display:flex;gap:.4rem;align-items:center;margin-bottom:.6rem;"><input type="checkbox" name="is_featured" value="1" @checked(old('is_featured', $service->is_featured))> Featured</label>
<label style="display:flex;gap:.4rem;align-items:center;margin-bottom:.6rem;"><input type="checkbox" name="is_active" value="1" @checked(old('is_active', $service->is_active ?? true))> Active</label>
<div class="admin-field"><label class="admin-label">SEO title</label><input class="admin-input" name="seo_title" value="{{ old('seo_title', $service->seo_title) }}"></div>
<div class="admin-field"><label class="admin-label">SEO description</label><textarea class="admin-textarea" name="seo_description" rows="2">{{ old('seo_description', $service->seo_description) }}</textarea></div>
<button class="admin-btn admin-btn-primary" type="submit">Save</button>
</form>
@endsection
