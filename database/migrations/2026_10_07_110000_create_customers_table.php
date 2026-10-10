<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('customers', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('mobile', 10)->unique();
            $table->foreignId('analytics_visitor_id')->nullable()->constrained('analytics_visitors')->nullOnDelete();
            $table->timestamp('last_login_at')->nullable();
            $table->string('last_login_ip', 45)->nullable();
            $table->timestamps();
        });

        $seen = [];

        DB::table('analytics_visitors')
            ->whereNotNull('mobile')
            ->where('mobile', '!=', '')
            ->orderBy('id')
            ->get(['id', 'name', 'mobile', 'first_seen_at', 'last_seen_at'])
            ->each(function (object $visitor) use (&$seen): void {
                if (isset($seen[$visitor->mobile])) {
                    return;
                }

                $seen[$visitor->mobile] = true;

                DB::table('customers')->insert([
                    'name' => $visitor->name ?: 'Customer',
                    'mobile' => $visitor->mobile,
                    'analytics_visitor_id' => $visitor->id,
                    'last_login_at' => $visitor->last_seen_at,
                    'created_at' => $visitor->first_seen_at ?? now(),
                    'updated_at' => now(),
                ]);
            });
    }

    public function down(): void
    {
        Schema::dropIfExists('customers');
    }
};
