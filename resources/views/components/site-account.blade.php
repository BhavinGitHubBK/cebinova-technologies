@php
    $customer = auth('customer')->user();
@endphp
@if (session('customer_signed_out'))
    <div class="site-account-signin">
        <x-button type="button" variant="secondary" size="sm" data-visitor-signin>Sign in</x-button>
    </div>
@else
    <div class="site-account" data-site-account @unless ($customer) hidden @endunless>
        <button type="button" class="site-account-trigger" data-site-account-trigger aria-haspopup="true">
            <span class="site-account-mark" data-site-account-mark aria-hidden="true">{{ $customer ? mb_strtoupper(mb_substr($customer->name, 0, 1)) : '' }}</span>
            <span class="site-account-name" data-site-account-name>{{ $customer->name ?? '' }}</span>
            <svg class="site-account-chevron" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M6 9l6 6 6-6"/></svg>
        </button>
        <div class="site-account-menu">
            <div class="site-account-panel">
                <div class="site-account-menu-head">
                    <span class="site-account-mark" data-site-account-mark aria-hidden="true">{{ $customer ? mb_strtoupper(mb_substr($customer->name, 0, 1)) : '' }}</span>
                    <div class="site-account-menu-id">
                        <p class="site-account-menu-name" data-site-account-name>{{ $customer->name ?? '' }}</p>
                        <p class="site-account-menu-mobile" data-site-account-mobile>{{ $customer?->mobileDisplay() }}</p>
                    </div>
                </div>
                <div class="site-account-menu-actions">
                    <a href="{{ route('account.show') }}">
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM4 21a8 8 0 0116 0"/></svg>
                        View profile
                    </a>
                    <form method="POST" action="{{ route('account.logout') }}">
                        @csrf
                        <button type="submit">
                            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M10 7V5a1 1 0 011-1h8a1 1 0 011 1v14a1 1 0 01-1 1h-8a1 1 0 01-1-1v-2M15 12H4m0 0l3-3m-3 3l3 3"/></svg>
                            Log out
                        </button>
                    </form>
                </div>
            </div>
        </div>
    </div>
@endif
