@extends('layouts.admin')
@section('title', 'Settings')
@section('breadcrumb', 'Admin / Settings')
@section('heading', 'Website settings')
@section('content')
<form method="POST" action="{{ route('admin.settings.update') }}" class="admin-card">
@csrf @method('PUT')
<h3>General</h3>
<div class="admin-field"><label class="admin-label">Company name</label><input class="admin-input" name="company_name" value="{{ old('company_name', $settings->company_name) }}"></div>
<div class="admin-field"><label class="admin-label">Tagline</label><input class="admin-input" name="tagline" value="{{ old('tagline', $settings->tagline) }}"></div>
<div class="admin-field"><label class="admin-label">Logo path</label><input class="admin-input" name="logo_path" value="{{ old('logo_path', $settings->logo_path) }}"></div>
<div class="admin-field"><label class="admin-label">Favicon path</label><input class="admin-input" name="favicon_path" value="{{ old('favicon_path', $settings->favicon_path) }}"></div>
<div class="admin-field"><label class="admin-label">Email</label><input class="admin-input" name="email" value="{{ old('email', $settings->email) }}"></div>
<div class="admin-field"><label class="admin-label">Alt email</label><input class="admin-input" name="email_alt" value="{{ old('email_alt', $settings->email_alt) }}"></div>
<div class="admin-field"><label class="admin-label">Phone</label><input class="admin-input" name="phone" value="{{ old('phone', $settings->phone) }}"></div>
<div class="admin-field"><label class="admin-label">WhatsApp</label><input class="admin-input" name="whatsapp" value="{{ old('whatsapp', $settings->whatsapp) }}"></div>
<div class="admin-field"><label class="admin-label">Address</label><textarea class="admin-textarea" name="address" rows="2">{{ old('address', $settings->address) }}</textarea></div>
<div class="admin-field"><label class="admin-label">Maps URL</label><input class="admin-input" name="maps_url" value="{{ old('maps_url', $settings->maps_url) }}"></div>
<h3>Social</h3>
@foreach(['facebook','instagram','linkedin','youtube','twitter'] as $social)
<div class="admin-field"><label class="admin-label">{{ ucfirst($social) }}</label><input class="admin-input" name="{{ $social }}" value="{{ old($social, $settings->$social) }}"></div>
@endforeach
<h3>SEO</h3>
<div class="admin-field"><label class="admin-label">Default meta title</label><input class="admin-input" name="seo_title" value="{{ old('seo_title', $settings->seo_title) }}"></div>
<div class="admin-field"><label class="admin-label">Default meta description</label><textarea class="admin-textarea" name="seo_description" rows="2">{{ old('seo_description', $settings->seo_description) }}</textarea></div>
<div class="admin-field"><label class="admin-label">OG image path</label><input class="admin-input" name="og_image_path" value="{{ old('og_image_path', $settings->og_image_path) }}"></div>
<div class="admin-field"><label class="admin-label">Google Analytics ID</label><input class="admin-input" name="google_analytics_id" value="{{ old('google_analytics_id', $settings->google_analytics_id) }}"></div>
<div class="admin-field"><label class="admin-label">Search Console verification</label><input class="admin-input" name="google_search_console" value="{{ old('google_search_console', $settings->google_search_console) }}"></div>
<h3>Other</h3>
<div class="admin-field"><label class="admin-label">Map embed</label><textarea class="admin-textarea" name="contact_map_embed" rows="3">{{ old('contact_map_embed', $settings->contact_map_embed) }}</textarea></div>
<div class="admin-field"><label class="admin-label">Maintenance message</label><textarea class="admin-textarea" name="maintenance_message" rows="2">{{ old('maintenance_message', $settings->maintenance_message) }}</textarea></div>
<div class="admin-field"><label class="admin-label">Footer text</label><textarea class="admin-textarea" name="footer_text" rows="2">{{ old('footer_text', $settings->footer_text) }}</textarea></div>
<div class="admin-field"><label class="admin-label">Copyright text</label><input class="admin-input" name="copyright_text" value="{{ old('copyright_text', $settings->copyright_text) }}"></div>
@can('admin.settings.manage')
<button class="admin-btn admin-btn-primary" type="submit">Save settings</button>
@endcan
</form>
@endsection
