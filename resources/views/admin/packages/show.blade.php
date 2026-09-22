@extends('layouts.admin')
@section('title', $package->name)
@section('breadcrumb', 'Admin / Packages / View')
@section('heading', $package->name)
@section('content')
<div class="admin-card">
<p><strong>Category:</strong> {{ $package->category }}</p>
<p><strong>Teaser:</strong> {{ $package->teaser }}</p>
<p><strong>Active:</strong> {{ $package->is_active ? 'Yes' : 'No' }}</p>
<h3>Plans</h3>
<ul>
@foreach($package->plans as $plan)
<li>{{ $plan->label }} — ₹{{ number_format($plan->price) }} {{ $plan->period }}</li>
@endforeach
</ul>
@can('admin.packages.manage')
<a class="admin-btn admin-btn-primary" href="{{ route('admin.packages.edit', $package) }}">Edit</a>
<form method="POST" action="{{ route('admin.packages.destroy', $package) }}" style="display:inline;">@csrf @method('DELETE')
<button class="admin-btn admin-btn-danger" data-confirm="Delete package?" type="submit">Delete</button></form>
@endcan
</div>
@endsection
