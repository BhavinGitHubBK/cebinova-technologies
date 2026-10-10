<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('promotion_campaigns')) {
            return;
        }

        $drop = ['show_brochure', 'brochure_path', 'popup_image_path', 'desktop_banner_path', 'mobile_banner_path'];
        $present = array_values(array_filter($drop, fn (string $column) => Schema::hasColumn('promotion_campaigns', $column)));
        if ($present === []) {
            return;
        }

        Schema::table('promotion_campaigns', function (Blueprint $table) use ($present) {
            $table->dropColumn($present);
        });
    }

    public function down(): void
    {
        Schema::table('promotion_campaigns', function (Blueprint $table) {
            $table->boolean('show_brochure')->default(false);
            $table->string('brochure_path')->nullable();
            $table->string('popup_image_path')->nullable();
            $table->string('desktop_banner_path')->nullable();
            $table->string('mobile_banner_path')->nullable();
        });
    }
};
