@extends('layouts.admin')
@section('title', 'Blog categories')
@section('breadcrumb', 'Admin / Blog categories')
@section('heading', 'Blog categories')
@section('content')
<div style="display:flex;justify-content:space-between;gap:1rem;margin-bottom:1rem;">
    <form method="GET"><input class="admin-input" name="q" value="{{ request('q') }}" placeholder="Search categories"></form>
    @can('admin.content.manage')<a class="admin-btn admin-btn-primary" href="{{ route('admin.blog-categories.create') }}">Add category</a>@endcan
</div>
<div class="admin-card admin-table-wrap">
<table class="admin-table">
<thead><tr><th>Name</th><th>Posts</th><th>Order</th><th>Status</th><th></th></tr></thead>
<tbody>
@forelse($categories as $category)
<tr>
<td><strong>{{ $category->name }}</strong><div style="font-size:.8rem;color:#64748b;">{{ $category->slug }}</div></td>
<td>{{ $category->posts_count }}</td>
<td>{{ $category->sort_order }}</td>
<td>{{ $category->is_active ? 'Active' : 'Inactive' }}</td>
<td style="white-space:nowrap;">
<a class="admin-btn admin-btn-ghost" href="{{ route('admin.blog-categories.show', $category) }}">View</a>
@can('admin.content.manage')<a class="admin-btn admin-btn-ghost" href="{{ route('admin.blog-categories.edit', $category) }}">Edit</a>@endcan
</td>
</tr>
@empty
<tr><td colspan="5"><div class="admin-empty">No categories yet.</div></td></tr>
@endforelse
</tbody>
</table>
{{ $categories->links() }}
</div>
@endsection
