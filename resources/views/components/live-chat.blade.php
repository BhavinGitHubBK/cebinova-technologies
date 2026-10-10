@php
    $chatQuestions = collect(\App\Support\LiveChatScript::questions())
        ->map(fn (array $question, string $key) => ['key' => $key, 'label' => $question['label']])
        ->values();
    $logoVer = (int) @filemtime(public_path('assets/brand/cebinova-c-icon.png'));
    $logoPng = asset('assets/brand/cebinova-c-icon.png').($logoVer ? '?v='.$logoVer : '');
    $chatName = \App\Models\Customer::signedIn()?->firstName() ?? '';
@endphp
<div id="floatChat" class="live-chat" data-live-chat data-chat-url="{{ route('chat.store') }}" data-chat-name="{{ $chatName }}" data-chat-topics='@json($chatQuestions)'>
    <div class="live-chat-panel" data-chat-panel hidden>
        <div class="live-chat-head">
            <span class="live-chat-brand" aria-hidden="true">
                <img src="{{ $logoPng }}" alt="" width="48" height="40" decoding="async">
            </span>
            <div class="live-chat-copy">
                <p class="live-chat-kicker">Live chat</p>
                <p class="live-chat-title">CEBINOVA Support Team</p>
                <p class="live-chat-status"><span class="live-chat-dot" aria-hidden="true"></span> Online now</p>
            </div>
            <button type="button" class="live-chat-close" data-chat-close aria-label="Close live chat">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M6 6l12 12M18 6L6 18"/></svg>
            </button>
        </div>
        <div class="live-chat-log" data-chat-log></div>
        <p class="live-chat-error" data-chat-error hidden></p>
        <div class="live-chat-questions" data-chat-questions hidden>
            <p class="live-chat-questions-label">Common questions</p>
        </div>
        <button type="button" class="live-chat-more" data-chat-more hidden>Ask another question</button>
        <form class="live-chat-form" data-chat-form>
            <label class="sr-only" for="live-chat-input">Your reply</label>
            <input id="live-chat-input" class="live-chat-input" data-chat-input type="text" maxlength="80" autocomplete="name" placeholder="Your name">
            <button class="live-chat-send" type="submit">Send</button>
        </form>
    </div>
    <button type="button" class="live-chat-launch" data-chat-toggle aria-expanded="false" aria-label="Open live chat">
        <span class="live-chat-launch-ring" aria-hidden="true"></span>
        <span class="live-chat-launch-icon" aria-hidden="true">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" aria-hidden="true">
                <path stroke-linecap="round" stroke-linejoin="round" d="M7 8h10M7 12h6"/>
                <path stroke-linecap="round" stroke-linejoin="round" d="M5 5h14a2 2 0 0 1 2 2v8a2 2 0 0 1-2 2H9l-4 3v-3H5a2 2 0 0 1-2-2V7a2 2 0 0 1 2-2z"/>
            </svg>
        </span>
    </button>
</div>
