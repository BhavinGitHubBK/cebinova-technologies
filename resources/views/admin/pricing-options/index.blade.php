@extends('layouts.admin')
@section('title', 'Pricing Options')
@section('breadcrumb', 'Admin / Pricing / Options')
@section('heading', 'Pricing options')
@section('content')
<div style="display:flex;justify-content:space-between;gap:1rem;margin-bottom:1rem;flex-wrap:wrap;">
    <form method="GET" style="display:flex;gap:.5rem;">
        <select class="admin-select" name="type"><option value="">All option types</option>@foreach($types as $key => $label)<option value="{{ $key }}" @selected(request('type') === $key)>{{ $label }}</option>@endforeach</select>
        <button class="admin-btn admin-btn-ghost" type="submit">Filter</button>
    </form>
    @can('admin.packages.manage')<a class="admin-btn admin-btn-primary" href="{{ route('admin.pricing-options.create') }}">Add option</a>@endcan
</div>
<div class="admin-card admin-table-wrap"><table class="admin-table">
<thead><tr><th>Name</th><th>Type</th><th>Price</th><th>Billing</th><th>Status</th><th>Order</th><th></th></tr></thead>
<tbody>@forelse($options as $option)<tr>
<td><strong>{{ $option->name }}</strong></td><td>{{ $types[$option->type] }}</td><td>{{ cebinova_inr($option->price) }}</td><td>{{ $option->billing_period }}</td><td>{{ $option->is_active ? 'Active' : 'Inactive' }}</td><td>{{ $option->sort_order }}</td>
<td>@can('admin.packages.manage')<a class="admin-btn admin-btn-ghost" href="{{ route('admin.pricing-options.edit', $option) }}">Edit</a><form method="POST" action="{{ route('admin.pricing-options.destroy', $option) }}" style="display:inline">@csrf @method('DELETE')<button class="admin-btn admin-btn-danger" data-confirm="Archive this option?">Archive</button></form>@endcan</td>
</tr>@empty<tr><td colspan="7"><div class="admin-empty">No pricing options found.</div></td></tr>@endforelse</tbody>
</table>{{ $options->links() }}</div>
@endsection
