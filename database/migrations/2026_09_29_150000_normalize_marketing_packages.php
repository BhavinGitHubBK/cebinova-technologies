<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('marketing_plan_deliverables', function (Blueprint $table) {
            $table->id();
            $table->foreignId('package_plan_id')->constrained()->cascadeOnDelete();
            $table->string('group', 30)->default('included');
            $table->string('name');
            $table->decimal('quantity', 10, 2)->nullable();
            $table->string('unit', 60)->nullable();
            $table->boolean('is_active')->default(true);
            $table->unsignedInteger('sort_order')->default(0);
            $table->timestamps();
            $table->index(['package_plan_id', 'group', 'is_active', 'sort_order'], 'marketing_deliverables_lookup');
        });

        Schema::table('leads', function (Blueprint $table) {
            $table->foreignId('marketing_package_id')->nullable()->after('package_category')->constrained('packages')->nullOnDelete();
            $table->foreignId('marketing_plan_id')->nullable()->after('plan_duration')->constrained('package_plans')->nullOnDelete();
            $table->json('marketing_snapshot')->nullable()->after('selected_price');
        });

        $now = now();
        DB::table('package_plans')
            ->join('packages', 'packages.id', '=', 'package_plans.package_id')
            ->whereIn('packages.category', ['marketing_regular', 'marketing_festival', 'marketing_growth'])
            ->select('package_plans.*')
            ->orderBy('package_plans.id')
            ->each(function ($plan) use ($now) {
                foreach (['included' => 'features', 'monthly_pace' => 'monthly_pace'] as $group => $column) {
                    $items = json_decode((string) $plan->{$column}, true) ?: [];
                    foreach ($items as $index => $item) {
                        DB::table('marketing_plan_deliverables')->insert([
                            'package_plan_id' => $plan->id,
                            'group' => $group,
                            'name' => is_string($item) ? $item : json_encode($item),
                            'is_active' => true,
                            'sort_order' => $index,
                            'created_at' => $now,
                            'updated_at' => $now,
                        ]);
                    }
                }
            });
    }

    public function down(): void
    {
        Schema::table('leads', function (Blueprint $table) {
            $table->dropConstrainedForeignId('marketing_package_id');
            $table->dropConstrainedForeignId('marketing_plan_id');
            $table->dropColumn('marketing_snapshot');
        });
        Schema::dropIfExists('marketing_plan_deliverables');
    }
};
