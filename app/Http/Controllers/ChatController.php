<?php

namespace App\Http\Controllers;

use App\Models\Customer;
use App\Models\LiveChat;
use App\Services\Account\CustomerLogin;
use App\Services\Analytics\VisitorCapture;
use App\Support\LiveChatScript;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class ChatController extends Controller
{
    public function store(Request $request, VisitorCapture $visitors, CustomerLogin $accounts): JsonResponse
    {
        $questions = LiveChatScript::questions();
        $known = Customer::signedIn();

        $request->merge([
            'name' => trim((string) $request->input('name')) ?: $known?->name,
            'mobile' => substr(preg_replace('/\D+/', '', (string) $request->input('mobile')) ?? '', -10) ?: $known?->mobile,
        ]);

        $data = $request->validate([
            'name' => ['required', 'string', 'max:80'],
            'mobile' => ['required', 'regex:/^[6-9]\d{9}$/'],
            'chat_id' => ['nullable', 'integer'],
            'token' => ['nullable', 'string', 'size:40'],
            'question' => ['nullable', 'string', 'in:'.implode(',', array_keys($questions))],
            'visitor' => ['nullable', 'uuid'],
            'path' => ['nullable', 'string', 'max:255'],
        ], [
            'mobile.regex' => 'Enter a 10-digit mobile number.',
        ]);

        $name = trim($data['name']);
        $mobile = $data['mobile'];
        $customer = $known;
        $visitorKey = null;
        if (! $known || $known->mobile !== $mobile) {
            $visitor = $visitors->identify(
                $request,
                $name,
                $mobile,
                $request->cookie(VisitorCapture::COOKIE) ?: ($data['visitor'] ?? null),
                $data['path'] ?? '/',
            );
            $customer = $accounts->signIn($visitor, $request->ip(), true);
            $visitorKey = $visitor->uuid;
        }
        $firstName = Customer::firstNameOf($name);
        $greeting = 'Hello '.$firstName.'! What would you like to know?';

        $chat = LiveChat::query()
            ->where('id', $data['chat_id'] ?? 0)
            ->where('token', $data['token'] ?? '')
            ->where('mobile', $mobile)
            ->where('updated_at', '>=', now()->subMinutes(LiveChat::IDLE_MINUTES))
            ->first();

        $chat ??= LiveChat::query()->create([
            'token' => Str::random(40),
            'name' => $name,
            'mobile' => $mobile,
            'messages' => $known
                ? [['from' => 'bot', 'text' => $greeting]]
                : [
                    ['from' => 'visitor', 'text' => $name],
                    ['from' => 'visitor', 'text' => $mobile],
                    ['from' => 'bot', 'text' => $greeting],
                ],
        ]);

        $reply = $greeting;
        $link = null;

        if (filled($data['question'] ?? null)) {
            $picked = $questions[$data['question']];
            $messages = $chat->messages ?? [];
            $messages[] = ['from' => 'visitor', 'text' => $picked['label']];
            $messages[] = ['from' => 'bot', 'text' => $picked['answer']];
            $chat->update([
                'name' => $name,
                'question' => $picked['label'],
                'messages' => $messages,
            ]);
            $reply = $picked['answer'];
            $link = $picked['link'];
        }

        $response = response()->json([
            'id' => $chat->id,
            'token' => $chat->token,
            'name' => $firstName,
            'reply' => $reply,
            'link' => $link,
            'questions' => $this->labels($questions),
            'visitor' => $visitorKey,
            'account' => $customer ? [
                'name' => $customer->name,
                'mobile' => $customer->mobileDisplay(),
            ] : null,
        ]);

        return $visitorKey
            ? $response->withCookie(cookie(VisitorCapture::COOKIE, $visitorKey, 60 * 24 * 400))
            : $response;
    }

    /**
     * @param  array<string, array{label: string, answer: string, link: array{label: string, url: string}}>  $questions
     * @return list<array{key: string, label: string}>
     */
    private function labels(array $questions): array
    {
        $labels = [];

        foreach ($questions as $key => $question) {
            $labels[] = ['key' => $key, 'label' => $question['label']];
        }

        return $labels;
    }
}
