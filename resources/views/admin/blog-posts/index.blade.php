@extends('layouts.admin')
@section('title', 'Blog posts')
@section('breadcrumb', 'Admin / Blog posts')
@section('heading', 'Blog posts')
@section('content')
<div style="display:flex;justify-content:space-between;gap:1rem;margin-bottom:1rem;">
    <form method="GET" style="display:flex;gap:.5rem;">
        <input class="admin-input" name="q" value="{{ request('q') }}" placeholder="Search posts">
        <select class="admin-input" name="status">
            <option value="">All statuses</option>
            @foreach(['draft','published','scheduled'] as $status)
            <option value="{{ $status }}" @selected(request('status') === $status)>{{ ucfirst($status) }}</option>
            @endforeach
        </select>
        <button class="admin-btn admin-btn-ghost" type="submit">Filter</button>
    </form>
    @can('admin.content.manage')<a class="admin-btn admin-btn-primary" href="{{ route('admin.blog-posts.create') }}">Add post</a>@endcan
</div>
<div class="admin-card admin-table-wrap">
<table class="admin-table">
<thead><tr><th>Title</th><th>Category</th><th>Status</th><th>Published</th><th></th></tr></thead>
<tbody>
@forelse($posts as $post)
<tr>
<td><strong>{{ $post->title }}</strong><div style="font-size:.8rem;color:#64748b;">{{ $post->slug }}</div></td>
<td>{{ $post->category?->name }}</td>
<td>{{ $post->status }}</td>
<td>{{ optional($post->published_at)->format('Y-m-d H:i') }}</td>
<td style="white-space:nowrap;">
<a class="admin-btn admin-btn-ghost" href="{{ route('admin.blog-posts.show', $post) }}">View</a>
@can('admin.content.manage')<a class="admin-btn admin-btn-ghost" href="{{ route('admin.blog-posts.edit', $post) }}">Edit</a>@endcan
</td>
</tr>
@empty
<tr><td colspan="5"><div class="admin-empty">No posts yet.</div></td></tr>
@endforelse
</tbody>
</table>
{{ $posts->links() }}
</div>
@endsection
