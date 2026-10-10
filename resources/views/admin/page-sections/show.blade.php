@extends('layouts.admin')

@section('title', $section->heading ?: $section->key)
@section('breadcrumb', 'Admin / Page sections / View')
@section('heading', $section->heading ?: $section->key)

@section('content')
<section class="lead-board lead-detail">
    <div class="admin-card lead-detail-head">
        <div class="lead-detail-meta">
            <span class="admin-badge {{ $section->is_visible ? 'is-won' : 'is-off' }}">{{ $section->is_visible ? 'Visible' : 'Hidden' }}</span>
            <span>{{ $section->page }} / {{ $section->key }}</span>
        </div>
        <div class="lead-detail-actions">
            <a class="lead-contact-btn" href="{{ route('admin.page-sections.index') }}">All sections</a>
        </div>
    </div>
    <article class="admin-card record-sheet">
        @if (filled($section->body))
            <p class="record-lead">{{ $section->body }}</p>
        @endif
        <dl class="spec">
            <div><dt>Page</dt><dd>{{ $section->page }}</dd></div>
            <div><dt>Key</dt><dd>{{ $section->key }}</dd></div>
            <div><dt>Heading</dt><dd @class(['is-empty' => blank($section->heading)])>{{ $section->heading ?: '—' }}</dd></div>
            <div><dt>Subheading</dt><dd @class(['is-empty' => blank($section->subheading)])>{{ $section->subheading ?: '—' }}</dd></div>
            <div><dt>Button</dt><dd @class(['is-empty' => blank($section->cta_label)])>{{ $section->cta_label ?: '—' }}</dd></div>
            <div><dt>Button link</dt><dd @class(['is-empty' => blank($section->cta_url)])>{{ $section->cta_url ?: '—' }}</dd></div>
            <div><dt>Second button</dt><dd @class(['is-empty' => blank($section->secondary_cta_label)])>{{ $section->secondary_cta_label ?: '—' }}</dd></div>
            <div><dt>Second link</dt><dd @class(['is-empty' => blank($section->secondary_cta_url)])>{{ $section->secondary_cta_url ?: '—' }}</dd></div>
            <div><dt>Order</dt><dd>{{ $section->sort_order }}</dd></div>
            <div><dt>Image</dt><dd @class(['is-empty' => blank($section->image_path)])>{{ $section->image_path ?: '—' }}</dd></div>
        </dl>
        @can('admin.content.manage')
            <div class="record-actions">
                <a class="admin-btn admin-btn-primary" href="{{ route('admin.page-sections.edit', $section) }}">Edit</a>
                <form method="POST" action="{{ route('admin.page-sections.destroy', $section) }}">
                    @csrf @method('DELETE')
                    <button class="admin-btn admin-btn-danger" type="submit" data-confirm="Delete this section?">Delete</button>
                </form>
            </div>
        @endcan
    </article>
</section>
@endsection
