<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasColumn('promotion_campaigns', 'popup_repeat_minutes')) {
            return;
        }

        Schema::table('promotion_campaigns', function (Blueprint $table) {
            $table->unsignedSmallInteger('popup_repeat_minutes')->default(1);
        });
    }

    public function down(): void
    {
        Schema::table('promotion_campaigns', function (Blueprint $table) {
            $table->dropColumn('popup_repeat_minutes');
        });
    }
};
