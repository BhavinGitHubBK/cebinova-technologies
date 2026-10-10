<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('site_settings', function (Blueprint $table) {
            if (! Schema::hasColumn('site_settings', 'gst_percent')) {
                $table->unsignedTinyInteger('gst_percent')->default(18)->after('copyright_text');
            }

            if (! Schema::hasColumn('site_settings', 'lead_notify_email')) {
                $table->string('lead_notify_email')->nullable()->after('gst_percent');
            }
        });

        DB::table('leads')->where('status', 'Follow-up')->update(['status' => 'Qualified']);
        DB::table('leads')->where('status', 'Converted')->update(['status' => 'Won']);
        DB::table('leads')->where('status', 'Negotiation')->update(['status' => 'Proposal Sent']);

        foreach ([
            ['support', 'Starter Care', 'starter-care', 0, 'one_time', false, 1],
            ['support', 'Business Care', 'business-care', 0, 'one_time', true, 2],
            ['support', 'Priority Care', 'priority-care', 0, 'one_time', false, 3],
        ] as [$type, $name, $slug, $price, $period, $recommended, $order]) {
            DB::table('pricing_options')->updateOrInsert(['slug' => $slug], [
                'type' => $type,
                'name' => $name,
                'description' => 'Included after launch unless a paid support price is set.',
                'price' => $price,
                'billing_period' => $period,
                'is_recommended' => $recommended,
                'is_active' => true,
                'sort_order' => $order,
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }

        if (! Schema::hasTable('case_studies')) {
            Schema::create('case_studies', function (Blueprint $table) {
                $table->id();
                $table->string('title');
                $table->string('slug')->unique();
                $table->string('client_name')->nullable();
                $table->string('industry')->nullable();
                $table->string('service')->nullable();
                $table->text('summary')->nullable();
                $table->longText('challenge')->nullable();
                $table->longText('approach')->nullable();
                $table->longText('outcome')->nullable();
                $table->string('image')->nullable();
                $table->boolean('is_published')->default(false);
                $table->boolean('is_featured')->default(false);
                $table->unsignedInteger('sort_order')->default(0);
                $table->string('seo_title')->nullable();
                $table->string('seo_description')->nullable();
                $table->timestamps();
                $table->softDeletes();
            });
        }

        if (! Schema::hasTable('testimonials')) {
            Schema::create('testimonials', function (Blueprint $table) {
                $table->id();
                $table->string('name');
                $table->string('role')->nullable();
                $table->string('company')->nullable();
                $table->text('quote');
                $table->unsignedTinyInteger('rating')->nullable();
                $table->boolean('is_published')->default(false);
                $table->boolean('is_featured')->default(false);
                $table->unsignedInteger('sort_order')->default(0);
                $table->timestamps();
                $table->softDeletes();
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('testimonials');
        Schema::dropIfExists('case_studies');

        DB::table('pricing_options')->whereIn('slug', ['starter-care', 'business-care', 'priority-care'])->delete();
        DB::table('leads')->where('status', 'Qualified')->update(['status' => 'Follow-up']);
        DB::table('leads')->where('status', 'Won')->update(['status' => 'Converted']);

        Schema::table('site_settings', function (Blueprint $table) {
            $table->dropColumn(['gst_percent', 'lead_notify_email']);
        });
    }
};
