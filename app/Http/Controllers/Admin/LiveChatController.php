<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\LiveChat;
use Illuminate\Http\Request;
use Illuminate\View\View;

class LiveChatController extends Controller
{
    public function index(Request $request): View
    {
        $search = trim((string) $request->query('q', ''));

        $chats = LiveChat::query()
            ->when($search !== '', function ($query) use ($search) {
                $digits = preg_replace('/\D+/', '', $search) ?? '';
                $query->where(function ($query) use ($search, $digits) {
                    $query->where('name', 'like', '%'.$search.'%');
                    if ($digits !== '') {
                        $query->orWhere('mobile', 'like', '%'.$digits.'%');
                    }
                });
            })
            ->latest('updated_at')
            ->latest('id')
            ->paginate(25)
            ->withQueryString();

        return view('admin.live-chats.index', [
            'chats' => $chats,
            'search' => $search,
        ]);
    }

    public function show(LiveChat $liveChat): View
    {
        return view('admin.live-chats.show', [
            'chat' => $liveChat,
        ]);
    }
}
