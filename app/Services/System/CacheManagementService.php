<?php

namespace App\Services\System;

use App\Models\CacheOperationLog;
use App\Services\Admin\ActivityLogger;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Auth;
use Throwable;

class CacheManagementService
{
    /** @var array<string, mixed>|null */
    private ?array $commands = null;

    public function clearApplicationCache(): CacheOperationLog
    {
        return $this->execute('application.clear');
    }

    public function clearConfigCache(): CacheOperationLog
    {
        return $this->execute('config.clear');
    }

    public function cacheConfig(): CacheOperationLog
    {
        return $this->execute('config.rebuild');
    }

    public function clearRouteCache(): CacheOperationLog
    {
        return $this->execute('routes.clear');
    }

    public function cacheRoutes(): CacheOperationLog
    {
        return $this->execute('routes.rebuild');
    }

    public function clearViewCache(): CacheOperationLog
    {
        return $this->execute('views.clear');
    }

    public function cacheViews(): CacheOperationLog
    {
        return $this->execute('views.rebuild');
    }

    public function clearEventCache(): CacheOperationLog
    {
        return $this->execute('events.clear');
    }

    public function cacheEvents(): CacheOperationLog
    {
        return $this->execute('events.rebuild');
    }

    public function clearCompiledFiles(): CacheOperationLog
    {
        return $this->execute('compiled.clear');
    }

    public function clearAll(): CacheOperationLog
    {
        return $this->execute('all.clear');
    }

    /**
     * @return array{
     *     environment: string,
     *     environment_label: string,
     *     summary: list<array{label: string, value: string, help: ?string}>,
     *     rows: list<array<string, mixed>>,
     *     history: Collection<int, CacheOperationLog>,
     *     system: array<string, string>
     * }
     */
    public function page(): array
    {
        $environment = (string) app()->environment();

        return [
            'environment' => $environment,
            'environment_label' => $this->environmentLabel($environment),
            'summary' => $this->summary($environment),
            'rows' => $this->rows(),
            'history' => CacheOperationLog::query()->with('user:id,name')->latest('id')->limit(8)->get(),
            'system' => [
                'Laravel' => app()->version(),
                'PHP' => PHP_VERSION,
                'Environment' => $this->environmentLabel($environment),
                'Cache driver' => (string) config('cache.default'),
                'Session driver' => (string) config('session.driver'),
                'Queue connection' => (string) config('queue.default'),
            ],
        ];
    }

    /**
     * @return array{command: string, type: string, action: string, label: string, success: string}|null
     */
    public function operation(string $key): ?array
    {
        return $this->operations()[$key] ?? null;
    }

    public function supports(string $command): bool
    {
        $this->commands ??= Artisan::all();

        return array_key_exists($command, $this->commands);
    }

    private function execute(string $key): CacheOperationLog
    {
        $operation = $this->operations()[$key] ?? null;
        if ($operation === null) {
            return $this->record('unknown', 'clear', 0, false, 'This cache action is not available.');
        }

        if (! $this->supports($operation['command'])) {
            return $this->record($operation['type'], $operation['action'], 0, false, 'This cache action is not available in this Laravel version.');
        }

        $started = hrtime(true);
        try {
            $code = Artisan::call($operation['command']);
            $duration = $this->duration($started);
            if ($code !== 0) {
                return $this->record($operation['type'], $operation['action'], $duration, false, $this->safeMessage(Artisan::output()));
            }

            $log = $this->record($operation['type'], $operation['action'], $duration, true, null);
            ActivityLogger::log('update', 'cache', null, $operation['success']);

            return $log;
        } catch (Throwable $exception) {
            return $this->record(
                $operation['type'],
                $operation['action'],
                $this->duration($started),
                false,
                $this->safeMessage($exception->getMessage())
            );
        }
    }

    private function record(string $type, string $action, int $duration, bool $success, ?string $error): CacheOperationLog
    {
        return CacheOperationLog::query()->create([
            'cache_type' => $type,
            'action' => $action,
            'cleared_at' => now(),
            'cleared_by' => Auth::id(),
            'duration_ms' => $duration,
            'success' => $success,
            'error_message' => $error,
        ]);
    }

    private function duration(int $started): int
    {
        return (int) min(600000, round((hrtime(true) - $started) / 1_000_000));
    }

    private function safeMessage(string $message): string
    {
        $message = trim((string) preg_replace('/\s+/', ' ', $message));
        $message = (string) preg_replace('#([A-Za-z]:\\\\|/(?:home|var|usr|opt|storage|Users)/)[^\s]+#', '[path]', $message);
        if ($message === '' || preg_match('/APP_KEY|DB_PASSWORD|DB_USERNAME|MAIL_PASSWORD|secret|token|credential/i', $message)) {
            return 'The command did not finish.';
        }

        return mb_substr($message, 0, 160);
    }

    /**
     * @return array<string, array{command: string, type: string, action: string, label: string, success: string}>
     */
    private function operations(): array
    {
        return [
            'application.clear' => ['command' => 'cache:clear', 'type' => 'application', 'action' => 'clear', 'label' => 'Application Cache', 'success' => 'Application cache cleared successfully.'],
            'config.clear' => ['command' => 'config:clear', 'type' => 'config', 'action' => 'clear', 'label' => 'Configuration', 'success' => 'Configuration cache cleared successfully.'],
            'config.rebuild' => ['command' => 'config:cache', 'type' => 'config', 'action' => 'rebuild', 'label' => 'Configuration', 'success' => 'Configuration rebuilt successfully.'],
            'routes.clear' => ['command' => 'route:clear', 'type' => 'routes', 'action' => 'clear', 'label' => 'Routes', 'success' => 'Route cache cleared successfully.'],
            'routes.rebuild' => ['command' => 'route:cache', 'type' => 'routes', 'action' => 'rebuild', 'label' => 'Routes', 'success' => 'Routes rebuilt successfully.'],
            'views.clear' => ['command' => 'view:clear', 'type' => 'views', 'action' => 'clear', 'label' => 'Views', 'success' => 'View cache cleared successfully.'],
            'views.rebuild' => ['command' => 'view:cache', 'type' => 'views', 'action' => 'rebuild', 'label' => 'Views', 'success' => 'Views rebuilt successfully.'],
            'events.clear' => ['command' => 'event:clear', 'type' => 'events', 'action' => 'clear', 'label' => 'Events', 'success' => 'Event cache cleared successfully.'],
            'events.rebuild' => ['command' => 'event:cache', 'type' => 'events', 'action' => 'rebuild', 'label' => 'Events', 'success' => 'Events rebuilt successfully.'],
            'compiled.clear' => ['command' => 'clear-compiled', 'type' => 'compiled', 'action' => 'clear', 'label' => 'Compiled Files', 'success' => 'Compiled files cleared successfully.'],
            'all.clear' => ['command' => 'optimize:clear', 'type' => 'all', 'action' => 'clear', 'label' => 'All cache', 'success' => 'All Laravel caches cleared successfully.'],
        ];
    }

    /**
     * @return list<array{label: string, value: string, help: ?string}>
     */
    private function summary(string $environment): array
    {
        return [
            ['label' => 'Cache driver', 'value' => (string) config('cache.default'), 'help' => 'The cache store configured for this application.'],
            $this->summaryCard('Configuration', $this->fileStatus(base_path('bootstrap/cache/config.php')), null),
            $this->summaryCard('Routes', $this->routeStatus(), null),
            ['label' => 'Views', 'value' => 'Available', 'help' => 'Blade compiles templates as pages are visited. Laravel does not report one cached or empty state.'],
            ['label' => 'Environment', 'value' => $this->environmentLabel($environment), 'help' => null],
        ];
    }

    /**
     * @return list<array<string, mixed>>
     */
    private function rows(): array
    {
        return [
            $this->row('application', 'Application Cache', 'General application and cache-store data.', 'Clears the configured cache store. Laravel fills it again when data is needed.', 'available', 'The cache driver does not report a single cached or empty state.', [
                $this->action('clear', 'admin.cache.application.clear', 'application.clear', 'Clearing…', 'Clear Application Cache?', 'This will clear cached application data. Laravel will regenerate required cache data automatically.', 'Clear cache'),
            ]),
            $this->row('config', 'Configuration', 'Cached Laravel configuration.', 'Stores configuration in one file so the application reads it faster.', $this->fileStatus(base_path('bootstrap/cache/config.php')), 'Cached when bootstrap/cache/config.php exists.', [
                $this->action('clear', 'admin.cache.config.clear', 'config.clear', 'Clearing…', 'Clear configuration cache?', 'This removes the cached configuration file. Laravel will read configuration from the application files again.', 'Clear cache'),
                $this->action('rebuild', 'admin.cache.config.rebuild', 'config.rebuild', 'Rebuilding…', 'Rebuild configuration cache?', 'This writes the current configuration into one cached file.', 'Rebuild'),
            ]),
            $this->row('routes', 'Routes', 'Cached route definitions.', 'Stores the site address list so route registration is faster.', $this->routeStatus(), 'Cached when a routes cache file exists in bootstrap/cache.', [
                $this->action('clear', 'admin.cache.routes.clear', 'routes.clear', 'Clearing…', 'Clear route cache?', 'This removes the cached route file. Laravel will load routes from the route files again.', 'Clear cache'),
                $this->action('rebuild', 'admin.cache.routes.rebuild', 'routes.rebuild', 'Rebuilding…', 'Rebuild route cache?', 'This writes the current routes into a cached file.', 'Rebuild'),
            ]),
            $this->row('views', 'Views', 'Compiled Blade templates.', 'Compiled templates are created when pages are rendered.', 'available', 'Laravel does not keep a single flag for whether every template is compiled.', [
                $this->action('clear', 'admin.cache.views.clear', 'views.clear', 'Clearing…', 'Clear view cache?', 'This removes compiled Blade templates. Laravel compiles them again on the next visit.', 'Clear cache'),
                $this->action('rebuild', 'admin.cache.views.rebuild', 'views.rebuild', 'Rebuilding…', 'Rebuild view cache?', 'This compiles the Blade templates ahead of the next visit.', 'Rebuild'),
            ]),
            $this->row('events', 'Events', 'Cached event and listener discovery.', 'Stores discovered events and listeners.', $this->fileStatus(base_path('bootstrap/cache/events.php')), 'Cached when bootstrap/cache/events.php exists.', [
                $this->action('clear', 'admin.cache.events.clear', 'events.clear', 'Clearing…', 'Clear event cache?', 'This removes the cached event file. Laravel will discover listeners again.', 'Clear cache'),
                $this->action('rebuild', 'admin.cache.events.rebuild', 'events.rebuild', 'Rebuilding…', 'Rebuild event cache?', 'This writes discovered events and listeners into a cached file.', 'Rebuild'),
            ]),
            $this->row('compiled', 'Compiled Files', 'Framework compiled service files.', 'Removes cached framework service and package files.', $this->compiledStatus(), 'Cached when compiled service or package files exist.', [
                $this->action('clear', 'admin.cache.compiled.clear', 'compiled.clear', 'Clearing…', 'Clear compiled files?', 'This removes compiled framework service and package files. Laravel recreates them when needed.', 'Clear cache'),
            ]),
        ];
    }

    /**
     * @param  list<array<string, string>|null>  $actions
     * @return array<string, mixed>
     */
    private function row(string $type, string $name, string $description, string $help, string $status, string $statusHelp, array $actions): array
    {
        $actions = array_values(array_filter($actions));

        return [
            'type' => $type,
            'name' => $name,
            'description' => $description,
            'help' => $help,
            'status' => $status,
            'status_label' => $this->statusLabel($status),
            'status_help' => $statusHelp,
            'last' => $this->lastOperation($type),
            'actions' => $actions,
        ];
    }

    /**
     * @return array<string, string>|null
     */
    private function action(string $kind, string $route, string $operation, string $pending, string $title, string $text, string $confirm): ?array
    {
        $command = $this->operations()[$operation]['command'] ?? null;
        if ($command === null || ! $this->supports($command)) {
            return null;
        }

        return [
            'kind' => $kind,
            'route' => $route,
            'pending' => $pending,
            'title' => $title,
            'text' => $text,
            'confirm' => $confirm,
        ];
    }

    private function lastOperation(string $type): ?CacheOperationLog
    {
        return CacheOperationLog::query()
            ->with('user:id,name')
            ->where('success', true)
            ->where(function ($query) use ($type) {
                $query->where('cache_type', $type)
                    ->orWhere(fn ($inner) => $inner->where('cache_type', 'all')->where('action', 'clear'));
            })
            ->latest('id')
            ->first();
    }

    /**
     * @return array{label: string, value: string, help: ?string}
     */
    private function summaryCard(string $label, string $status, ?string $help): array
    {
        return [
            'label' => $label,
            'value' => $this->statusLabel($status),
            'help' => $status === 'unknown' ? 'This state could not be read from the server.' : $help,
        ];
    }

    private function fileStatus(string $path): string
    {
        $directory = dirname($path);
        if (! is_dir($directory) || ! is_readable($directory)) {
            return 'unknown';
        }

        return is_file($path) ? 'cached' : 'not_cached';
    }

    private function routeStatus(): string
    {
        $directory = base_path('bootstrap/cache');
        if (! is_dir($directory) || ! is_readable($directory)) {
            return 'unknown';
        }

        return (glob($directory.'/routes-*.php') ?: []) !== [] ? 'cached' : 'not_cached';
    }

    private function compiledStatus(): string
    {
        $directory = base_path('bootstrap/cache');
        if (! is_dir($directory) || ! is_readable($directory)) {
            return 'unknown';
        }

        $services = app()->getCachedServicesPath();
        $packages = app()->getCachedPackagesPath();

        return (is_file($services) || is_file($packages)) ? 'cached' : 'not_cached';
    }

    private function statusLabel(string $status): string
    {
        return match ($status) {
            'cached' => 'Cached',
            'not_cached' => 'Not cached',
            'available' => 'Available',
            default => 'Unknown',
        };
    }

    private function environmentLabel(string $environment): string
    {
        return match ($environment) {
            'local' => 'Local',
            'staging' => 'Staging',
            'production' => 'Production',
            'testing' => 'Testing',
            default => $environment,
        };
    }
}
