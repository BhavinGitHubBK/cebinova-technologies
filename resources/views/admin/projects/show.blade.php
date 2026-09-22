@extends('layouts.admin')
@section('title', $project->title)
@section('breadcrumb', 'Admin / Projects / View')
@section('heading', $project->title)
@section('content')
<div class="admin-card">
<p><strong>Slug:</strong> {{ $project->slug }}</p>
<p><strong>Type:</strong> {{ $project->type }}</p>
<p><strong>Status:</strong> {{ $project->status }}</p>
<p><strong>Summary:</strong> {{ $project->short_description }}</p>
<p><strong>Tags:</strong> {{ implode(', ', $project->technologies ?? []) }}</p>
@can('admin.content.manage')
<a class="admin-btn admin-btn-primary" href="{{ route('admin.projects.edit', $project) }}">Edit</a>
<form method="POST" action="{{ route('admin.projects.destroy', $project) }}" style="display:inline;">@csrf @method('DELETE')
<button class="admin-btn admin-btn-danger" data-confirm="Delete this project?" type="submit">Delete</button></form>
@endcan
</div>
@endsection
