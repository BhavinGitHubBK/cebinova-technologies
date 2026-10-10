<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;

class Customer extends Authenticatable
{
    protected $fillable = [
        'name',
        'mobile',
        'analytics_visitor_id',
        'last_login_at',
        'last_login_ip',
    ];

    protected $hidden = [
        'remember_token',
    ];

    protected function casts(): array
    {
        return [
            'last_login_at' => 'datetime',
        ];
    }

    public static function signedIn(): ?self
    {
        if (session('customer_signed_out')) {
            return null;
        }

        $customer = Auth::guard('customer')->user();

        return $customer instanceof self ? $customer : null;
    }

    public static function firstNameOf(string $name): string
    {
        return Str::ucfirst((string) Str::of($name)->trim()->explode(' ')->first()) ?: trim($name);
    }

    public function firstName(): string
    {
        return self::firstNameOf($this->name);
    }

    public function visitor(): BelongsTo
    {
        return $this->belongsTo(AnalyticsVisitor::class, 'analytics_visitor_id');
    }

    public function mobileDisplay(): string
    {
        return strlen($this->mobile) === 10
            ? substr($this->mobile, 0, 5).' '.substr($this->mobile, 5)
            : $this->mobile;
    }
}
