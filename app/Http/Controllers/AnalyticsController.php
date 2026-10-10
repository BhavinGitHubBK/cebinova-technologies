<?php

namespace App\Http\Controllers;

use App\Models\AnalyticsVisitor;
use App\Services\Account\CustomerLogin;
use App\Services\Analytics\VisitorCapture;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class AnalyticsController extends Controller
{
    public function visit(Request $request, VisitorCapture $visitors, CustomerLogin $accounts): JsonResponse
    {
        $data = $request->validate([
            'path' => ['required', 'string', 'max:255'],
            'title' => ['nullable', 'string', 'max:180'],
            'referrer' => ['nullable', 'string', 'max:500'],
            'visitor' => ['nullable', 'uuid'],
        ]);

        if ($this->skipped($data['path'])) {
            return response()->json(['ok' => true, 'tracked' => false]);
        }

        $key = $data['visitor'] ?? null;
        if (
            $key
            && session('customer_signed_out')
            && ! Auth::guard('customer')->check()
            && AnalyticsVisitor::query()->where('uuid', $key)->whereNotNull('mobile')->exists()
        ) {
            $key = null;
        }

        $visitor = $visitors->record(
            $request,
            $data['path'],
            $data['title'] ?? null,
            $data['referrer'] ?? $request->headers->get('referer'),
            $key,
        );

        return $this->payload($visitor, $accounts);
    }

    public function identify(Request $request, VisitorCapture $visitors, CustomerLogin $accounts): JsonResponse
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:80'],
            'mobile' => ['required', 'regex:/^(?:\+?91[\s-]?)?[6-9]\d{9}$/'],
            'visitor' => ['nullable', 'uuid'],
            'path' => ['nullable', 'string', 'max:255'],
        ], [
            'mobile.regex' => 'Enter a 10-digit mobile number.',
        ]);

        $mobile = preg_replace('/\D+/', '', $data['mobile']) ?? '';
        $mobile = substr($mobile, -10);

        $visitor = $visitors->identify(
            $request,
            trim($data['name']),
            $mobile,
            $data['visitor'] ?? null,
            $data['path'] ?? '/',
        );

        return $this->payload($visitor, $accounts, true);
    }

    public function locate(Request $request, VisitorCapture $visitors): JsonResponse
    {
        $data = $request->validate([
            'visitor' => ['required', 'uuid'],
            'latitude' => ['required', 'numeric', 'between:-90,90'],
            'longitude' => ['required', 'numeric', 'between:-180,180'],
            'accuracy' => ['nullable', 'integer', 'min:0', 'max:100000'],
        ]);

        $visitor = $visitors->visitor($data['visitor'], '/', null, $request->ip());
        $visitor = $visitors->pin(
            $visitor,
            (float) $data['latitude'],
            (float) $data['longitude'],
            isset($data['accuracy']) ? (int) $data['accuracy'] : null,
        );

        return $this->payload($visitor);
    }

    private function payload(AnalyticsVisitor $visitor, ?CustomerLogin $accounts = null, bool $explicit = false): JsonResponse
    {
        $customer = $accounts?->signIn($visitor, request()->ip(), $explicit);

        return response()
            ->json([
                'ok' => true,
                'tracked' => true,
                'visitor' => $visitor->uuid,
                'identified' => filled($visitor->name) && filled($visitor->mobile),
                'signed_out' => (bool) session('customer_signed_out'),
                'located' => $visitor->latitude !== null && $visitor->longitude !== null,
                'account' => $customer ? [
                    'name' => $customer->name,
                    'mobile' => $customer->mobileDisplay(),
                ] : null,
            ])
            ->withCookie(cookie(
                VisitorCapture::COOKIE,
                $visitor->uuid,
                60 * 24 * 400,
            ));
    }

    private function skipped(string $path): bool
    {
        $path = '/'.ltrim(parse_url($path, PHP_URL_PATH) ?: '/', '/');

        return str_starts_with($path, '/admin') || str_starts_with($path, '/up');
    }
}
