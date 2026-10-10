<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::dropIfExists('analytics_conversions');
        Schema::dropIfExists('analytics_events');

        if (Schema::hasTable('analytics_pageviews') && Schema::hasColumn('analytics_pageviews', 'session_id')) {
            Schema::table('analytics_pageviews', function (Blueprint $table) {
                $table->dropForeign(['session_id']);
                $table->dropUnique(['session_id', 'client_key']);
            });
        }

        Schema::dropIfExists('analytics_sessions');

        if (! Schema::hasTable('analytics_visitors')) {
            Schema::create('analytics_visitors', function (Blueprint $table) {
                $table->id();
                $table->uuid('uuid')->unique();
                $table->string('name', 80)->nullable();
                $table->string('mobile', 20)->nullable();
                $table->string('ip_address', 45)->nullable();
                $table->string('city', 80)->nullable();
                $table->string('region', 80)->nullable();
                $table->string('country', 80)->nullable();
                $table->dateTime('first_seen_at');
                $table->dateTime('last_seen_at');
                $table->string('first_referrer_domain', 190)->nullable();
                $table->string('first_landing_path', 255)->nullable();
                $table->string('referrer_url', 500)->nullable();
                $table->string('exit_path', 255)->nullable();
                $table->timestamps();

                $table->index('first_seen_at');
                $table->index('last_seen_at');
            });
        } else {
            if (Schema::hasColumn('analytics_visitors', 'lead_id')) {
                Schema::table('analytics_visitors', function (Blueprint $table) {
                    $table->dropForeign(['lead_id']);
                });
            }

            $this->dropColumns('analytics_visitors', [
                'first_channel',
                'first_utm_source',
                'first_utm_medium',
                'first_utm_campaign',
                'lead_id',
            ]);
        }

        if (! Schema::hasTable('analytics_pageviews')) {
            Schema::create('analytics_pageviews', function (Blueprint $table) {
                $table->id();
                $table->foreignId('visitor_id')->constrained('analytics_visitors')->cascadeOnDelete();
                $table->string('path', 255);
                $table->string('page_title', 180)->nullable();
                $table->string('referrer_path', 255)->nullable();
                $table->dateTime('viewed_at');
                $table->dateTime('created_at')->nullable();

                $table->index(['visitor_id', 'viewed_at']);
                $table->index('viewed_at');
                $table->index('path');
            });
        } else {
            $this->dropColumns('analytics_pageviews', [
                'session_id',
                'client_key',
                'route_name',
                'page_type',
                'entity_type',
                'entity_id',
                'engagement_seconds',
            ]);
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('analytics_pageviews');
        Schema::dropIfExists('analytics_visitors');
    }

    private function dropColumns(string $table, array $columns): void
    {
        $present = array_values(array_filter(
            $columns,
            fn (string $column) => Schema::hasColumn($table, $column),
        ));

        if ($present === []) {
            return;
        }

        Schema::table($table, function (Blueprint $table) use ($present) {
            $table->dropColumn($present);
        });
    }
};
