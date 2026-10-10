@extends('layouts.admin')

@section('title', $service->name)
@section('breadcrumb', 'Admin / Services / View')
@section('heading', $service->name)

@section('content')
<section class="lead-board lead-detail">
    <div class="admin-card lead-detail-head">
        <div class="lead-detail-meta">
            <span class="admin-badge {{ $service->is_active ? 'is-won' : 'is-off' }}">{{ $service->is_active ? 'Active' : 'Inactive' }}</span>
            @if ($service->is_featured)<span class="solution-flag">Featured</span>@endif
            <span>{{ $service->category ?: 'No category' }}</span>
        </div>
        <div class="lead-detail-actions">
            <a class="lead-contact-btn" href="{{ route('admin.services.index') }}">All services</a>
        </div>
    </div>
    <article class="admin-card record-sheet">
        @if (filled($service->short_description))
            <p class="record-lead">{{ $service->short_description }}</p>
        @endif
        <dl class="spec">
            <div><dt>Slug</dt><dd>{{ $service->slug }}</dd></div>
            <div><dt>Category</dt><dd @class(['is-empty' => blank($service->category)])>{{ $service->category ?: '—' }}</dd></div>
            <div><dt>Order</dt><dd>{{ $service->sort_order }}</dd></div>
            <div><dt>Icon</dt><dd @class(['is-empty' => blank($service->icon)])>{{ $service->icon ?: '—' }}</dd></div>
            @if (filled($service->full_description))
                <div><dt>Full description</dt><dd>{{ $service->full_description }}</dd></div>
            @endif
            @if (filled($service->cta_label) || filled($service->cta_url))
                <div><dt>Button</dt><dd>{{ $service->cta_label ?: '—' }}@if (filled($service->cta_url)) · {{ $service->cta_url }}@endif</dd></div>
            @endif
            @if (count($service->features ?? []))
                <div>
                    <dt>Features</dt>
                    <dd>
                        <ul class="spec-notes">
                            @foreach ($service->features as $feature)
                                <li><strong>{{ $feature }}</strong></li>
                            @endforeach
                        </ul>
                    </dd>
                </div>
            @endif
        </dl>
        @can('admin.services.manage')
            <div class="record-actions">
                <a class="admin-btn admin-btn-primary" href="{{ route('admin.services.edit', $service) }}">Edit</a>
                <form method="POST" action="{{ route('admin.services.destroy', $service) }}">
                    @csrf @method('DELETE')
                    <button class="admin-btn admin-btn-danger" type="submit" data-confirm="Delete this service?">Delete</button>
                </form>
            </div>
        @endcan
    </article>
</section>
@endsection
