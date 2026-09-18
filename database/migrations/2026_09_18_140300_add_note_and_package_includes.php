<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('marketing_packages', function (Blueprint $table) {
            $table->json('includes')->nullable()->after('why');
        });

        Schema::table('marketing_plans', function (Blueprint $table) {
            $table->text('note')->nullable()->after('cta');
        });
    }

    public function down(): void
    {
        Schema::table('marketing_packages', function (Blueprint $table) {
            $table->dropColumn('includes');
        });

        Schema::table('marketing_plans', function (Blueprint $table) {
            $table->dropColumn('note');
        });
    }
};
