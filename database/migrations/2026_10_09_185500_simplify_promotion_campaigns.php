<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('promotion_targets')) {
            DB::table('promotion_targets')
                ->whereNotIn('target_type', ['all_website_packages', 'all_marketing_packages'])
                ->delete();
        }

        if (! Schema::hasTable('promotion_campaigns')) {
            return;
        }

        $drop = [
            'campaign_type', 'festival_name', 'subheadline', 'maximum_discount_amount', 'minimum_amount',
            'priority', 'allow_coupon_stack', 'requires_all_targets', 'show_before_start', 'preview_days',
            'popup_delay_seconds', 'popup_scroll_percent', 'popup_exit_intent', 'popup_frequency',
            'popup_cooldown_hours', 'announcement_text', 'announcement_dismissible', 'badge_text', 'cta_url',
            'terms', 'show_brochure', 'brochure_path', 'popup_image_path', 'desktop_banner_path',
            'mobile_banner_path', 'seo_title', 'seo_description',
        ];
        $present = array_values(array_filter($drop, fn (string $column) => Schema::hasColumn('promotion_campaigns', $column)));
        if ($present === []) {
            return;
        }

        Schema::table('promotion_campaigns', function (Blueprint $table) use ($present) {
            $table->dropColumn($present);
        });
    }

    public function down(): void
    {
        Schema::table('promotion_campaigns', function (Blueprint $table) {
            $table->string('campaign_type', 40)->default('festival');
            $table->string('festival_name')->nullable();
            $table->string('subheadline')->nullable();
            $table->decimal('maximum_discount_amount', 12, 2)->nullable();
            $table->decimal('minimum_amount', 12, 2)->nullable();
            $table->unsignedInteger('priority')->default(0);
            $table->boolean('allow_coupon_stack')->default(false);
            $table->boolean('requires_all_targets')->default(false);
            $table->boolean('show_before_start')->default(false);
            $table->unsignedSmallInteger('preview_days')->default(0);
            $table->unsignedSmallInteger('popup_delay_seconds')->default(4);
            $table->unsignedTinyInteger('popup_scroll_percent')->nullable();
            $table->boolean('popup_exit_intent')->default(false);
            $table->string('popup_frequency', 20)->default('session');
            $table->unsignedSmallInteger('popup_cooldown_hours')->default(24);
            $table->string('announcement_text')->nullable();
            $table->boolean('announcement_dismissible')->default(true);
            $table->string('badge_text')->nullable();
            $table->string('cta_url')->nullable();
            $table->text('terms')->nullable();
            $table->boolean('show_brochure')->default(false);
            $table->string('brochure_path')->nullable();
            $table->string('popup_image_path')->nullable();
            $table->string('desktop_banner_path')->nullable();
            $table->string('mobile_banner_path')->nullable();
            $table->string('seo_title')->nullable();
            $table->string('seo_description', 300)->nullable();
        });
    }
};
