@extends('layouts.admin')
@section('title', $post->exists ? 'Edit post' : 'Create post')
@section('breadcrumb', 'Admin / Blog posts / Form')
@section('heading', $post->exists ? 'Edit post' : 'Create post')
@section('content')
<form method="POST" action="{{ $post->exists ? route('admin.blog-posts.update', $post) : route('admin.blog-posts.store') }}" class="admin-card">
@csrf
@if($post->exists) @method('PUT') @endif
<div class="admin-field"><label class="admin-label">Title</label><input class="admin-input" name="title" value="{{ old('title', $post->title) }}" required></div>
<div class="admin-field"><label class="admin-label">Slug</label><input class="admin-input" name="slug" value="{{ old('slug', $post->slug) }}"></div>
<div class="admin-field"><label class="admin-label">Category</label>
<select class="admin-input" name="blog_category_id">
<option value="">— None —</option>
@foreach($categories as $category)
<option value="{{ $category->id }}" @selected(old('blog_category_id', $post->blog_category_id) == $category->id)>{{ $category->name }}</option>
@endforeach
</select></div>
<div class="admin-field"><label class="admin-label">Excerpt</label><textarea class="admin-textarea" name="excerpt" rows="2">{{ old('excerpt', $post->excerpt) }}</textarea></div>
<div class="admin-field"><label class="admin-label">Content</label><textarea class="admin-textarea" name="content" rows="12">{{ old('content', $post->content) }}</textarea></div>
<div class="admin-field"><label class="admin-label">Featured image path/URL</label><input class="admin-input" name="featured_image" value="{{ old('featured_image', $post->featured_image) }}"></div>
<div class="admin-field"><label class="admin-label">Status</label>
<select class="admin-input" name="status">
@foreach(['draft','published','scheduled'] as $status)
<option value="{{ $status }}" @selected(old('status', $post->status ?? 'draft') === $status)>{{ ucfirst($status) }}</option>
@endforeach
</select></div>
<div class="admin-field"><label class="admin-label">Published at</label><input class="admin-input" type="datetime-local" name="published_at" value="{{ old('published_at', optional($post->published_at)->format('Y-m-d\TH:i')) }}"></div>
<label style="display:flex;gap:.4rem;align-items:center;margin-bottom:.6rem;"><input type="checkbox" name="is_featured" value="1" @checked(old('is_featured', $post->is_featured))> Featured</label>
<div class="admin-field"><label class="admin-label">SEO title</label><input class="admin-input" name="seo_title" value="{{ old('seo_title', $post->seo_title) }}"></div>
<div class="admin-field"><label class="admin-label">SEO description</label><textarea class="admin-textarea" name="seo_description" rows="2">{{ old('seo_description', $post->seo_description) }}</textarea></div>
<div class="admin-field"><label class="admin-label">Canonical URL</label><input class="admin-input" name="canonical_url" value="{{ old('canonical_url', $post->canonical_url) }}"></div>
<div class="admin-field"><label class="admin-label">OG image</label><input class="admin-input" name="og_image" value="{{ old('og_image', $post->og_image) }}"></div>
<button class="admin-btn admin-btn-primary" type="submit">Save</button>
</form>
@endsection
