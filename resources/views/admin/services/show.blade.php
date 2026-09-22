@extends('layouts.admin')
@section('title', $service->name)
@section('breadcrumb', 'Admin / Services / View')
@section('heading', $service->name)
@section('content')
<div class="admin-card">
<p><strong>Slug:</strong> {{ $service->slug }}</p>
<p><strong>Category:</strong> {{ $service->category }}</p>
<p><strong>Short:</strong> {{ $service->short_description }}</p>
<p><strong>Full:</strong> {{ $service->full_description }}</p>
<p><strong>Features:</strong></p>
<ul>@foreach(($service->features ?? []) as $feature)<li>{{ $feature }}</li>@endforeach</ul>
@can('admin.services.manage')
<a class="admin-btn admin-btn-primary" href="{{ route('admin.services.edit', $service) }}">Edit</a>
<form method="POST" action="{{ route('admin.services.destroy', $service) }}" style="display:inline;">@csrf @method('DELETE')
<button class="admin-btn admin-btn-danger" data-confirm="Delete this service?" type="submit">Delete</button></form>
@endcan
</div>
@endsection
