<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class AnalyticsAccountEvent extends Model
{
    public const LOGIN = 'login';

    public const LOGOUT = 'logout';

    protected $fillable = [
        'analytics_visitor_id',
        'customer_id',
        'action',
        'ip_address',
        'path',
        'occurred_at',
    ];

    protected function casts(): array
    {
        return [
            'occurred_at' => 'datetime',
        ];
    }

    public function visitor(): BelongsTo
    {
        return $this->belongsTo(AnalyticsVisitor::class, 'analytics_visitor_id');
    }

    public function customer(): BelongsTo
    {
        return $this->belongsTo(Customer::class);
    }

    public function label(): string
    {
        return $this->action === self::LOGOUT ? 'Logged out' : 'Logged in';
    }

    public static function record(string $action, ?Customer $customer, ?int $visitorId, ?string $ip, ?string $path = null): void
    {
        static::query()->create([
            'analytics_visitor_id' => $visitorId,
            'customer_id' => $customer?->getKey(),
            'action' => $action,
            'ip_address' => $ip,
            'path' => $path ? mb_substr($path, 0, 255) : null,
            'occurred_at' => now(),
        ]);
    }
}
