<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('site_settings')) {
            return;
        }

        $links = [
            'instagram' => 'https://www.instagram.com/cebinovatechnologies/',
            'linkedin' => 'https://www.linkedin.com/in/cebinova-technologies-31b44943a/',
            'facebook' => 'https://www.facebook.com/profile.php?id=61594302886895',
        ];

        $existing = DB::table('site_settings')->orderBy('id')->first();

        if ($existing) {
            DB::table('site_settings')->where('id', $existing->id)->update($links);
        } else {
            DB::table('site_settings')->insert($links + [
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }

        Cache::forget('site_settings');
    }

    public function down(): void
    {
        if (! Schema::hasTable('site_settings')) {
            return;
        }

        DB::table('site_settings')->update([
            'instagram' => null,
            'linkedin' => null,
            'facebook' => null,
        ]);

        Cache::forget('site_settings');
    }
};
