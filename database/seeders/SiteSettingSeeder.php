<?php

namespace Database\Seeders;

use App\Models\SiteSetting;
use Illuminate\Database\Seeder;

class SiteSettingSeeder extends Seeder
{
    public function run(): void
    {
        $contact = config('cebinova.contact', []);

        SiteSetting::query()->firstOrCreate([], [
            'phone' => $contact['phone'] ?? null,
            'whatsapp' => $contact['whatsapp'] ?? null,
            'email' => $contact['email'] ?? null,
            'email_alt' => $contact['email_alt'] ?? null,
            'address' => $contact['address'] ?? null,
            'maps_url' => $contact['maps_url'] ?? null,
            'linkedin' => data_get($contact, 'social.linkedin'),
            'instagram' => data_get($contact, 'social.instagram'),
            'facebook' => data_get($contact, 'social.facebook'),
        ]);
    }
}
