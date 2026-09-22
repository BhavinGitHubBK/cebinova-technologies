@extends('layouts.admin')
@section('title', $post->title)
@section('breadcrumb', 'Admin / Blog posts / View')
@section('heading', $post->title)
@section('content')
<div class="admin-card">
<p><strong>Slug:</strong> {{ $post->slug }}</p>
<p><strong>Category:</strong> {{ $post->category?->name }}</p>
<p><strong>Author:</strong> {{ $post->author?->name }}</p>
<p><strong>Status:</strong> {{ $post->status }}</p>
<p><strong>Excerpt:</strong> {{ $post->excerpt }}</p>
<div style="white-space:pre-wrap;margin-top:1rem;">{{ $post->content }}</div>
@can('admin.content.manage')
<div style="margin-top:1rem;">
<a class="admin-btn admin-btn-primary" href="{{ route('admin.blog-posts.edit', $post) }}">Edit</a>
<form method="POST" action="{{ route('admin.blog-posts.destroy', $post) }}" style="display:inline;">@csrf @method('DELETE')
<button class="admin-btn admin-btn-danger" data-confirm="Delete this post?" type="submit">Delete</button></form>
</div>
@endcan
</div>
@endsection
