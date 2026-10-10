@extends('layouts.admin')
@section('title', $industry->exists ? 'Edit industry' : 'Create industry')
@section('breadcrumb', 'Admin / Industries / Form')
@section('heading', $industry->exists ? 'Edit industry' : 'Create industry')
@section('content')
<form method="POST" action="{{ $industry->exists ? route('admin.industries.update', $industry) : route('admin.industries.store') }}" class="admin-card admin-form">
    @csrf
    @if($industry->exists) @method('PUT') @endif

    <section class="form-block">
        <h2>Basics</h2>
        <div class="form-grid">
            <div class="admin-field">
                <label class="admin-label" for="industry-name">Name</label>
                <input id="industry-name" class="admin-input" name="name" value="{{ old('name', $industry->name) }}" required>
            </div>
            <div class="admin-field">
                <label class="admin-label" for="industry-slug">Slug</label>
                <input id="industry-slug" class="admin-input" name="slug" value="{{ old('slug', $industry->slug) }}">
            </div>
            <div class="admin-field">
                <label class="admin-label" for="industry-group">Group</label>
                <input id="industry-group" class="admin-input" name="group" value="{{ old('group', $industry->group) }}">
            </div>
            <div class="admin-field">
                <label class="admin-label" for="industry-order">Sort order</label>
                <input id="industry-order" class="admin-input" type="number" name="sort_order" value="{{ old('sort_order', $industry->sort_order ?? 0) }}">
            </div>
            <div class="admin-field is-wide">
                <label class="admin-label" for="industry-short">Short description</label>
                <textarea id="industry-short" class="admin-textarea" name="short_description" rows="{{ admin_rows(old('short_description', $industry->short_description), 2) }}" required>{{ old('short_description', $industry->short_description) }}</textarea>
            </div>
            <div class="admin-field is-wide">
                <label class="admin-label" for="industry-description">Description</label>
                <textarea id="industry-description" class="admin-textarea" name="description" rows="{{ admin_rows(old('description', $industry->description), 3) }}">{{ old('description', $industry->description) }}</textarea>
            </div>
        </div>
    </section>

    <section class="form-block">
        <h2>Solutions</h2>
        <div class="choice-grid">
            @foreach($solutions as $solution)
                <label>
                    <input type="checkbox" name="solution_ids[]" value="{{ $solution->id }}" @checked(in_array($solution->id, old('solution_ids', $industry->solutions->pluck('id')->all())))>
                    {{ $solution->name }}
                </label>
            @endforeach
        </div>
    </section>

    <div class="form-footer">
        <label class="admin-check"><input type="checkbox" name="is_featured" value="1" @checked(old('is_featured', $industry->is_featured))> Featured</label>
        <label class="admin-check"><input type="checkbox" name="is_active" value="1" @checked(old('is_active', $industry->is_active ?? true))> Active</label>
        <button class="admin-btn admin-btn-primary" type="submit">Save</button>
    </div>
</form>
@endsection
