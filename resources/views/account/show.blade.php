@extends('layouts.app')

@section('title', 'Your account | CEBINOVA Technologies')
@section('description', 'Your CEBINOVA account.')

@section('content')
<section class="account-page">
    <div class="account-card">
        <header class="account-head">
            <div class="account-mark" aria-hidden="true">{{ mb_strtoupper(mb_substr($customer->name, 0, 1)) }}</div>
            <div class="account-id">
                <p class="account-kicker">Your account</p>
                <h1>{{ $customer->name }}</h1>
                <p class="account-mobile">{{ $customer->mobileDisplay() }}</p>
            </div>
        </header>

        <dl class="account-facts">
            <div>
                <dt>Mobile</dt>
                <dd>{{ $customer->mobileDisplay() }}</dd>
            </div>
            <div>
                <dt>Location</dt>
                <dd>{{ $customer->visitor?->place() ?? 'Not available yet' }}</dd>
            </div>
            <div>
                <dt>Last login</dt>
                <dd>{{ ist($customer->last_login_at) }}</dd>
            </div>
        </dl>

        <div class="account-actions">
            <a href="{{ route('home') }}">Back to website</a>
            <form method="POST" action="{{ route('account.logout') }}">
                @csrf
                <button type="submit">Log out</button>
            </form>
        </div>
    </div>
</section>
@endsection
