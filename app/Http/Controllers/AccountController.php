<?php

namespace App\Http\Controllers;

use App\Models\AnalyticsAccountEvent;
use App\Models\AnalyticsVisitor;
use App\Models\Customer;
use App\Services\Account\CustomerLogin;
use App\Services\Analytics\VisitorCapture;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class AccountController extends Controller
{
    public function show(): View|RedirectResponse
    {
        $customer = Auth::guard('customer')->user();

        if (! $customer) {
            return redirect()->route('home');
        }

        $customer->load('visitor');

        return view('account.show', [
            'customer' => $customer,
        ]);
    }

    public function store(Request $request, CustomerLogin $accounts): RedirectResponse
    {
        $key = (string) $request->cookie(VisitorCapture::COOKIE);
        $visitor = AnalyticsVisitor::query()->where('uuid', $key)->first();

        if ($visitor) {
            $accounts->signIn($visitor, $request->ip(), true);
        }

        return Auth::guard('customer')->check()
            ? redirect()->route('account.show')
            : redirect()->route('home');
    }

    public function destroy(Request $request): RedirectResponse
    {
        $previous = url()->previous();
        $visitor = AnalyticsVisitor::query()
            ->where('uuid', (string) $request->cookie(VisitorCapture::COOKIE))
            ->first();
        $customer = Auth::guard('customer')->user();
        if (! $customer instanceof Customer && filled($visitor?->mobile)) {
            $customer = Customer::query()->where('mobile', $visitor->mobile)->first();
        }

        if ($customer instanceof Customer || $visitor) {
            AnalyticsAccountEvent::record(
                AnalyticsAccountEvent::LOGOUT,
                $customer instanceof Customer ? $customer : null,
                $visitor?->id ?? ($customer instanceof Customer ? $customer->analytics_visitor_id : null),
                $request->ip(),
                parse_url($previous, PHP_URL_PATH) ?: '/',
            );
        }

        Auth::guard('customer')->logout();
        $request->session()->put('customer_signed_out', true);
        $request->session()->regenerateToken();

        $redirect = str_contains($previous, '/account')
            ? redirect()->route('home')
            : redirect()->back();

        return $redirect
            ->with('logged_out', 'You have been logged out successfully.')
            ->withCookie(cookie()->forget(VisitorCapture::COOKIE));
    }
}
