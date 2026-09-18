<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('marketing_packages', function (Blueprint $table) {
            $table->string('badge')->nullable()->after('service');
            $table->string('cta')->nullable()->after('badge');
            $table->string('secondary_cta')->nullable()->after('cta');
            $table->text('why')->nullable()->after('secondary_cta');
        });
    }

    public function down(): void
    {
        Schema::table('marketing_packages', function (Blueprint $table) {
            $table->dropColumn(['badge', 'cta', 'secondary_cta', 'why']);
        });
    }
};
