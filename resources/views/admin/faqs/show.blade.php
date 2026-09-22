@extends('layouts.admin')
@section('title', 'FAQ')
@section('breadcrumb', 'Admin / FAQs / View')
@section('heading', 'FAQ')
@section('content')
<div class="admin-card">
<p><strong>Page:</strong> {{ $faq->page }}</p>
<p><strong>Question:</strong> {{ $faq->question }}</p>
<p><strong>Answer:</strong> {{ $faq->answer }}</p>
@can('admin.content.manage')
<a class="admin-btn admin-btn-primary" href="{{ route('admin.faqs.edit', $faq) }}">Edit</a>
<form method="POST" action="{{ route('admin.faqs.destroy', $faq) }}" style="display:inline;">@csrf @method('DELETE')
<button class="admin-btn admin-btn-danger" data-confirm="Delete this FAQ?" type="submit">Delete</button></form>
@endcan
</div>
@endsection
