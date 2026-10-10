<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class RespondJsonToAdminAjax
{
    public function handle(Request $request, Closure $next): Response
    {
        $response = $next($request);

        if (! $request->headers->has('X-Admin-Ajax') || ! $response->isRedirection()) {
            return $response;
        }

        $errors = session('errors');
        if ($errors && method_exists($errors, 'any') && $errors->any()) {
            session()->forget('errors');

            return response()->json([
                'ok' => false,
                'message' => $errors->first(),
            ], 422);
        }

        $message = session()->pull('success') ?: session()->pull('status') ?: 'Saved.';

        return response()->json([
            'ok' => true,
            'message' => $message,
            'redirect' => $response->headers->get('Location'),
        ]);
    }
}
