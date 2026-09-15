@php
    $growth = config('sarvix.marketing.growth');
    $defaultPlan = $growth['plans']['yearly'];
@endphp

<article class="overflow-hidden rounded-[1.15rem] border border-gold/40 bg-navy text-white shadow-[0_24px_50px_rgba(11,31,58,0.22)]">
    <div class="grid lg:grid-cols-12">
        <div class="border-b border-white/10 px-6 py-8 sm:px-8 lg:col-span-5 lg:border-b-0 lg:border-r lg:border-white/10">
            <p class="inline-flex rounded-full bg-gold px-3 py-1 text-[10px] font-bold uppercase tracking-[0.18em] text-navy-deep">{{ $growth['badge'] }} · Most Popular</p>
            <h2 class="mt-4 text-[1.85rem] font-extrabold tracking-tight text-white sm:text-4xl">{{ $growth['heading'] }}</h2>
            <p class="mt-4 text-[17px] leading-relaxed text-white/75">{{ $growth['subheading'] }}</p>
            <p class="mt-3 rounded-xl border border-gold/30 bg-gold/10 px-4 py-3 text-[14.5px] font-semibold leading-snug text-gold">{{ $growth['why'] }}</p>

            <div class="mt-7 flex items-stretch gap-1.5 sm:mt-8 sm:gap-3">
                <div class="flex min-w-0 flex-1 items-center justify-center rounded-xl border border-white/12 bg-white/[0.06] px-1.5 py-2.5 text-center sm:px-3 sm:py-3">
                    <p class="text-[9px] font-bold uppercase leading-tight tracking-wider text-gold sm:text-[11px]">Regular Marketing</p>
                </div>
                <span class="flex items-center text-base font-extrabold text-gold sm:text-lg" aria-hidden="true">+</span>
                <div class="flex min-w-0 flex-1 items-center justify-center rounded-xl border border-white/12 bg-white/[0.06] px-1.5 py-2.5 text-center sm:px-3 sm:py-3">
                    <p class="text-[9px] font-bold uppercase leading-tight tracking-wider text-gold sm:text-[11px]">Festival Marketing</p>
                </div>
                <span class="flex items-center text-base font-extrabold text-gold sm:text-lg" aria-hidden="true">=</span>
                <div class="flex min-w-0 flex-1 items-center justify-center rounded-xl border border-gold/40 bg-gold/10 px-1.5 py-2.5 text-center sm:px-3 sm:py-3">
                    <p class="text-[9px] font-bold uppercase leading-tight tracking-wider text-gold sm:text-[11px]">Complete Growth</p>
                </div>
            </div>

            <div class="mt-7 grid grid-cols-2 gap-2.5" role="tablist" aria-label="Complete Growth durations">
                @foreach ($growth['plans'] as $plan)
                    <button
                        type="button"
                        class="growth-duration js-growth-duration {{ $plan['label'] === 'Yearly' ? 'is-active' : '' }}"
                        data-url="{{ package_enquiry_url($growth['service'], $plan['label']) }}"
                        data-whatsapp="{{ package_whatsapp_url($growth['service'], $plan['label']) }}"
                        data-price="{{ sarvix_inr($plan['price']) }}"
                        data-period="{{ $plan['period'] }}"
                        data-duration="{{ $plan['label'] }}"
                        data-stack="{{ \App\Support\MarketingPackages::priceHeadline($growth['service'], $plan['label']) }}"
                        data-sticky-name="Complete Growth · {{ $plan['label'] }}"
                        data-sticky-price="{{ \App\Support\MarketingPackages::priceHeadline($growth['service'], $plan['label']) }}"
                        data-sticky-cta="{{ package_enquiry_url($growth['service'], $plan['label']) }}"
                        data-sticky-wa="{{ package_whatsapp_url($growth['service'], $plan['label']) }}"
                    >
                        <span class="growth-duration-label">{{ $plan['label'] }}</span>
                        <span class="growth-duration-price">{{ sarvix_inr($plan['price']) }}</span>
                        @if ($plan['badge'] ?? null)
                            <span class="growth-duration-badge">{{ $plan['badge'] }}</span>
                        @endif
                    </button>
                @endforeach
            </div>

            <p class="js-growth-price mt-7 whitespace-nowrap text-[2rem] font-extrabold leading-none tracking-tight text-white sm:text-[2.35rem]">{{ sarvix_inr($defaultPlan['price']) }}</p>
            <p class="js-growth-period mt-1.5 text-[15px] font-semibold text-white/60">{{ $defaultPlan['period'] }}</p>
            <p class="js-growth-stack mt-3 text-[14.5px] font-semibold leading-snug text-white/85">{{ \App\Support\MarketingPackages::priceHeadline($growth['service'], 'Yearly') }}</p>
            <p class="js-growth-benefit mt-3 text-[14.5px] font-semibold leading-snug text-gold">{{ $growth['plans']['yearly']['note'] }}</p>

            <div class="mt-7 flex flex-col gap-3 sm:flex-row">
                <x-button href="{{ package_enquiry_url($growth['service'], 'Yearly') }}" size="lg" class="js-growth-cta w-full sm:w-auto">Get This Plan</x-button>
                <x-button href="{{ package_whatsapp_url($growth['service'], 'Yearly') }}" variant="light" size="lg" class="js-growth-whatsapp w-full sm:w-auto">Ask on WhatsApp</x-button>
            </div>
        </div>

        <div class="px-6 py-8 sm:px-8 lg:col-span-7">
            <p class="text-xs font-semibold uppercase tracking-[0.16em] text-gold">What you get every month on Yearly</p>
            <ul class="mt-4 grid gap-2.5 sm:grid-cols-2">
                @foreach ($growth['plans']['yearly']['monthly_pace'] as $item)
                    <li class="flex items-start gap-2 text-[16px] leading-snug text-white/88">
                        <span class="mt-2 h-1.5 w-1.5 shrink-0 rounded-full bg-gold"></span>
                        <span>{{ $item }}</span>
                    </li>
                @endforeach
            </ul>
            <p class="mt-6 text-xs font-semibold uppercase tracking-[0.16em] text-white/40">Included in every Complete Growth plan</p>
            <ul class="mt-4 grid gap-2.5 sm:grid-cols-2">
                @foreach ($growth['includes'] as $item)
                    <li class="flex items-start gap-2 text-[16px] leading-snug text-white/88">
                        <span class="mt-2 h-1.5 w-1.5 shrink-0 rounded-full bg-gold"></span>
                        <span>{{ $item }}</span>
                    </li>
                @endforeach
            </ul>
            <div class="mt-6 space-y-2 border-t border-white/10 pt-5 text-[14px] leading-relaxed text-white/58">
                <p>{{ config('sarvix.marketing.notes.tax') }}</p>
                <p>{{ config('sarvix.marketing.notes.timeline') }}</p>
                <p>{{ config('sarvix.marketing.notes.payment') }}</p>
                <p>{{ config('sarvix.marketing.notes.support') }}</p>
                <p>{{ config('sarvix.marketing.notes.reels') }}</p>
                <p>{{ config('sarvix.marketing.notes.third_party') }}</p>
            </div>
        </div>
    </div>
</article>
