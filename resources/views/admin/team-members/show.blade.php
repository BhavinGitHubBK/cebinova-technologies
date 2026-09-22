@extends('layouts.admin')
@section('title', $member->name)
@section('breadcrumb', 'Admin / Team / View')
@section('heading', $member->name)
@section('content')
<div class="admin-card">
<p><strong>Designation:</strong> {{ $member->designation }}</p>
<p><strong>Email:</strong> {{ $member->email }}</p>
<p><strong>Phone:</strong> {{ $member->phone }}</p>
<p>{{ $member->bio }}</p>
@can('admin.content.manage')
<a class="admin-btn admin-btn-primary" href="{{ route('admin.team-members.edit', $member) }}">Edit</a>
<form method="POST" action="{{ route('admin.team-members.destroy', $member) }}" style="display:inline;">@csrf @method('DELETE')
<button class="admin-btn admin-btn-danger" data-confirm="Delete this team member?" type="submit">Delete</button></form>
@endcan
</div>
@endsection
