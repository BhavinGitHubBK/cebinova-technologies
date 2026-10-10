<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\ActivityLog;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\View\View;

class ActivityLogController extends Controller
{
    public function index(Request $request): View
    {
        $visible = ActivityLog::query()
            ->when(! $request->user()->isSuperAdmin(), fn ($query) => $query->whereNotIn('description', ['Internal staff note added', 'Internal note added']));
        $logs = (clone $visible)
            ->with('user')
            ->when($request->filled('module'), fn ($query) => $query->where('module', $request->module))
            ->when($request->filled('user'), fn ($query) => $query->where('user_id', $request->integer('user')))
            ->when($request->filled('action'), fn ($query) => $query->where('action', $request->action))
            ->when($request->filled('from'), fn ($query) => $query->whereDate('created_at', '>=', $request->date('from')))
            ->when($request->filled('to'), fn ($query) => $query->whereDate('created_at', '<=', $request->date('to')))
            ->when($request->filled('q'), fn ($query) => $query->where('description', 'like', '%'.$request->string('q').'%'));
        $counted = (clone $visible)
            ->when($request->filled('module'), fn ($query) => $query->where('module', $request->module))
            ->when($request->filled('user'), fn ($query) => $query->where('user_id', $request->integer('user')))
            ->when($request->filled('from'), fn ($query) => $query->whereDate('created_at', '>=', $request->date('from')))
            ->when($request->filled('to'), fn ($query) => $query->whereDate('created_at', '<=', $request->date('to')))
            ->when($request->filled('q'), fn ($query) => $query->where('description', 'like', '%'.$request->string('q').'%'));
        [$sort, $dir] = admin_sort($request, ['created_at', 'user', 'action', 'module', 'description', 'ip_address'], 'created_at', 'desc');
        if ($sort === 'user') {
            $logs->orderBy(User::query()->select('name')->whereColumn('users.id', 'activity_logs.user_id')->limit(1), $dir);
        } else {
            $logs->orderBy($sort, $dir);
        }
        $logs = $logs->orderByDesc('activity_logs.id')->paginate(30)->withQueryString();

        return view('admin.activity-logs.index', [
            'logs' => $logs,
            'modules' => (clone $visible)->whereNotNull('module')->distinct()->orderBy('module')->pluck('module'),
            'actions' => (clone $visible)->whereNotNull('action')->distinct()->orderBy('action')->pluck('action'),
            'people' => User::query()
                ->whereIn('id', (clone $visible)->whereNotNull('user_id')->distinct()->pluck('user_id'))
                ->orderBy('name')
                ->get(['id', 'name']),
            'sort' => $sort,
            'dir' => $dir,
            'counts' => [
                'all' => (clone $counted)->count(),
                'update' => (clone $counted)->where('action', 'update')->count(),
                'create' => (clone $counted)->where('action', 'create')->count(),
                'delete' => (clone $counted)->where('action', 'delete')->count(),
            ],
        ]);
    }
}
