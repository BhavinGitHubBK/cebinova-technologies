<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('package_features', function (Blueprint $table) {
            $table->id();
            $table->foreignId('package_id')->constrained()->cascadeOnDelete();
            $table->string('name');
            $table->string('display_value')->nullable();
            $table->boolean('is_included')->default(true);
            $table->boolean('is_active')->default(true);
            $table->unsignedInteger('sort_order')->default(0);
            $table->timestamps();
            $table->index(['package_id', 'is_active', 'sort_order']);
        });

        Schema::create('pricing_options', function (Blueprint $table) {
            $table->id();
            $table->string('type', 30);
            $table->string('name');
            $table->string('slug')->unique();
            $table->text('description')->nullable();
            $table->decimal('price', 12, 2)->default(0);
            $table->string('billing_period', 30)->default('one_time');
            $table->json('metadata')->nullable();
            $table->boolean('is_recommended')->default(false);
            $table->boolean('is_active')->default(true);
            $table->unsignedInteger('sort_order')->default(0);
            $table->softDeletes();
            $table->timestamps();
            $table->index(['type', 'is_active', 'sort_order']);
        });

        Schema::table('leads', function (Blueprint $table) {
            $table->foreignId('pricing_package_id')->nullable()->after('selected_price')->constrained('packages')->nullOnDelete();
            $table->json('pricing_option_ids')->nullable()->after('pricing_package_id');
            $table->decimal('estimated_total', 12, 2)->nullable()->after('pricing_option_ids');
            $table->json('pricing_snapshot')->nullable()->after('estimated_total');
        });

        $now = now();
        $packages = DB::table('packages')->where('category', 'website')->get();
        foreach ($packages as $package) {
            $features = json_decode((string) $package->includes, true) ?: [];
            if ($features === []) {
                $plan = DB::table('package_plans')->where('package_id', $package->id)->orderBy('sort_order')->first();
                $features = $plan ? (json_decode((string) $plan->features, true) ?: []) : [];
            }
            foreach ($features as $index => $feature) {
                DB::table('package_features')->insert([
                    'package_id' => $package->id,
                    'name' => is_array($feature) ? ($feature['name'] ?? json_encode($feature)) : $feature,
                    'is_included' => true,
                    'is_active' => true,
                    'sort_order' => $index,
                    'created_at' => $now,
                    'updated_at' => $now,
                ]);
            }
        }

        $options = [
            ['addon', 'Android App', 'android-app', 24999, 'one_time', false, 1],
            ['addon', 'iOS App', 'ios-app', 34999, 'one_time', false, 2],
            ['domain', 'Use Existing Domain', 'existing-domain', 0, 'yearly', false, 1],
            ['domain', 'Hostinger Domain', 'hostinger-domain', 799, 'yearly', true, 2],
            ['domain', 'GoDaddy Domain', 'godaddy-domain', 999, 'yearly', false, 3],
            ['domain', 'BigRock Domain', 'bigrock-domain', 899, 'yearly', false, 4],
            ['hosting', 'Use Existing Hosting', 'existing-hosting', 0, 'yearly', false, 1],
            ['hosting', 'Basic Hosting', 'basic-hosting', 3000, 'yearly', false, 2],
            ['hosting', 'Premium Hosting', 'premium-hosting', 6000, 'yearly', true, 3],
        ];

        foreach ($options as [$type, $name, $slug, $price, $period, $recommended, $sort]) {
            DB::table('pricing_options')->updateOrInsert(['slug' => $slug], [
                'type' => $type,
                'name' => $name,
                'price' => $price,
                'billing_period' => $period,
                'is_recommended' => $recommended,
                'is_active' => true,
                'sort_order' => $sort,
                'created_at' => $now,
                'updated_at' => $now,
            ]);
        }
    }

    public function down(): void
    {
        Schema::table('leads', function (Blueprint $table) {
            $table->dropConstrainedForeignId('pricing_package_id');
            $table->dropColumn(['pricing_option_ids', 'estimated_total', 'pricing_snapshot']);
        });
        Schema::dropIfExists('pricing_options');
        Schema::dropIfExists('package_features');
    }
};
