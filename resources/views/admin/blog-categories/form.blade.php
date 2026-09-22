@extends('layouts.admin')
@section('title', $category->exists ? 'Edit category' : 'Create category')
@section('breadcrumb', 'Admin / Blog categories / Form')
@section('heading', $category->exists ? 'Edit category' : 'Create category')
@section('content')
<form method="POST" action="{{ $category->exists ? route('admin.blog-categories.update', $category) : route('admin.blog-categories.store') }}" class="admin-card">
@csrf
@if($category->exists) @method('PUT') @endif
<div class="admin-field"><label class="admin-label">Name</label><input class="admin-input" name="name" value="{{ old('name', $category->name) }}" required></div>
<div class="admin-field"><label class="admin-label">Slug</label><input class="admin-input" name="slug" value="{{ old('slug', $category->slug) }}"></div>
<div class="admin-field"><label class="admin-label">Sort order</label><input class="admin-input" type="number" name="sort_order" value="{{ old('sort_order', $category->sort_order ?? 0) }}"></div>
<label style="display:flex;gap:.4rem;align-items:center;margin-bottom:.6rem;"><input type="checkbox" name="is_active" value="1" @checked(old('is_active', $category->is_active ?? true))> Active</label>
<button class="admin-btn admin-btn-primary" type="submit">Save</button>
</form>
@endsection
