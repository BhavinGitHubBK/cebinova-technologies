@extends('layouts.admin')

@section('title', $industry->name)
@section('breadcrumb', 'Admin / Industries / View')
@section('heading', $industry->name)

@section('content')
<section class="lead-board lead-detail">
    <div class="admin-card lead-detail-head">
        <div class="lead-detail-meta">
            <span class="admin-badge {{ $industry->is_active ? 'is-won' : 'is-off' }}">{{ $industry->is_active ? 'Active' : 'Inactive' }}</span>
            @if ($industry->is_featured)<span class="solution-flag">Featured</span>@endif
            <span>{{ $industry->group ?: 'No group' }}</span>
        </div>
        <div class="lead-detail-actions">
            <a class="lead-contact-btn" href="{{ route('admin.industries.index') }}">All industries</a>
        </div>
    </div>
    <article class="admin-card record-sheet">
        @if (filled($industry->short_description))
            <p class="record-lead">{{ $industry->short_description }}</p>
        @endif
        <dl class="spec">
            <div><dt>Slug</dt><dd>{{ $industry->slug }}</dd></div>
            <div><dt>Group</dt><dd @class(['is-empty' => blank($industry->group)])>{{ $industry->group ?: '—' }}</dd></div>
            <div><dt>Order</dt><dd>{{ $industry->sort_order }}</dd></div>
            <div><dt>Solutions</dt><dd>{{ $industry->solutions->count() }}</dd></div>
            @if (filled($industry->description) && $industry->description !== $industry->short_description)
                <div><dt>Description</dt><dd>{{ $industry->description }}</dd></div>
            @endif
            <div>
                <dt>Linked solutions</dt>
                <dd @class(['is-empty' => $industry->solutions->isEmpty()])>
                    @if ($industry->solutions->isEmpty())
                        None linked yet
                    @else
                        <div class="record-chips">
                            @foreach ($industry->solutions as $solution)
                                <a href="{{ route('admin.solutions.show', $solution) }}">{{ $solution->name }}</a>
                            @endforeach
                        </div>
                    @endif
                </dd>
            </div>
        </dl>
        @can('admin.industries.manage')
            <div class="record-actions">
                <a class="admin-btn admin-btn-primary" href="{{ route('admin.industries.edit', $industry) }}">Edit</a>
                <form method="POST" action="{{ route('admin.industries.destroy', $industry) }}">
                    @csrf @method('DELETE')
                    <button class="admin-btn admin-btn-danger" type="submit" data-confirm="Archive this industry?">Archive</button>
                </form>
            </div>
        @endcan
    </article>
</section>
@endsection
