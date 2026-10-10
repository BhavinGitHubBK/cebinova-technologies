@extends('layouts.admin')

@section('title', 'Live chats')
@section('breadcrumb', 'Admin / Live chats')
@section('heading', 'Live chats')

@section('content')
<section class="lead-board">
    <div class="lead-summary">
        <p><strong>{{ $chats->total() }}</strong><span>chat conversations</span></p>
    </div>

    <div class="admin-card" style="margin-bottom:1rem;">
        <form method="GET" action="{{ route('admin.live-chats.index') }}" class="solution-search" style="display:flex;gap:.75rem;align-items:end;flex-wrap:wrap;">
            <div class="admin-field" style="min-width:16rem;flex:1;">
                <label class="admin-label" for="chat-search">Search</label>
                <input id="chat-search" class="admin-input" name="q" value="{{ $search }}" placeholder="Name or mobile">
            </div>
            <button class="admin-btn" type="submit">Search</button>
            @if ($search !== '')
                <a class="admin-btn admin-btn-ghost" href="{{ route('admin.live-chats.index') }}">Clear</a>
            @endif
        </form>
    </div>

    <div class="admin-card admin-table-wrap">
        <table class="admin-table lead-table visitor-table">
            <thead>
                <tr>
                    <th>Name</th>
                    <th>Mobile</th>
                    <th>Last question</th>
                    <th>Started</th>
                    <th>Last active</th>
                </tr>
            </thead>
            <tbody>
                @forelse ($chats as $chat)
                    <tr>
                        <td><a href="{{ route('admin.live-chats.show', $chat) }}">{{ $chat->name }}</a></td>
                        <td>{{ $chat->mobileDisplay() }}</td>
                        <td>{{ $chat->question ?: 'Waiting for a question' }}</td>
                        <td>{{ ist($chat->created_at) }}</td>
                        <td>{{ ist($chat->updated_at) }}</td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="5">No live chats yet.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    {{ $chats->links() }}
</section>
@endsection
