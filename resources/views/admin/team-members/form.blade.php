@extends('layouts.admin')
@section('title', $member->exists ? 'Edit team member' : 'Create team member')
@section('breadcrumb', 'Admin / Team / Form')
@section('heading', $member->exists ? 'Edit team member' : 'Create team member')
@section('content')
<form method="POST" action="{{ $member->exists ? route('admin.team-members.update', $member) : route('admin.team-members.store') }}" class="admin-card">
@csrf
@if($member->exists) @method('PUT') @endif
<div class="admin-field"><label class="admin-label">Name</label><input class="admin-input" name="name" value="{{ old('name', $member->name) }}" required></div>
<div class="admin-field"><label class="admin-label">Designation</label><input class="admin-input" name="designation" value="{{ old('designation', $member->designation) }}"></div>
<div class="admin-field"><label class="admin-label">Bio</label><textarea class="admin-textarea" name="bio" rows="4">{{ old('bio', $member->bio) }}</textarea></div>
<div class="admin-field"><label class="admin-label">Photo path/URL</label><input class="admin-input" name="photo" value="{{ old('photo', $member->photo) }}"></div>
<div class="admin-field"><label class="admin-label">Email</label><input class="admin-input" type="email" name="email" value="{{ old('email', $member->email) }}"></div>
<div class="admin-field"><label class="admin-label">Phone</label><input class="admin-input" name="phone" value="{{ old('phone', $member->phone) }}"></div>
<div class="admin-field"><label class="admin-label">LinkedIn URL</label><input class="admin-input" name="linkedin_url" value="{{ old('linkedin_url', $member->linkedin_url) }}"></div>
<div class="admin-field"><label class="admin-label">Sort order</label><input class="admin-input" type="number" name="sort_order" value="{{ old('sort_order', $member->sort_order ?? 0) }}"></div>
<label style="display:flex;gap:.4rem;align-items:center;margin-bottom:.6rem;"><input type="checkbox" name="is_active" value="1" @checked(old('is_active', $member->is_active ?? true))> Active</label>
<button class="admin-btn admin-btn-primary" type="submit">Save</button>
</form>
@endsection
