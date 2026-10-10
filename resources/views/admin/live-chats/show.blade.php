@extends('layouts.admin')

@section('title', $chat->name)
@section('breadcrumb', 'Admin / Live chats / '.$chat->name)
@section('heading', $chat->name)

@section('content')
@php
    $exchanges = $chat->exchanges();
    $answered = collect($exchanges)->whereNotNull('answer')->count();
    $wa = '91'.$chat->mobile;
@endphp

<section class="lead-board lead-detail">
    <div class="admin-card lead-detail-head">
        <div class="lead-detail-meta">
            <span class="admin-badge is-new">Live chat</span>
            <span>Started {{ ist($chat->created_at) }}</span>
            <span>Last active {{ ist($chat->updated_at) }}</span>
            <span>{{ count($exchanges) }} {{ \Illuminate\Support\Str::plural('question', count($exchanges)) }}</span>
        </div>
        <div class="lead-detail-actions">
            <a class="lead-contact-btn" href="tel:+{{ $wa }}"><x-mark name="phone" class="h-4 w-4" /> Call</a>
            <a class="lead-contact-btn" href="https://wa.me/{{ $wa }}" target="_blank" rel="noopener"><x-mark name="whatsapp" class="h-4 w-4" /> WhatsApp</a>
            <a class="lead-contact-btn" href="{{ route('admin.live-chats.index') }}"><x-mark name="list" class="h-4 w-4" /> All chats</a>
        </div>
    </div>

    <article class="admin-card">
        <h2>Visitor</h2>
        <dl class="lead-facts">
            <div>
                <dt>Name</dt>
                <dd>{{ $chat->name }}</dd>
            </div>
            <div>
                <dt>Mobile</dt>
                <dd><a href="tel:+{{ $wa }}">{{ $chat->mobileDisplay() }}</a></dd>
            </div>
            <div>
                <dt>Questions asked</dt>
                <dd>{{ count($exchanges) }}</dd>
            </div>
            <div>
                <dt>Answered</dt>
                <dd>{{ $answered }}</dd>
            </div>
            <div class="is-wide">
                <dt>Last question</dt>
                <dd @class(['is-empty' => blank($chat->question)])>{{ $chat->question ?: 'Waiting for a question' }}</dd>
            </div>
        </dl>
    </article>

    <article class="admin-card" style="margin-top:1rem;">
        <h2>What they asked</h2>
        @if ($exchanges === [])
            <p class="chat-review-empty">They shared a name and mobile. No question yet.</p>
        @else
            <ol class="chat-review">
                @foreach ($exchanges as $exchange)
                    <li>
                        <p class="chat-review-q">{{ $exchange['question'] }}</p>
                        <p @class(['chat-review-a', 'is-empty' => blank($exchange['answer'])])>{{ $exchange['answer'] ?: 'No answer saved.' }}</p>
                    </li>
                @endforeach
            </ol>
        @endif
    </article>
</section>
@endsection