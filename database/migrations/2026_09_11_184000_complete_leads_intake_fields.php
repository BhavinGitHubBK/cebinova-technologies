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
            if (! Schema::hasColumn('leads', 'whatsapp')) {
                $table->string('whatsapp')->nullable();
            }
            if (! Schema::hasColumn('leads', 'city')) {
                $table->string('city')->nullable();
            }
            if (! Schema::hasColumn('leads', 'selected_price')) {
                $table->string('selected_price')->nullable();
            }
            if (! Schema::hasColumn('leads', 'notes')) {
                $table->text('notes')->nullable();
            }
            if (! Schema::hasColumn('leads', 'free_consultation')) {
                $table->boolean('free_consultation')->default(false);
            }
        });

        Schema::table('leads', function (Blueprint $table) {
            $table->string('business_name')->nullable()->change();
            $table->string('email')->nullable()->change();
        });

        foreach (DB::table('leads')->get() as $lead) {
            DB::table('leads')->where('id', $lead->id)->update([
                'selected_price' => $lead->selected_price ?: ($lead->plan_price ?? null),
                'free_consultation' => (int) ($lead->free_consultation ?: ($lead->consultation_requested ?? 0)),
                'status' => $lead->status === 'new' ? 'New' : $lead->status,
            ]);
        }
    }

    public function down(): void
    {
        Schema::table('leads', function (Blueprint $table) {
            $table->dropColumn(['whatsapp', 'city', 'selected_price', 'notes', 'free_consultation']);
        });
    }
};
