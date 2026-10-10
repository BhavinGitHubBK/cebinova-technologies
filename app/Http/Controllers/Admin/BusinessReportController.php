<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Service;
use App\Models\User;
use App\Services\Admin\ActivityLogger;
use App\Services\Reports\BusinessMetrics;
use App\Services\Reports\CompanyReports;
use App\Services\Reports\ManagementReport;
use App\Services\Reports\ReportQuery;
use App\Services\Reports\ReportRange;
use Illuminate\Http\Request;

class BusinessReportController extends Controller
{
    public function dashboard(Request $request, BusinessMetrics $metrics)
    {
        return view('admin.reports.dashboard', $metrics->dashboard(ReportRange::fromRequest($request), $request->user()));
    }

    public function index(Request $request, ManagementReport $reports)
    {
        return view('admin.reports.index', $reports->build(ReportRange::fromRequest($request)));
    }

    public function review(Request $request, string $report, CompanyReports $reports)
    {
        abort_unless($reports->exists($report), 404);

        return view('admin.reports.review', $reports->review($report, ReportRange::fromRequest($request)));
    }

    public function show(Request $request, string $type, ReportQuery $reports)
    {
        abort_unless(in_array($type, ReportQuery::TYPES, true), 404);
        $range = ReportRange::fromRequest($request);
        $query = $reports->query($type, $request, $range, $request->user());
        [$sort, $dir] = admin_sort($request, ['name', 'business_name', 'source', 'service', 'status', 'estimated_value', 'assigned', 'created_at'], 'created_at', 'desc');
        if ($sort === 'assigned') {
            $query->orderBy(User::query()->select('name')->whereColumn('users.id', 'leads.assigned_to')->limit(1), $dir);
        } else {
            $query->orderBy('leads.'.$sort, $dir);
        }
        $rows = $query->orderByDesc('leads.id')->paginate(25)->withQueryString();

        return view('admin.reports.show', [
            'type' => $type,
            'range' => $range,
            'rows' => $rows,
            'columns' => $reports->columns($type),
            'reports' => $reports,
            'totals' => [],
            'statuses' => $reports->statuses($type),
            'clients' => collect(),
            'users' => User::query()->orderBy('name')->get(['id', 'name']),
            'services' => Service::query()->orderBy('name')->get(['id', 'name']),
            'snapshot' => false,
            'sort' => $sort,
            'dir' => $dir,
        ]);
    }

    public function export(Request $request, string $type, ReportQuery $reports)
    {
        abort_unless(in_array($type, ReportQuery::TYPES, true), 404);
        $range = ReportRange::fromRequest($request);
        $query = $reports->query($type, $request, $range, $request->user());
        abort_if((clone $query)->count() > 10000, 422, 'Export limit is 10,000 rows. Narrow the report filters.');
        ActivityLogger::log('report_export', 'reports', null, 'Report CSV exported', ['type' => $type, 'filters' => $request->only(['range', 'from', 'to', 'status', 'user_id', 'service_id'])]);

        return response()->streamDownload(function () use ($query, $type, $reports) {
            $stream = fopen('php://output', 'w');
            fputcsv($stream, $reports->columns($type));
            $query->orderBy($query->getModel()->getTable().'.id')->chunk(200, function ($rows) use ($stream, $type, $reports) {
                foreach ($rows as $row) {
                    fputcsv($stream, array_map([ReportQuery::class, 'csvSafe'], $reports->row($type, $row)));
                }
            });
            fclose($stream);
        }, $type.'-'.$range->from->format('Ymd').'-'.$range->to->format('Ymd').'.csv', ['Content-Type' => 'text/csv; charset=UTF-8', 'Cache-Control' => 'private, no-store', 'X-Content-Type-Options' => 'nosniff']);
    }
}
