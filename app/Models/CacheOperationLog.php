<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class CacheOperationLog extends Model
{
    protected $fillable = [
        'cache_type',
        'action',
        'cleared_at',
        'cleared_by',
        'duration_ms',
        'success',
        'error_message',
    ];

    protected function casts(): array
    {
        return [
            'cleared_at' => 'datetime',
            'success' => 'boolean',
            'duration_ms' => 'integer',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'cleared_by');
    }

    public function cacheLabel(): string
    {
        return match ($this->cache_type) {
            'application' => 'Application Cache',
            'config' => 'Configuration',
            'routes' => 'Routes',
            'views' => 'Views',
            'events' => 'Events',
            'compiled' => 'Compiled Files',
            'all' => 'All cache',
            default => (string) $this->cache_type,
        };
    }
}
