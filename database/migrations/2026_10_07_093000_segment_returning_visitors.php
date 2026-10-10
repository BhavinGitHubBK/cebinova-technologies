<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('analytics_visits', function (Blueprint $table) {
            $table->id();
            $table->foreignId('visitor_id')->constrained('analytics_visitors')->cascadeOnDelete();
            $table->dateTime('started_at');
            $table->dateTime('last_seen_at');
            $table->string('landing_path', 255)->nullable();
            $table->string('exit_path', 255)->nullable();
            $table->string('referrer_domain', 190)->nullable();
            $table->string('referrer_url', 500)->nullable();
            $table->timestamps();

            $table->index(['visitor_id', 'started_at']);
        });

        Schema::table('analytics_pageviews', function (Blueprint $table) {
            $table->foreignId('visit_id')->nullable()->after('visitor_id')->constrained('analytics_visits')->nullOnDelete();
        });

        $visitors = DB::table('analytics_visitors')->orderBy('id')->get();

        foreach ($visitors as $visitor) {
            $views = DB::table('analytics_pageviews')
                ->where('visitor_id', $visitor->id)
                ->orderBy('viewed_at')
                ->get();

            $visitId = DB::table('analytics_visits')->insertGetId([
                'visitor_id' => $visitor->id,
                'started_at' => $views->first()->viewed_at ?? $visitor->first_seen_at,
                'last_seen_at' => $views->last()->viewed_at ?? $visitor->last_seen_at,
                'landing_path' => $views->first()->path ?? $visitor->first_landing_path,
                'exit_path' => $views->last()->path ?? $visitor->exit_path,
                'referrer_domain' => $visitor->first_referrer_domain,
                'referrer_url' => $visitor->referrer_url,
                'created_at' => now(),
                'updated_at' => now(),
            ]);

            if ($views->isNotEmpty()) {
                DB::table('analytics_pageviews')
                    ->where('visitor_id', $visitor->id)
                    ->update(['visit_id' => $visitId]);
            }
        }

        $grouped = DB::table('analytics_visitors')
            ->whereNotNull('mobile')
            ->where('mobile', '!=', '')
            ->orderBy('first_seen_at')
            ->get()
            ->groupBy('mobile');

        foreach ($grouped as $rows) {
            if ($rows->count() < 2) {
                continue;
            }

            $canonical = $rows->first();

            foreach ($rows->skip(1) as $extra) {
                DB::table('analytics_visits')->where('visitor_id', $extra->id)->update(['visitor_id' => $canonical->id]);
                DB::table('analytics_pageviews')->where('visitor_id', $extra->id)->update(['visitor_id' => $canonical->id]);

                $updates = [];
                if ($extra->last_seen_at && $extra->last_seen_at > $canonical->last_seen_at) {
                    $updates['last_seen_at'] = $extra->last_seen_at;
                    $updates['exit_path'] = $extra->exit_path;
                    $updates['ip_address'] = $extra->ip_address;
                    $canonical->last_seen_at = $extra->last_seen_at;
                }

                $canonicalWeak = $canonical->country === null || $canonical->country === 'Local network';
                $extraWeak = $extra->country === null || $extra->country === 'Local network';
                if ($canonicalWeak && ! $extraWeak) {
                    $updates['city'] = $extra->city;
                    $updates['region'] = $extra->region;
                    $updates['country'] = $extra->country;
                    $canonical->country = $extra->country;
                }

                if ($canonical->latitude === null && $extra->latitude !== null) {
                    $updates['latitude'] = $extra->latitude;
                    $updates['longitude'] = $extra->longitude;
                    $updates['location_accuracy'] = $extra->location_accuracy;
                    $updates['address'] = $extra->address;
                    $canonical->latitude = $extra->latitude;
                }

                if ($updates !== []) {
                    DB::table('analytics_visitors')->where('id', $canonical->id)->update($updates);
                }

                DB::table('analytics_visitors')->where('id', $extra->id)->delete();
            }
        }
    }

    public function down(): void
    {
        Schema::table('analytics_pageviews', function (Blueprint $table) {
            $table->dropConstrainedForeignId('visit_id');
        });

        Schema::dropIfExists('analytics_visits');
    }
};
