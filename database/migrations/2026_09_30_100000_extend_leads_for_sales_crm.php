<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('leads', function (Blueprint $table) {
            $table->string('priority', 20)->default('Medium')->after('status');
            $table->decimal('estimated_value', 12, 2)->nullable()->after('priority');
            $table->timestamp('last_contacted_at')->nullable()->after('contacted_at');
            $table->string('lost_reason', 60)->nullable()->after('lost_at');
            $table->index(['status', 'assigned_to'], 'leads_status_assignee_idx');
            $table->index('follow_up_at', 'leads_follow_up_idx');
        });

        Schema::create('lead_tasks', function (Blueprint $table) {
            $table->id();
            $table->foreignId('lead_id')->constrained()->restrictOnDelete();
            $table->foreignId('assigned_to')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->string('title', 190);
            $table->text('description')->nullable();
            $table->timestamp('due_at');
            $table->string('priority', 20)->default('Medium');
            $table->string('status', 20)->default('Pending');
            $table->timestamp('completed_at')->nullable();
            $table->timestamps();

            $table->index(['lead_id', 'status', 'due_at'], 'lead_tasks_lead_due_idx');
            $table->index(['assigned_to', 'status', 'due_at'], 'lead_tasks_assignee_due_idx');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('lead_tasks');

        Schema::table('leads', function (Blueprint $table) {
            $table->dropIndex('leads_status_assignee_idx');
            $table->dropIndex('leads_follow_up_idx');
            $table->dropColumn(['priority', 'estimated_value', 'last_contacted_at', 'lost_reason']);
        });
    }
};
