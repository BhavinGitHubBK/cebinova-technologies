@extends('layouts.admin')
@section('title', 'Settings')
@section('breadcrumb', 'Admin / Settings')
@section('heading', 'Website settings')
@section('content')
<p class="page-note">These details appear across the website: the header, contact page, footer, and search results.</p>
<form method="POST" action="{{ route('admin.settings.update') }}" class="admin-form">
    @csrf
    @method('PUT')

    <section class="admin-card form-block">
        <h2>Company</h2>
        <p class="form-hint">Name and mark used in the header and page titles.</p>
        <div class="form-grid">
            <div class="admin-field">
                <label class="admin-label" for="set-company">Company name</label>
                <input id="set-company" class="admin-input" name="company_name" value="{{ old('company_name', $settings->company_name) }}" required>
            </div>
            <div class="admin-field">
                <label class="admin-label" for="set-tagline">Tagline</label>
                <input id="set-tagline" class="admin-input" name="tagline" value="{{ old('tagline', $settings->tagline) }}">
            </div>
            <div class="admin-field">
                <label class="admin-label" for="set-logo">Logo path</label>
                <input id="set-logo" class="admin-input" name="logo_path" value="{{ old('logo_path', $settings->logo_path) }}" placeholder="/assets/brand/logo.svg">
            </div>
            <div class="admin-field">
                <label class="admin-label" for="set-favicon">Favicon path</label>
                <input id="set-favicon" class="admin-input" name="favicon_path" value="{{ old('favicon_path', $settings->favicon_path) }}" placeholder="/favicon.ico">
            </div>
        </div>
    </section>

    <section class="admin-card form-block">
        <h2>Contact</h2>
        <p class="form-hint">Shown on the contact page and used by the enquiry buttons.</p>
        <div class="form-grid">
            <div class="admin-field">
                <label class="admin-label" for="set-lead-email">Lead alert email</label>
                <input id="set-lead-email" class="admin-input" type="email" name="lead_notify_email" value="{{ old('lead_notify_email', $settings->lead_notify_email) }}" placeholder="{{ $settings->email }}">
                <p class="form-hint">New enquiries are emailed here. Leave blank to use the company email.</p>
            </div>
            <div class="admin-field">
                <label class="admin-label" for="set-email">Email</label>
                <input id="set-email" class="admin-input" type="email" name="email" value="{{ old('email', $settings->email) }}" required>
            </div>
            <div class="admin-field">
                <label class="admin-label" for="set-email-alt">Alt email</label>
                <input id="set-email-alt" class="admin-input" type="email" name="email_alt" value="{{ old('email_alt', $settings->email_alt) }}">
            </div>
            <div class="admin-field">
                <label class="admin-label" for="set-phone">Phone</label>
                <input id="set-phone" class="admin-input" name="phone" value="{{ old('phone', $settings->phone) }}" required>
            </div>
            <div class="admin-field">
                <label class="admin-label" for="set-whatsapp">WhatsApp</label>
                <input id="set-whatsapp" class="admin-input" name="whatsapp" value="{{ old('whatsapp', $settings->whatsapp) }}" placeholder="9190...">
            </div>
            <div class="admin-field is-wide">
                <label class="admin-label" for="set-address">Address</label>
                <textarea id="set-address" class="admin-textarea" name="address" rows="{{ admin_rows(old('address', $settings->address), 2) }}">{{ old('address', $settings->address) }}</textarea>
            </div>
            <div class="admin-field is-wide">
                <label class="admin-label" for="set-maps">Maps URL</label>
                <textarea id="set-maps" class="admin-textarea" name="maps_url" rows="{{ admin_rows(old('maps_url', $settings->maps_url), 2) }}">{{ old('maps_url', $settings->maps_url) }}</textarea>
            </div>
            <div class="admin-field is-wide">
                <label class="admin-label" for="set-map">Map embed</label>
                <textarea id="set-map" class="admin-textarea" name="contact_map_embed" rows="{{ admin_rows(old('contact_map_embed', $settings->contact_map_embed), 3) }}">{{ old('contact_map_embed', $settings->contact_map_embed) }}</textarea>
                <p class="form-hint">Paste the map iframe. Leave it empty to keep the current map.</p>
            </div>
        </div>
    </section>

    <section class="admin-card form-block" id="gst">
        <h2>GST</h2>
        <p class="form-hint">This rate is shown on the pricing calculator as extra. It is not added into the estimated project cost.</p>
        <div class="form-grid">
            <div class="admin-field">
                <label class="admin-label" for="set-gst">GST percent</label>
                <input id="set-gst" class="admin-input" type="number" min="0" max="100" step="1" name="gst_percent" value="{{ old('gst_percent', $settings->gst_percent ?? 18) }}" required>
                <p class="form-hint">Use 18 for the standard rate, or 0 when a quote should say GST is not added.</p>
            </div>
        </div>
    </section>

    <section class="admin-card form-block">
        <h2>Social</h2>
        <p class="form-hint">Full profile links. Leave a field empty when that profile is not ready.</p>
        <div class="form-grid">
            @foreach (['facebook' => 'Facebook', 'instagram' => 'Instagram', 'linkedin' => 'LinkedIn', 'youtube' => 'YouTube', 'twitter' => 'X / Twitter'] as $social => $label)
                <div class="admin-field">
                    <label class="admin-label" for="set-{{ $social }}">{{ $label }}</label>
                    <input id="set-{{ $social }}" class="admin-input" name="{{ $social }}" value="{{ old($social, $settings->$social) }}" placeholder="https://">
                </div>
            @endforeach
        </div>
    </section>

    <section class="admin-card form-block">
        <h2>Search</h2>
        <p class="form-hint">Used when a page does not set its own title or description.</p>
        <div class="form-grid">
            <div class="admin-field">
                <label class="admin-label" for="set-seo-title">Default meta title</label>
                <input id="set-seo-title" class="admin-input" name="seo_title" value="{{ old('seo_title', $settings->seo_title) }}">
            </div>
            <div class="admin-field">
                <label class="admin-label" for="set-og">OG image path</label>
                <input id="set-og" class="admin-input" name="og_image_path" value="{{ old('og_image_path', $settings->og_image_path) }}" placeholder="/assets/brand/share.png">
            </div>
            <div class="admin-field is-wide">
                <label class="admin-label" for="set-seo">Default meta description</label>
                <textarea id="set-seo" class="admin-textarea" name="seo_description" rows="{{ admin_rows(old('seo_description', $settings->seo_description), 2) }}">{{ old('seo_description', $settings->seo_description) }}</textarea>
            </div>
            <div class="admin-field">
                <label class="admin-label" for="set-ga">Google Analytics ID</label>
                <input id="set-ga" class="admin-input" name="google_analytics_id" value="{{ old('google_analytics_id', $settings->google_analytics_id) }}" placeholder="G-XXXXXXXX">
            </div>
            <div class="admin-field">
                <label class="admin-label" for="set-gsc">Search Console verification</label>
                <input id="set-gsc" class="admin-input" name="google_search_console" value="{{ old('google_search_console', $settings->google_search_console) }}">
            </div>
        </div>
    </section>

    <section class="admin-card form-block">
        <h2>Footer</h2>
        <p class="form-hint">Shown at the bottom of every page.</p>
        <div class="form-grid">
            <div class="admin-field">
                <label class="admin-label" for="set-footer">Footer text</label>
                <textarea id="set-footer" class="admin-textarea" name="footer_text" rows="{{ admin_rows(old('footer_text', $settings->footer_text), 2) }}">{{ old('footer_text', $settings->footer_text) }}</textarea>
            </div>
            <div class="admin-field">
                <label class="admin-label" for="set-maint">Maintenance message</label>
                <textarea id="set-maint" class="admin-textarea" name="maintenance_message" rows="{{ admin_rows(old('maintenance_message', $settings->maintenance_message), 2) }}">{{ old('maintenance_message', $settings->maintenance_message) }}</textarea>
            </div>
            <div class="admin-field is-wide">
                <label class="admin-label" for="set-copy">Copyright text</label>
                <textarea id="set-copy" class="admin-textarea" name="copyright_text" rows="{{ admin_rows(old('copyright_text', $settings->copyright_text), 2) }}">{{ old('copyright_text', $settings->copyright_text) }}</textarea>
            </div>
        </div>
        @can('admin.settings.manage')
            <div class="form-footer">
                <button class="admin-btn admin-btn-primary" type="submit">Save settings</button>
            </div>
        @endcan
    </section>
</form>
@endsection
