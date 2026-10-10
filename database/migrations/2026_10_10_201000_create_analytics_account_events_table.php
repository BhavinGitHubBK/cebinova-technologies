<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('analytics_account_events', function (Blueprint $table) {
            $table->id();
            $table->foreignId('analytics_visitor_id')->nullable()->constrained('analytics_visitors')->nullOnDelete();
            $table->foreignId('customer_id')->nullable()->constrained('customers')->nullOnDelete();
            $table->string('action', 20);
            $table->string('ip_address', 45)->nullable();
            $table->string('path')->nullable();
            $table->timestamp('occurred_at');
            $table->timestamps();

            $table->index(['analytics_visitor_id', 'occurred_at'], 'account_events_visitor_idx');
            $table->index(['customer_id', 'occurred_at'], 'account_events_customer_idx');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('analytics_account_events');
    }
};
