<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreLeadRequest;
use App\Services\LeadService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;

class ContactController extends Controller
{
    public function store(StoreLeadRequest $request, LeadService $leads): JsonResponse|RedirectResponse
    {
        $leads->storeFromRequest($request);

        $payload = [
            'ok' => true,
            'message' => 'Thank you for contacting CEBINOVA Technologies.',
            'detail' => 'Our team will review your requirement and get in touch with you shortly.',
        ];

        if ($request->expectsJson() || $request->ajax()) {
            return response()->json($payload);
        }

        return redirect()
            ->route('contact')
            ->with('status', $payload['message'])
            ->with('status_detail', $payload['detail']);
    }
}
