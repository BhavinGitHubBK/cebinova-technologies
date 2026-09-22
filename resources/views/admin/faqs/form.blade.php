@extends('layouts.admin')
@section('title', $faq->exists ? 'Edit FAQ' : 'Create FAQ')
@section('breadcrumb', 'Admin / FAQs / Form')
@section('heading', $faq->exists ? 'Edit FAQ' : 'Create FAQ')
@section('content')
<form method="POST" action="{{ $faq->exists ? route('admin.faqs.update', $faq) : route('admin.faqs.store') }}" class="admin-card">
@csrf
@if($faq->exists) @method('PUT') @endif
<div class="admin-field"><label class="admin-label">Page</label>
<select class="admin-input" name="page" required>
@foreach($pages as $page)
<option value="{{ $page }}" @selected(old('page', $faq->page ?? 'general') === $page)>{{ $page }}</option>
@endforeach
</select></div>
<div class="admin-field"><label class="admin-label">Question</label><input class="admin-input" name="question" value="{{ old('question', $faq->question) }}" required></div>
<div class="admin-field"><label class="admin-label">Answer</label><textarea class="admin-textarea" name="answer" rows="5" required>{{ old('answer', $faq->answer) }}</textarea></div>
<div class="admin-field"><label class="admin-label">Sort order</label><input class="admin-input" type="number" name="sort_order" value="{{ old('sort_order', $faq->sort_order ?? 0) }}"></div>
<label style="display:flex;gap:.4rem;align-items:center;margin-bottom:.6rem;"><input type="checkbox" name="is_active" value="1" @checked(old('is_active', $faq->is_active ?? true))> Active</label>
<button class="admin-btn admin-btn-primary" type="submit">Save</button>
</form>
@endsection
