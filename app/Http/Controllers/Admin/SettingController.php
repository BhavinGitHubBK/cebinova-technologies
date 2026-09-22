<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\SiteSetting;
use App\Services\Admin\ActivityLogger;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class SettingController extends Controller
{
    public function edit(): View
    {
        return view('admin.settings.edit', [
            'settings' => SiteSetting::current(),
        ]);
    }

    public function update(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'company_name' => ['nullable', 'string', 'max:190'],
            'tagline' => ['nullable', 'string', 'max:255'],
            'logo_path' => ['nullable', 'string', 'max:255'],
            'favicon_path' => ['nullable', 'string', 'max:255'],
            'email' => ['nullable', 'email', 'max:190'],
            'email_alt' => ['nullable', 'email', 'max:190'],
            'phone' => ['nullable', 'string', 'max:40'],
            'whatsapp' => ['nullable', 'string', 'max:40'],
            'address' => ['nullable', 'string', 'max:1000'],
            'maps_url' => ['nullable', 'string', 'max:500'],
            'facebook' => ['nullable', 'string', 'max:255'],
            'instagram' => ['nullable', 'string', 'max:255'],
            'linkedin' => ['nullable', 'string', 'max:255'],
            'youtube' => ['nullable', 'string', 'max:255'],
            'twitter' => ['nullable', 'string', 'max:255'],
            'seo_title' => ['nullable', 'string', 'max:190'],
            'seo_description' => ['nullable', 'string', 'max:500'],
            'og_image_path' => ['nullable', 'string', 'max:255'],
            'google_analytics_id' => ['nullable', 'string', 'max:80'],
            'google_search_console' => ['nullable', 'string', 'max:255'],
            'contact_map_embed' => ['nullable', 'string'],
            'maintenance_message' => ['nullable', 'string'],
            'footer_text' => ['nullable', 'string'],
            'copyright_text' => ['nullable', 'string', 'max:255'],
        ]);

        $settings = SiteSetting::current();
        $settings->update($data);
        SiteSetting::clearCache();
        SiteSetting::applyToConfig();
        ActivityLogger::log('update', 'settings', $settings, 'Site settings updated');

        return back()->with('success', 'Settings saved.');
    }
}
