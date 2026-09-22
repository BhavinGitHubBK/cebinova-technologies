@extends('layouts.admin')
@section('title', $testimonial->exists ? 'Edit testimonial' : 'Create testimonial')
@section('breadcrumb', 'Admin / Testimonials / Form')
@section('heading', $testimonial->exists ? 'Edit testimonial' : 'Create testimonial')
@section('content')
<form method="POST" action="{{ $testimonial->exists ? route('admin.testimonials.update', $testimonial) : route('admin.testimonials.store') }}" class="admin-card">
@csrf
@if($testimonial->exists) @method('PUT') @endif
<div class="admin-field"><label class="admin-label">Customer name</label><input class="admin-input" name="customer_name" value="{{ old('customer_name', $testimonial->customer_name) }}" required></div>
<div class="admin-field"><label class="admin-label">Company</label><input class="admin-input" name="company" value="{{ old('company', $testimonial->company) }}"></div>
<div class="admin-field"><label class="admin-label">Position</label><input class="admin-input" name="position" value="{{ old('position', $testimonial->position) }}"></div>
<div class="admin-field"><label class="admin-label">Review</label><textarea class="admin-textarea" name="review" rows="5" required>{{ old('review', $testimonial->review) }}</textarea></div>
<div class="admin-field"><label class="admin-label">Rating (1-5)</label><input class="admin-input" type="number" min="1" max="5" name="rating" value="{{ old('rating', $testimonial->rating ?? 5) }}"></div>
<div class="admin-field"><label class="admin-label">Photo path/URL</label><input class="admin-input" name="photo" value="{{ old('photo', $testimonial->photo) }}"></div>
<div class="admin-field"><label class="admin-label">Sort order</label><input class="admin-input" type="number" name="sort_order" value="{{ old('sort_order', $testimonial->sort_order ?? 0) }}"></div>
<label style="display:flex;gap:.4rem;align-items:center;margin-bottom:.6rem;"><input type="checkbox" name="is_featured" value="1" @checked(old('is_featured', $testimonial->is_featured))> Featured</label>
<label style="display:flex;gap:.4rem;align-items:center;margin-bottom:.6rem;"><input type="checkbox" name="is_active" value="1" @checked(old('is_active', $testimonial->is_active ?? true))> Active</label>
<button class="admin-btn admin-btn-primary" type="submit">Save</button>
</form>
@endsection
