@extends('layouts.admin')
@section('title', 'Page sections')
@section('breadcrumb', 'Admin / Page sections')
@section('heading', 'Page sections')
@section('content')
<div style="display:flex;justify-content:space-between;gap:1rem;margin-bottom:1rem;">
    <form method="GET" style="display:flex;gap:.5rem;">
        <input class="admin-input" name="q" value="{{ request('q') }}" placeholder="Search key or heading">
        <input class="admin-input" name="section_page" value="{{ request('section_page') }}" placeholder="Page filter">
        <button class="admin-btn admin-btn-ghost" type="submit">Filter</button>
    </form>
    @can('admin.content.manage')<a class="admin-btn admin-btn-primary" href="{{ route('admin.page-sections.create') }}">Add section</a>@endcan
</div>
<div class="admin-card admin-table-wrap">
<table class="admin-table">
<thead><tr><th>Page</th><th>Key</th><th>Heading</th><th>Visible</th><th></th></tr></thead>
<tbody>
@forelse($sections as $section)
<tr>
<td>{{ $section->page }}</td>
<td><strong>{{ $section->key }}</strong></td>
<td>{{ \Illuminate\Support\Str::limit($section->heading, 60) }}</td>
<td>{{ $section->is_visible ? 'Yes' : 'No' }}</td>
<td style="white-space:nowrap;">
<a class="admin-btn admin-btn-ghost" href="{{ route('admin.page-sections.show', $section) }}">View</a>
@can('admin.content.manage')<a class="admin-btn admin-btn-ghost" href="{{ route('admin.page-sections.edit', $section) }}">Edit</a>@endcan
</td>
</tr>
@empty
<tr><td colspan="5"><div class="admin-empty">No page sections yet.</div></td></tr>
@endforelse
</tbody>
</table>
{{ $sections->links() }}
</div>
@endsection
