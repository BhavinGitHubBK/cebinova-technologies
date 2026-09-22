@extends('layouts.admin')
@section('title', $project->exists ? 'Edit project' : 'Create project')
@section('breadcrumb', 'Admin / Projects / Form')
@section('heading', $project->exists ? 'Edit project' : 'Create project')
@section('content')
<form method="POST" action="{{ $project->exists ? route('admin.projects.update', $project) : route('admin.projects.store') }}" class="admin-card">
@csrf
@if($project->exists) @method('PUT') @endif
<div class="admin-field"><label class="admin-label">Title</label><input class="admin-input" name="title" value="{{ old('title', $project->title) }}" required></div>
<div class="admin-field"><label class="admin-label">Slug</label><input class="admin-input" name="slug" value="{{ old('slug', $project->slug) }}"></div>
<div class="admin-field"><label class="admin-label">Client name</label><input class="admin-input" name="client_name" value="{{ old('client_name', $project->client_name) }}"></div>
<div class="admin-field"><label class="admin-label">Category</label><input class="admin-input" name="category" value="{{ old('category', $project->category) }}"></div>
<div class="admin-field"><label class="admin-label">Preview key</label><input class="admin-input" name="preview" value="{{ old('preview', $project->preview) }}" placeholder="website, store, ecommerce..."></div>
<div class="admin-field"><label class="admin-label">Type</label><input class="admin-input" name="type" value="{{ old('type', $project->type) }}"></div>
<div class="admin-field"><label class="admin-label">Technologies / tags (one per line or comma-separated)</label><textarea class="admin-textarea" name="technologies_text" rows="3">{{ old('technologies_text', implode("\n", $project->technologies ?? [])) }}</textarea></div>
<div class="admin-field"><label class="admin-label">Short description</label><textarea class="admin-textarea" name="short_description" rows="2">{{ old('short_description', $project->short_description) }}</textarea></div>
<div class="admin-field"><label class="admin-label">Full case study</label><textarea class="admin-textarea" name="full_case_study" rows="5">{{ old('full_case_study', $project->full_case_study) }}</textarea></div>
<div class="admin-field"><label class="admin-label">Thumbnail path/URL</label><input class="admin-input" name="thumbnail" value="{{ old('thumbnail', $project->thumbnail) }}"></div>
<div class="admin-field"><label class="admin-label">Gallery paths (one per line)</label><textarea class="admin-textarea" name="gallery_text" rows="3">{{ old('gallery_text', implode("\n", $project->gallery ?? [])) }}</textarea></div>
<div class="admin-field"><label class="admin-label">Project URL</label><input class="admin-input" name="project_url" value="{{ old('project_url', $project->project_url) }}"></div>
<div class="admin-field"><label class="admin-label">Completed at</label><input class="admin-input" type="date" name="completed_at" value="{{ old('completed_at', optional($project->completed_at)->format('Y-m-d')) }}"></div>
<div class="admin-field"><label class="admin-label">Sort order</label><input class="admin-input" type="number" name="sort_order" value="{{ old('sort_order', $project->sort_order ?? 0) }}"></div>
<div class="admin-field"><label class="admin-label">Status</label>
<select class="admin-input" name="status">
<option value="draft" @selected(old('status', $project->status ?? 'draft') === 'draft')>Draft</option>
<option value="published" @selected(old('status', $project->status ?? 'draft') === 'published')>Published</option>
</select></div>
<label style="display:flex;gap:.4rem;align-items:center;margin-bottom:.6rem;"><input type="checkbox" name="is_featured" value="1" @checked(old('is_featured', $project->is_featured))> Featured</label>
<div class="admin-field"><label class="admin-label">SEO title</label><input class="admin-input" name="seo_title" value="{{ old('seo_title', $project->seo_title) }}"></div>
<div class="admin-field"><label class="admin-label">SEO description</label><textarea class="admin-textarea" name="seo_description" rows="2">{{ old('seo_description', $project->seo_description) }}</textarea></div>
<button class="admin-btn admin-btn-primary" type="submit">Save</button>
</form>
@endsection
