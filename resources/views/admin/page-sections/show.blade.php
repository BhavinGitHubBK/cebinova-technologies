@extends('layouts.admin')
@section('title', $section->heading ?: $section->key)
@section('breadcrumb', 'Admin / Page sections / View')
@section('heading', $section->heading ?: $section->key)
@section('content')
<div class="admin-card">
<p><strong>Page:</strong> {{ $section->page }}</p>
<p><strong>Key:</strong> {{ $section->key }}</p>
<p><strong>Subheading:</strong> {{ $section->subheading }}</p>
<p>{{ $section->body }}</p>
<p><strong>CTA:</strong> {{ $section->cta_label }} → {{ $section->cta_url }}</p>
@can('admin.content.manage')
<a class="admin-btn admin-btn-primary" href="{{ route('admin.page-sections.edit', $section) }}">Edit</a>
<form method="POST" action="{{ route('admin.page-sections.destroy', $section) }}" style="display:inline;">@csrf @method('DELETE')
<button class="admin-btn admin-btn-danger" data-confirm="Delete this section?" type="submit">Delete</button></form>
@endcan
</div>
@endsection
