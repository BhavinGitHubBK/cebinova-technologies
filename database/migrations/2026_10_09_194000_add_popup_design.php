<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasColumn('promotion_campaigns', 'popup_design')) {
            return;
        }

        Schema::table('promotion_campaigns', function (Blueprint $table) {
            $table->string('popup_design', 20)->default('split');
        });
    }

    public function down(): void
    {
        if (! Schema::hasColumn('promotion_campaigns', 'popup_design')) {
            return;
        }

        Schema::table('promotion_campaigns', function (Blueprint $table) {
            $table->dropColumn('popup_design');
        });
    }
};
