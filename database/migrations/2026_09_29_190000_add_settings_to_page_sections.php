<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('page_sections')) {
            return;
        }

        Schema::table('page_sections', function (Blueprint $table) {
            if (! Schema::hasColumn('page_sections', 'settings')) {
                $table->json('settings')->nullable()->after('stats');
            }
        });
    }

    public function down(): void
    {
        if (! Schema::hasTable('page_sections')) {
            return;
        }

        Schema::table('page_sections', function (Blueprint $table) {
            if (Schema::hasColumn('page_sections', 'settings')) {
                $table->dropColumn('settings');
            }
        });
    }
};
