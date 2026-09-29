<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('leads', function (Blueprint $table) {
            $table->string('page_url', 2048)->nullable()->after('source');
            $table->timestamp('contacted_at')->nullable()->after('follow_up_at');
            $table->timestamp('qualified_at')->nullable()->after('contacted_at');
            $table->timestamp('proposal_sent_at')->nullable()->after('qualified_at');
            $table->timestamp('won_at')->nullable()->after('proposal_sent_at');
            $table->timestamp('lost_at')->nullable()->after('won_at');
        });

        DB::table('leads')->where('status', 'Follow-up')->update(['status' => 'Qualified']);
        DB::table('leads')->where('status', 'Converted')->update(['status' => 'Won']);
    }

    public function down(): void
    {
        DB::table('leads')->where('status', 'Qualified')->update(['status' => 'Follow-up']);
        DB::table('leads')->where('status', 'Won')->update(['status' => 'Converted']);

        Schema::table('leads', function (Blueprint $table) {
            $table->dropColumn([
                'page_url',
                'contacted_at',
                'qualified_at',
                'proposal_sent_at',
                'won_at',
                'lost_at',
            ]);
        });
    }
};
