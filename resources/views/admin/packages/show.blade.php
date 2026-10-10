@extends('layouts.admin')

@section('title', $package->name)
@section('breadcrumb', $package->isMarketing() ? 'Admin / Marketing packages / View' : 'Admin / Website packages / View')
@section('heading', $package->name)

@section('content')
<section class="lead-board lead-detail" data-package-group="{{ $package->isMarketing() ? 'marketing' : 'website' }}">
    <div class="admin-card lead-detail-head">
        <div class="lead-detail-meta">
            <span class="admin-badge {{ $package->is_active ? 'is-won' : 'is-off' }}">{{ $package->is_active ? 'Active' : 'Inactive' }}</span>
            @if ($package->is_highlighted)<span class="solution-flag">Highlighted</span>@endif
            <span>{{ \App\Models\Package::CATEGORIES[$package->category] ?? $package->category }}</span>
        </div>
        <div class="lead-detail-actions">
            <a class="lead-contact-btn" href="{{ route('admin.packages.index') }}">All packages</a>
        </div>
    </div>
    <article class="admin-card record-sheet">
        @if (filled($package->teaser))
            <p class="record-lead">{{ $package->teaser }}</p>
        @endif
        <dl class="spec">
            <div><dt>Slug</dt><dd>{{ $package->slug }}</dd></div>
            <div><dt>Plans</dt><dd>{{ $package->plans->count() }}</dd></div>
            <div><dt>Category</dt><dd>{{ \App\Models\Package::CATEGORIES[$package->category] ?? $package->category }}</dd></div>
            <div><dt>Order</dt><dd>{{ $package->sort_order ?? '—' }}</dd></div>
            @if (filled($package->short_description))
                <div><dt>Short description</dt><dd>{{ $package->short_description }}</dd></div>
            @endif
        </dl>
        <div class="record-block">
            <h3>Plans</h3>
            @php
                [$planSort, $planDir] = admin_sort(request(), ['label', 'price', 'period', 'is_active'], 'sort_order');
                $plans = $planDir === 'desc' ? $package->plans->sortByDesc($planSort) : $package->plans->sortBy($planSort);
            @endphp
            @if ($plans->isEmpty())
                <p>No plans have been added.</p>
            @else
                <div class="admin-table-wrap">
                    <table class="admin-table lead-table">
                        <thead><tr>
                            <th><x-admin.sort column="label" label="Plan" :sort="$planSort" :dir="$planDir" /></th>
                            <th><x-admin.sort column="price" label="Price" :sort="$planSort" :dir="$planDir" /></th>
                            <th><x-admin.sort column="period" label="Period" :sort="$planSort" :dir="$planDir" /></th>
                            <th><x-admin.sort column="is_active" label="Status" :sort="$planSort" :dir="$planDir" /></th>
                        </tr></thead>
                        <tbody>
                        @foreach ($plans as $plan)
                            <tr>
                                <td>{{ $plan->label }}</td>
                                <td class="solution-num">₹{{ number_format((float) $plan->price) }}</td>
                                <td>{{ $plan->period ?: '—' }}</td>
                                <td><span class="admin-badge {{ $plan->is_active ? 'is-won' : 'is-off' }}">{{ $plan->is_active ? 'Active' : 'Inactive' }}</span></td>
                            </tr>
                        @endforeach
                        </tbody>
                    </table>
                </div>
            @endif
        </div>
        @can('admin.packages.manage')
            <div class="record-actions">
                <a class="admin-btn admin-btn-primary" href="{{ route('admin.packages.edit', $package) }}">Edit</a>
                <form method="POST" action="{{ route('admin.packages.destroy', $package) }}">
                    @csrf @method('DELETE')
                    <button class="admin-btn admin-btn-danger" type="submit" data-confirm="Delete this package?">Delete</button>
                </form>
            </div>
        @endcan
    </article>
</section>
@endsection
