<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        $visitorIds = DB::table('analytics_pageviews')->distinct()->pluck('visitor_id');

        foreach ($visitorIds as $visitorId) {
            $views = DB::table('analytics_pageviews')
                ->where('visitor_id', $visitorId)
                ->orderBy('viewed_at')
                ->orderBy('id')
                ->get();

            $previousPath = null;
            $remove = [];

            foreach ($views as $view) {
                if ($previousPath === $view->path) {
                    $remove[] = $view->id;

                    continue;
                }

                $previousPath = $view->path;
            }

            if ($remove !== []) {
                DB::table('analytics_pageviews')->whereIn('id', $remove)->delete();
            }
        }

        $usedVisitIds = DB::table('analytics_pageviews')->whereNotNull('visit_id')->distinct()->pluck('visit_id');
        DB::table('analytics_visits')->whereNotIn('id', $usedVisitIds)->delete();
    }

    public function down(): void
    {
        // Removed refresh duplicates are not restored.
    }
};
