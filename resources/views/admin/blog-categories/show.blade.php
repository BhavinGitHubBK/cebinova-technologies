@extends('layouts.admin')
@section('title', $category->name)
@section('breadcrumb', 'Admin / Blog categories / View')
@section('heading', $category->name)
@section('content')
<div class="admin-card">
<p><strong>Slug:</strong> {{ $category->slug }}</p>
<p><strong>Posts:</strong> {{ $category->posts_count ?? $category->posts()->count() }}</p>
<p><strong>Status:</strong> {{ $category->is_active ? 'Active' : 'Inactive' }}</p>
@can('admin.content.manage')
<a class="admin-btn admin-btn-primary" href="{{ route('admin.blog-categories.edit', $category) }}">Edit</a>
<form method="POST" action="{{ route('admin.blog-categories.destroy', $category) }}" style="display:inline;">@csrf @method('DELETE')
<button class="admin-btn admin-btn-danger" data-confirm="Delete this category?" type="submit">Delete</button></form>
@endcan
</div>
@endsection
