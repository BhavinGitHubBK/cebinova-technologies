@extends('layouts.admin')
@section('title', 'Media')
@section('breadcrumb', 'Admin / Media')
@section('heading', 'Media library')
@section('content')
@can('admin.media.manage')
<form method="POST" action="{{ route('admin.media.store') }}" enctype="multipart/form-data" class="admin-card" style="margin-bottom:1rem;">
@csrf
<div class="admin-field"><label class="admin-label">Upload file</label><input class="admin-input" type="file" name="file" required></div>
<div class="admin-field"><label class="admin-label">Alt text</label><input class="admin-input" name="alt"></div>
<button class="admin-btn admin-btn-primary" type="submit">Upload</button>
@if(session('uploaded_url'))
<p>Uploaded URL: <code>{{ session('uploaded_url') }}</code>
<button type="button" class="admin-btn admin-btn-ghost" data-copy="{{ session('uploaded_url') }}">Copy URL</button></p>
@endif
</form>
@endcan
<form method="GET" style="margin-bottom:1rem;"><input class="admin-input" name="q" value="{{ request('q') }}" placeholder="Search media"></form>
<div style="display:grid;grid-template-columns:repeat(auto-fill,minmax(180px,1fr));gap:.85rem;">
@forelse($media as $item)
<div class="admin-card">
@if($item->isImage())
<img src="{{ $item->url() }}" alt="{{ $item->alt }}" style="width:100%;height:120px;object-fit:cover;border-radius:.5rem;">
@else
<div class="admin-empty">{{ $item->mime_type }}</div>
@endif
<p style="font-size:.8rem;word-break:break-all;">{{ $item->original_name }}</p>
<button type="button" class="admin-btn admin-btn-ghost" data-copy="{{ $item->url() }}">Copy URL</button>
@can('admin.media.manage')
<form method="POST" action="{{ route('admin.media.destroy', $item) }}" style="margin-top:.4rem;">@csrf @method('DELETE')
<button class="admin-btn admin-btn-danger" data-confirm="Delete this file?" type="submit">Delete</button></form>
@endcan
</div>
@empty
<div class="admin-empty">No media uploaded.</div>
@endforelse
</div>
{{ $media->links() }}
@endsection
