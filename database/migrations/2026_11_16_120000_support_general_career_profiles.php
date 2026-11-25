<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('career_applications', function (Blueprint $table) {
            $table->unsignedBigInteger('career_job_id')->nullable()->change();
            $table->string('phone', 40)->nullable()->change();
            $table->string('area_of_interest', 190)->nullable();
        });
    }

    public function down(): void
    {
        // Retain nullable columns so existing general profiles remain intact.
        Schema::table('career_applications', fn (Blueprint $table) => $table->dropColumn('area_of_interest'));
    }
};
