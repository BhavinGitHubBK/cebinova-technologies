<?php

namespace Database\Seeders;

use App\Models\SiteSetting;
use Illuminate\Database\Seeder;

class SiteSettingSeeder extends Seeder
{
    public function run(): void
    {
        SiteSetting::query()->updateOrCreate(['id' => 1], [
            'company_name' => config('cebinova.name'),
            'tagline' => config('cebinova.tagline'),
            'email' => config('cebinova.contact.email'),
            'email_alt' => config('cebinova.contact.email_alt'),
            'phone' => config('cebinova.contact.phone'),
            'whatsapp' => config('cebinova.contact.whatsapp'),
            'address' => config('cebinova.contact.address'),
            'maps_url' => config('cebinova.contact.maps_url'),
            'facebook' => config('cebinova.contact.social.facebook'),
            'instagram' => config('cebinova.contact.social.instagram'),
            'linkedin' => config('cebinova.contact.social.linkedin'),
            'seo_title' => config('cebinova.seo.default_title'),
            'seo_description' => config('cebinova.seo.default_description'),
            'copyright_text' => '© '.date('Y').' CEBINOVA Technologies. All Rights Reserved.',
        ]);

        SiteSetting::clearCache();
    }
}
