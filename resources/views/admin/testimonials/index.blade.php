@extends('layouts.admin')
@section('title', 'Testimonials')
@section('breadcrumb', 'Admin / Testimonials')
@section('heading', 'Testimonials')
@section('content')
<div style="display:flex;justify-content:space-between;gap:1rem;margin-bottom:1rem;">
    <form method="GET"><input class="admin-input" name="q" value="{{ request('q') }}" placeholder="Search testimonials"></form>
    @can('admin.content.manage')<a class="admin-btn admin-btn-primary" href="{{ route('admin.testimonials.create') }}">Add testimonial</a>@endcan
</div>
<div class="admin-card admin-table-wrap">
<table class="admin-table">
<thead><tr><th>Customer</th><th>Company</th><th>Rating</th><th>Status</th><th></th></tr></thead>
<tbody>
@forelse($testimonials as $testimonial)
<tr>
<td><strong>{{ $testimonial->customer_name }}</strong></td>
<td>{{ $testimonial->company }}</td>
<td>{{ $testimonial->rating }}/5</td>
<td>{{ $testimonial->is_active ? 'Active' : 'Inactive' }}</td>
<td style="white-space:nowrap;">
<a class="admin-btn admin-btn-ghost" href="{{ route('admin.testimonials.show', $testimonial) }}">View</a>
@can('admin.content.manage')<a class="admin-btn admin-btn-ghost" href="{{ route('admin.testimonials.edit', $testimonial) }}">Edit</a>@endcan
</td>
</tr>
@empty
<tr><td colspan="5"><div class="admin-empty">No testimonials yet.</div></td></tr>
@endforelse
</tbody>
</table>
{{ $testimonials->links() }}
</div>
@endsection
