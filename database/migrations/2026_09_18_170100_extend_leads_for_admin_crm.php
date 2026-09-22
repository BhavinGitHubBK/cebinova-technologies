<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('leads', function (Blueprint $table) {
            $table->timestamp('follow_up_at')->nullable()->after('status');
            $table->foreignId('assigned_to')->nullable()->after('follow_up_at')->constrained('users')->nullOnDelete();
            $table->softDeletes();
            $table->index('status');
            $table->index('service');
            $table->index('source');
            $table->index('created_at');
            $table->index(['name', 'phone']);
        });
    }

    public function down(): void
    {
        Schema::table('leads', function (Blueprint $table) {
            $table->dropForeign(['assigned_to']);
            $table->dropSoftDeletes();
            $table->dropColumn(['follow_up_at', 'assigned_to']);
        });
    }
};
