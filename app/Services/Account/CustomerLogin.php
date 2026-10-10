<?php

namespace App\Services\Account;

use App\Models\AnalyticsAccountEvent;
use App\Models\AnalyticsVisitor;
use App\Models\Customer;
use Illuminate\Support\Facades\Auth;

class CustomerLogin
{
    public function signIn(AnalyticsVisitor $visitor, ?string $ip, bool $explicit = false): ?Customer
    {
        if ($explicit) {
            session()->forget('customer_signed_out');
        } elseif (session('customer_signed_out')) {
            return null;
        }

        if (blank($visitor->name) || blank($visitor->mobile)) {
            return null;
        }

        $guard = Auth::guard('customer');
        $current = $guard->user();

        if (
            $current instanceof Customer
            && $current->mobile === $visitor->mobile
            && $current->name === $visitor->name
            && (int) $current->analytics_visitor_id === (int) $visitor->id
        ) {
            if (! request()->cookies->has($guard->getRecallerName())) {
                $guard->login($current, true);
            }

            return $current;
        }

        $customer = Customer::query()->updateOrCreate(
            ['mobile' => $visitor->mobile],
            [
                'name' => $visitor->name,
                'analytics_visitor_id' => $visitor->id,
            ],
        );

        if (! $current instanceof Customer || (int) $current->getAuthIdentifier() !== (int) $customer->id) {
            $customer->forceFill([
                'last_login_at' => now(),
                'last_login_ip' => $ip,
            ])->save();
            $guard->login($customer, true);
            $customer = $customer->fresh();
            AnalyticsAccountEvent::record(AnalyticsAccountEvent::LOGIN, $customer, $visitor->id, $ip, $visitor->exit_path);
        }

        return $customer;
    }
}
