<?php

namespace App\Services\System;

use App\Services\Promotions\PromotionEngine;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * Cleanup kept for a future System maintenance page.
 * These actions are not cache operations and are not shown on Cache Management.
 */
class MaintenanceCleanup
{
    public function clearLogs(): void
    {
        foreach ($this->logFiles() as $file) {
            file_put_contents($file, '');
        }
    }

    public function clearExpiredSessions(): void
    {
        if (config('session.driver') === 'database' && Schema::hasTable('sessions')) {
            DB::table('sessions')
                ->where('last_activity', '<', $this->sessionCutoff())
                ->where('id', '!=', session()->getId())
                ->delete();

            return;
        }

        foreach ($this->expiredSessionFiles() as $file) {
            unlink($file);
        }
    }

    public function clearTempUploads(): void
    {
        foreach ($this->tempUploadFiles() as $file) {
            unlink($file);
        }
    }

    /**
     * @return list<string>
     */
    public function logFiles(): array
    {
        return array_values(array_filter(glob(storage_path('logs/*.log')) ?: [], 'is_file'));
    }

    public function expiredSessionCount(): int
    {
        if (config('session.driver') !== 'database' || ! Schema::hasTable('sessions')) {
            return count($this->expiredSessionFiles());
        }

        return DB::table('sessions')
            ->where('last_activity', '<', $this->sessionCutoff())
            ->where('id', '!=', session()->getId())
            ->count();
    }

    /**
     * @return list<string>
     */
    public function tempUploadFiles(): array
    {
        $files = [];
        foreach ([storage_path('app/tmp'), storage_path('app/livewire-tmp')] as $directory) {
            if (! is_dir($directory)) {
                continue;
            }
            foreach (glob($directory.'/*') ?: [] as $file) {
                if (is_file($file) && ! str_ends_with($file, '.gitignore')) {
                    $files[] = $file;
                }
            }
        }

        return $files;
    }

    public function applicationCacheInUse(): bool
    {
        if (Cache::has('site_settings') || Cache::has(PromotionEngine::CACHE_KEY)) {
            return true;
        }

        $files = glob(storage_path('framework/cache/data/*/*/*')) ?: [];

        return count(array_filter($files, 'is_file')) > 0;
    }

    private function sessionCutoff(): int
    {
        return now()->subMinutes((int) config('session.lifetime'))->getTimestamp();
    }

    /**
     * @return list<string>
     */
    private function expiredSessionFiles(): array
    {
        $current = storage_path('framework/sessions/'.session()->getId());
        $cutoff = $this->sessionCutoff();
        $files = [];
        foreach (glob(storage_path('framework/sessions/*')) ?: [] as $file) {
            if (! is_file($file) || $file === $current || str_ends_with($file, '.gitignore')) {
                continue;
            }
            if (filemtime($file) < $cutoff) {
                $files[] = $file;
            }
        }

        return $files;
    }
}
