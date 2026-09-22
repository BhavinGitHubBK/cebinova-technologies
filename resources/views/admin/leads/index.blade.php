@extends('layouts.admin')

@section('title', 'Leads')
@section('breadcrumb', 'Admin / Leads')
@section('heading', 'Leads & enquiries')

@section('content')
<form method="GET" class="admin-card" style="margin-bottom:1rem;display:grid;grid-template-columns:repeat(auto-fit,minmax(140px,1fr));gap:.65rem;align-items:end;">
    <div class="admin-field" style="margin:0;"><label class="admin-label">Search</label><input class="admin-input" name="q" value="{{ $filters['q'] ?? '' }}" placeholder="Name, phone, email"></div>
    <div class="admin-field" style="margin:0;"><label class="admin-label">Status</label>
        <select class="admin-select" name="status"><option value="">All</option>@foreach($statuses as $status)<option value="{{ $status }}" @selected(($filters['status'] ?? '') === $status)>{{ $status }}</option>@endforeach</select>
    </div>
    <div class="admin-field" style="margin:0;"><label class="admin-label">Service</label><input class="admin-input" name="service" value="{{ $filters['service'] ?? '' }}"></div>
    <div class="admin-field" style="margin:0;"><label class="admin-label">Package</label><input class="admin-input" name="package_category" value="{{ $filters['package_category'] ?? '' }}"></div>
    <div class="admin-field" style="margin:0;"><label class="admin-label">Source</label><input class="admin-input" name="source" value="{{ $filters['source'] ?? '' }}"></div>
    <div class="admin-field" style="margin:0;"><label class="admin-label">From</label><input class="admin-input" type="date" name="from" value="{{ $filters['from'] ?? '' }}"></div>
    <div class="admin-field" style="margin:0;"><label class="admin-label">To</label><input class="admin-input" type="date" name="to" value="{{ $filters['to'] ?? '' }}"></div>
    <div style="display:flex;gap:.4rem;flex-wrap:wrap;">
        <button class="admin-btn admin-btn-primary" type="submit">Filter</button>
        <a class="admin-btn admin-btn-ghost" href="{{ route('admin.leads.export', request()->query()) }}">Export CSV</a>
    </div>
</form>

<form method="POST" action="{{ route('admin.leads.bulk-status') }}" class="admin-card">
    @csrf
    @can('admin.leads.manage')
    <div style="display:flex;gap:.5rem;flex-wrap:wrap;align-items:end;margin-bottom:.75rem;">
        <div class="admin-field" style="margin:0;min-width:160px;"><label class="admin-label">Bulk status</label>
            <select class="admin-select" name="status" required>@foreach($statuses as $status)<option value="{{ $status }}">{{ $status }}</option>@endforeach</select>
        </div>
        <button class="admin-btn admin-btn-accent" type="submit">Update selected</button>
    </div>
    @endcan
    <div class="admin-table-wrap">
        <table class="admin-table">
            <thead><tr>
                @can('admin.leads.manage')<th></th>@endcan
                <th>Name</th><th>Contact</th><th>Service</th><th>Status</th><th>Source</th><th>Created</th><th></th>
            </tr></thead>
            <tbody>
            @forelse ($leads as $lead)
                <tr>
                    @can('admin.leads.manage')
                        <td><input type="checkbox" name="ids[]" value="{{ $lead->id }}"></td>
                    @endcan
                    <td>
                        <a href="{{ route('admin.leads.show', $lead) }}"><strong>{{ $lead->name }}</strong></a>
                        <div style="font-size:.8rem;color:#64748b;">{{ $lead->business_name }}</div>
                    </td>
                    <td>
                        <a href="tel:{{ preg_replace('/\D+/', '', $lead->phone) }}">{{ $lead->phone }}</a><br>
                        @if($lead->email)<a href="mailto:{{ $lead->email }}">{{ $lead->email }}</a>@endif
                    </td>
                    <td>{{ $lead->service }}@if($lead->package_category)<div style="font-size:.8rem;color:#64748b;">{{ $lead->package_category }} / {{ $lead->plan_duration }}</div>@endif</td>
                    <td><span class="admin-badge">{{ $lead->status }}</span></td>
                    <td>{{ $lead->source }}</td>
                    <td>{{ $lead->created_at?->format('d M Y') }}</td>
                    <td><a class="admin-btn admin-btn-ghost" href="{{ route('admin.leads.show', $lead) }}">Open</a></td>
                </tr>
            @empty
                <tr><td colspan="8"><div class="admin-empty">No leads match your filters.</div></td></tr>
            @endforelse
            </tbody>
        </table>
    </div>
    {{ $leads->links() }}
</form>
@endsection
