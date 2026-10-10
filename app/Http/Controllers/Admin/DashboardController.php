<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Services\Admin\DashboardMetrics;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class DashboardController extends Controller
{
    public function index(Request $request, DashboardMetrics $metrics): View
    {
        return view('admin.dashboard.index', $metrics->build($request));
    }

    public function section(Request $request, DashboardMetrics $metrics, string $section): JsonResponse
    {
        abort_unless(in_array($section, ['metrics', 'pipeline', 'trend', 'services', 'attention', 'leads'], true), 404);

        $data = $metrics->build($request);
        $data['focus'] = in_array($request->query('focus'), ['overdue', 'today'], true) ? (string) $request->query('focus') : '';

        if ($section === 'trend') {
            return response()->json([
                'caption' => $data['range'] === 'all' ? 'Last 12 months' : $data['rangeLabel'],
                'labels' => $data['trendLabels'],
                'values' => $data['trendValues'],
                'empty' => 'No enquiries in this period yet.',
            ]);
        }

        if ($section === 'services') {
            return response()->json([
                'caption' => $data['status'] !== '' ? $data['status'] : 'Top enquiry sources',
                'labels' => $data['serviceLabels'],
                'values' => $data['serviceValues'],
                'empty' => 'No service-tagged leads yet.',
            ]);
        }

        return response()->json([
            'html' => view('admin.dashboard.partials.'.$section, $data)->render(),
            'note' => $data['filterSummary'],
        ]);
    }
}
