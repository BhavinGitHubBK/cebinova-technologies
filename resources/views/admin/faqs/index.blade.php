@extends('layouts.admin')
@section('title', 'FAQs')
@section('breadcrumb', 'Admin / FAQs')
@section('heading', 'FAQs')
@section('content')
<div style="display:flex;justify-content:space-between;gap:1rem;margin-bottom:1rem;">
    <form method="GET" style="display:flex;gap:.5rem;">
        <input class="admin-input" name="q" value="{{ request('q') }}" placeholder="Search FAQs">
        <select class="admin-input" name="page">
            <option value="">All pages</option>
            @foreach($pages as $page)
            <option value="{{ $page }}" @selected(request('page') === $page)>{{ $page }}</option>
            @endforeach
        </select>
        <button class="admin-btn admin-btn-ghost" type="submit">Filter</button>
    </form>
    @can('admin.content.manage')<a class="admin-btn admin-btn-primary" href="{{ route('admin.faqs.create') }}">Add FAQ</a>@endcan
</div>
<div class="admin-card admin-table-wrap">
<table class="admin-table">
<thead><tr><th>Question</th><th>Page</th><th>Order</th><th>Status</th><th></th></tr></thead>
<tbody>
@forelse($faqs as $faq)
<tr>
<td><strong>{{ \Illuminate\Support\Str::limit($faq->question, 80) }}</strong></td>
<td>{{ $faq->page }}</td>
<td>{{ $faq->sort_order }}</td>
<td>{{ $faq->is_active ? 'Active' : 'Inactive' }}</td>
<td style="white-space:nowrap;">
<a class="admin-btn admin-btn-ghost" href="{{ route('admin.faqs.show', $faq) }}">View</a>
@can('admin.content.manage')<a class="admin-btn admin-btn-ghost" href="{{ route('admin.faqs.edit', $faq) }}">Edit</a>@endcan
</td>
</tr>
@empty
<tr><td colspan="5"><div class="admin-empty">No FAQs yet.</div></td></tr>
@endforelse
</tbody>
</table>
{{ $faqs->links() }}
</div>
@endsection
