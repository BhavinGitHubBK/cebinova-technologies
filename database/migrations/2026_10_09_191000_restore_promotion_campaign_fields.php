<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('promotion_campaigns')) {
            return;
        }

        Schema::table('promotion_campaigns', function (Blueprint $table) {
            if (! Schema::hasColumn('promotion_campaigns', 'campaign_type')) {
                $table->string('campaign_type', 40)->default('festival');
            }
            if (! Schema::hasColumn('promotion_campaigns', 'festival_name')) {
                $table->string('festival_name')->nullable();
            }
            if (! Schema::hasColumn('promotion_campaigns', 'subheadline')) {
                $table->string('subheadline')->nullable();
            }
            if (! Schema::hasColumn('promotion_campaigns', 'announcement_text')) {
                $table->string('announcement_text')->nullable();
            }
            if (! Schema::hasColumn('promotion_campaigns', 'badge_text')) {
                $table->string('badge_text')->nullable();
            }
            if (! Schema::hasColumn('promotion_campaigns', 'cta_url')) {
                $table->string('cta_url')->nullable();
            }
            if (! Schema::hasColumn('promotion_campaigns', 'terms')) {
                $table->text('terms')->nullable();
            }
            if (! Schema::hasColumn('promotion_campaigns', 'preview_days')) {
                $table->unsignedSmallInteger('preview_days')->default(0);
            }
            if (! Schema::hasColumn('promotion_campaigns', 'popup_delay_seconds')) {
                $table->unsignedSmallInteger('popup_delay_seconds')->default(4);
            }
            if (! Schema::hasColumn('promotion_campaigns', 'popup_scroll_percent')) {
                $table->unsignedTinyInteger('popup_scroll_percent')->nullable();
            }
            if (! Schema::hasColumn('promotion_campaigns', 'popup_frequency')) {
                $table->string('popup_frequency', 20)->default('session');
            }
            if (! Schema::hasColumn('promotion_campaigns', 'popup_cooldown_hours')) {
                $table->unsignedSmallInteger('popup_cooldown_hours')->default(24);
            }
            if (! Schema::hasColumn('promotion_campaigns', 'show_before_start')) {
                $table->boolean('show_before_start')->default(false);
            }
            if (! Schema::hasColumn('promotion_campaigns', 'announcement_dismissible')) {
                $table->boolean('announcement_dismissible')->default(true);
            }
            if (! Schema::hasColumn('promotion_campaigns', 'popup_exit_intent')) {
                $table->boolean('popup_exit_intent')->default(false);
            }
            if (! Schema::hasColumn('promotion_campaigns', 'seo_title')) {
                $table->string('seo_title')->nullable();
            }
            if (! Schema::hasColumn('promotion_campaigns', 'seo_description')) {
                $table->string('seo_description', 300)->nullable();
            }
        });

        DB::table('promotion_campaigns')->where('slug', 'diwali-special')->update([
            'campaign_type' => 'festival',
            'festival_name' => 'Diwali',
            'subheadline' => '20% off website pricing and marketing packages',
            'announcement_text' => 'Diwali Special — 20% off website pricing and marketing packages',
            'badge_text' => 'Diwali Special',
            'show_before_start' => true,
            'preview_days' => 14,
            'popup_delay_seconds' => 3,
            'popup_frequency' => 'session',
            'popup_cooldown_hours' => 24,
            'announcement_dismissible' => false,
        ]);

        DB::table('promotion_campaigns')->where('slug', 'new-business-offer')->update([
            'campaign_type' => 'strategic',
            'subheadline' => '10% off website pricing and marketing packages',
            'announcement_text' => 'New Business Offer — 10% off with code STARTUP10',
            'badge_text' => 'New Business Offer',
            'show_before_start' => true,
            'preview_days' => 45,
            'popup_delay_seconds' => 4,
            'popup_frequency' => 'session',
            'popup_cooldown_hours' => 24,
            'announcement_dismissible' => false,
        ]);
    }

    public function down(): void
    {
        $drop = [
            'campaign_type', 'festival_name', 'subheadline', 'announcement_text', 'badge_text', 'cta_url', 'terms',
            'preview_days', 'popup_delay_seconds', 'popup_scroll_percent', 'popup_frequency', 'popup_cooldown_hours',
            'show_before_start', 'announcement_dismissible', 'popup_exit_intent', 'seo_title', 'seo_description',
        ];
        $present = array_values(array_filter($drop, fn (string $column) => Schema::hasColumn('promotion_campaigns', $column)));
        if ($present === []) {
            return;
        }

        Schema::table('promotion_campaigns', function (Blueprint $table) use ($present) {
            $table->dropColumn($present);
        });
    }
};
