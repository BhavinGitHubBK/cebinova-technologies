@extends('layouts.admin')

@section('title', 'Customers')
@section('breadcrumb', 'Admin / Customers')
@section('heading', 'Website customers')

@section('content')
<section class="lead-board">
    <div class="lead-summary">
        <p><strong>{{ $customers->total() }}</strong><span>signed in with a name and mobile</span></p>
    </div>

    <div class="admin-card" style="margin-bottom:1rem;">
        <form method="GET" action="{{ route('admin.customers.index') }}" class="solution-search" style="display:flex;gap:.75rem;align-items:end;flex-wrap:wrap;">
            <div class="admin-field" style="min-width:16rem;flex:1;">
                <label class="admin-label" for="customer-search">Search</label>
                <input id="customer-search" class="admin-input" name="q" value="{{ $search }}" placeholder="Name or mobile">
            </div>
            <button class="admin-btn" type="submit">Search</button>
            @if ($search !== '')
                <a class="admin-btn admin-btn-ghost" href="{{ route('admin.customers.index') }}">Clear</a>
            @endif
        </form>
    </div>

    <div class="admin-card admin-table-wrap">
        <table class="admin-table lead-table visitor-table">
            <thead>
                <tr>
                    <th>Name</th>
                    <th>Mobile</th>
                    <th>Location</th>
                    <th>Last login</th>
                </tr>
            </thead>
            <tbody>
                @forelse ($customers as $customer)
                    <tr>
                        <td>
                            @if ($customer->visitor)
                                <a href="{{ route('admin.analytics.visitors.show', $customer->visitor) }}">{{ $customer->name }}</a>
                            @else
                                {{ $customer->name }}
                            @endif
                        </td>
                        <td>{{ $customer->mobileDisplay() }}</td>
                        <td class="visitor-place">{{ $customer->visitor?->place() ?? '—' }}</td>
                        <td>{{ ist($customer->last_login_at) }}</td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="4">No customers yet. Someone appears here after they enter a name and mobile on the website.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <div style="margin-top:1rem;">{{ $customers->links() }}</div>
</section>
@endsection
