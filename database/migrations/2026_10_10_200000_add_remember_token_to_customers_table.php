<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasColumn('customers', 'remember_token')) {
            return;
        }

        Schema::table('customers', function (Blueprint $table) {
            $table->rememberToken()->after('last_login_ip');
        });
    }

    public function down(): void
    {
        if (! Schema::hasColumn('customers', 'remember_token')) {
            return;
        }

        Schema::table('customers', function (Blueprint $table) {
            $table->dropColumn('remember_token');
        });
    }
};
