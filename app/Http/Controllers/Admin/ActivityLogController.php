<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\ActivityLog;
use Illuminate\Http\Request;
use Illuminate\View\View;

class ActivityLogController extends Controller
{
    public function index(Request $request): View
    {
        $logs = ActivityLog::query()
            ->with('user')
            ->when($request->filled('module'), fn ($q) => $q->where('module', $request->module))
            ->when($request->filled('action'), fn ($q) => $q->where('action', $request->action))
            ->when($request->filled('q'), function ($q) use ($request) {
                $q->where('description', 'like', '%'.$request->q.'%');
            })
            ->latest()
            ->paginate(30)
            ->withQueryString();

        return view('admin.activity-logs.index', compact('logs'));
    }
}
