<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Services\Admin\AdminNotifications;
use Illuminate\Http\JsonResponse;

class NotificationController extends Controller
{
    public function seen(AdminNotifications $notifications): JsonResponse
    {
        $notifications->markSeen();

        return response()->json(['ok' => true]);
    }
}
