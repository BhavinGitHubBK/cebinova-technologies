<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\CacheOperationLog;
use App\Services\System\CacheManagementService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

class CacheController extends Controller
{
    public function index(CacheManagementService $cache): View
    {
        return view('admin.cache.index', $cache->page());
    }

    public function clearApplication(CacheManagementService $cache): RedirectResponse|JsonResponse
    {
        return $this->finish($cache->clearApplicationCache(), 'Application cache cleared successfully.');
    }

    public function clearConfig(CacheManagementService $cache): RedirectResponse|JsonResponse
    {
        return $this->finish($cache->clearConfigCache(), 'Configuration cache cleared successfully.');
    }

    public function rebuildConfig(CacheManagementService $cache): RedirectResponse|JsonResponse
    {
        return $this->finish($cache->cacheConfig(), 'Configuration rebuilt successfully.');
    }

    public function clearRoutes(CacheManagementService $cache): RedirectResponse|JsonResponse
    {
        return $this->finish($cache->clearRouteCache(), 'Route cache cleared successfully.');
    }

    public function rebuildRoutes(CacheManagementService $cache): RedirectResponse|JsonResponse
    {
        return $this->finish($cache->cacheRoutes(), 'Routes rebuilt successfully.');
    }

    public function clearViews(CacheManagementService $cache): RedirectResponse|JsonResponse
    {
        return $this->finish($cache->clearViewCache(), 'View cache cleared successfully.');
    }

    public function rebuildViews(CacheManagementService $cache): RedirectResponse|JsonResponse
    {
        return $this->finish($cache->cacheViews(), 'Views rebuilt successfully.');
    }

    public function clearEvents(CacheManagementService $cache): RedirectResponse|JsonResponse
    {
        return $this->finish($cache->clearEventCache(), 'Event cache cleared successfully.');
    }

    public function rebuildEvents(CacheManagementService $cache): RedirectResponse|JsonResponse
    {
        return $this->finish($cache->cacheEvents(), 'Events rebuilt successfully.');
    }

    public function clearCompiled(CacheManagementService $cache): RedirectResponse|JsonResponse
    {
        return $this->finish($cache->clearCompiledFiles(), 'Compiled files cleared successfully.');
    }

    public function clearAll(CacheManagementService $cache): RedirectResponse|JsonResponse
    {
        return $this->finish($cache->clearAll(), 'All Laravel caches cleared successfully.');
    }

    private function finish(CacheOperationLog $log, string $success): RedirectResponse|JsonResponse
    {
        $detail = $log->error_message ?: 'The command did not finish.';
        $message = $log->success ? $success : 'Cache operation failed. '.$detail;

        if (request()->expectsJson()) {
            return response()->json(array_merge(
                ['ok' => $log->success, 'message' => $message],
                $this->state(app(CacheManagementService::class))
            ), $log->success ? 200 : 422);
        }

        if (! $log->success) {
            return back()->withErrors(['cache' => $message]);
        }

        return back()->with('success', $success);
    }

    /**
     * @return array{summary: list<array{label: string, value: string}>, rows: list<array<string, mixed>>, history: list<array<string, mixed>>}
     */
    private function state(CacheManagementService $cache): array
    {
        $page = $cache->page();

        return [
            'summary' => collect($page['summary'])->map(fn (array $card) => [
                'label' => $card['label'],
                'value' => $card['value'],
            ])->values()->all(),
            'rows' => collect($page['rows'])->map(fn (array $row) => [
                'type' => $row['type'],
                'status' => $row['status'],
                'status_label' => $row['status_label'],
                'status_help' => $row['status_help'],
                'when' => $row['last']?->cleared_at?->diffForHumans(),
                'by' => $row['last']?->user?->name,
            ])->values()->all(),
            'history' => $page['history']->map(fn (CacheOperationLog $entry) => [
                'action' => $entry->action === 'rebuild' ? 'Rebuilt' : 'Cleared',
                'cache' => $entry->cacheLabel(),
                'admin' => $entry->user->name ?? 'Admin',
                'time' => $entry->cleared_at?->diffForHumans(),
                'success' => $entry->success,
                'duration' => number_format($entry->duration_ms).' ms',
            ])->values()->all(),
        ];
    }
}
