@extends('layouts.admin')

@section('title', $solution->name)
@section('breadcrumb', 'Admin / Solutions / View')
@section('heading', $solution->name)

@section('content')
<section class="lead-board lead-detail">
    <div class="admin-card lead-detail-head">
        <div class="lead-detail-meta">
            <span class="admin-badge {{ $solution->is_active ? 'is-won' : 'is-off' }}">{{ $solution->is_active ? 'Active' : 'Inactive' }}</span>
            @if ($solution->is_featured)<span class="solution-flag">Featured</span>@endif
            <span>{{ $solution->category ?: 'No category' }}</span>
        </div>
        <div class="lead-detail-actions">
            <a class="lead-contact-btn" href="{{ route('admin.solutions.index') }}">All solutions</a>
        </div>
    </div>
    <article class="admin-card record-sheet">
        @if (filled($solution->short_description))
            <p class="record-lead">{{ $solution->short_description }}</p>
        @endif
        <dl class="spec">
            <div><dt>Slug</dt><dd>{{ $solution->slug }}</dd></div>
            <div><dt>Category</dt><dd @class(['is-empty' => blank($solution->category)])>{{ $solution->category ?: '—' }}</dd></div>
            <div><dt>Order</dt><dd>{{ $solution->sort_order }}</dd></div>
            <div><dt>Services</dt><dd>{{ $solution->services->count() }}</dd></div>
            @if (filled($solution->description) && $solution->description !== $solution->short_description)
                <div><dt>Description</dt><dd>{{ $solution->description }}</dd></div>
            @endif
            @if (filled($solution->audience))
                <div><dt>Audience</dt><dd>{{ $solution->audience }}</dd></div>
            @endif
            @if (filled($solution->outcome))
                <div><dt>Outcome</dt><dd>{{ $solution->outcome }}</dd></div>
            @endif
            @php $featureSection = $solution->sections->firstWhere('type', 'features'); @endphp
            @if ($featureSection && $featureSection->items->isNotEmpty())
                <div>
                    <dt>Features</dt>
                    <dd>
                        <ul class="spec-notes">
                            @foreach ($featureSection->items as $item)
                                <li>
                                    <strong>{{ $item->title }}</strong>
                                    @if (filled($item->description))<span>{{ $item->description }}</span>@endif
                                </li>
                            @endforeach
                        </ul>
                    </dd>
                </div>
            @endif
            <div>
                <dt>Linked services</dt>
                <dd @class(['is-empty' => $solution->services->isEmpty()])>
                    @if ($solution->services->isEmpty())
                        None linked yet
                    @else
                        <div class="record-chips">
                            @foreach ($solution->services as $service)
                                <a href="{{ route('admin.services.show', $service) }}">{{ $service->name }}</a>
                            @endforeach
                        </div>
                    @endif
                </dd>
            </div>
            <div>
                <dt>Linked industries</dt>
                <dd @class(['is-empty' => $solution->industries->isEmpty()])>
                    @if ($solution->industries->isEmpty())
                        None linked yet
                    @else
                        <div class="record-chips">
                            @foreach ($solution->industries as $industry)
                                <a href="{{ route('admin.industries.show', $industry) }}">{{ $industry->name }}</a>
                            @endforeach
                        </div>
                    @endif
                </dd>
            </div>
        </dl>
        @can('admin.solutions.manage')
            <div class="record-actions">
                <a class="admin-btn admin-btn-primary" href="{{ route('admin.solutions.edit', $solution) }}">Edit</a>
                <form method="POST" action="{{ route('admin.solutions.destroy', $solution) }}">
                    @csrf @method('DELETE')
                    <button class="admin-btn admin-btn-danger" type="submit" data-confirm="Archive this solution?">Archive</button>
                </form>
            </div>
        @endcan
    </article>
</section>
@endsection
