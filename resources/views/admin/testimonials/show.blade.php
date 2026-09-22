@extends('layouts.admin')
@section('title', $testimonial->customer_name)
@section('breadcrumb', 'Admin / Testimonials / View')
@section('heading', $testimonial->customer_name)
@section('content')
<div class="admin-card">
<p><strong>Company:</strong> {{ $testimonial->company }}</p>
<p><strong>Position:</strong> {{ $testimonial->position }}</p>
<p><strong>Rating:</strong> {{ $testimonial->rating }}/5</p>
<p>{{ $testimonial->review }}</p>
@can('admin.content.manage')
<a class="admin-btn admin-btn-primary" href="{{ route('admin.testimonials.edit', $testimonial) }}">Edit</a>
<form method="POST" action="{{ route('admin.testimonials.destroy', $testimonial) }}" style="display:inline;">@csrf @method('DELETE')
<button class="admin-btn admin-btn-danger" data-confirm="Delete this testimonial?" type="submit">Delete</button></form>
@endcan
</div>
@endsection
