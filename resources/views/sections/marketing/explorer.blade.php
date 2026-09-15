@php
    $regular = config('sarvix.marketing.regular');
    $festival = config('sarvix.marketing.festival');
    $growth = config('sarvix.marketing.growth');
@endphp

<section id="marketing-plans" class="bg-white pt-12 pb-10 lg:pt-16 lg:pb-12">
    <div class="container-wide">
        <div data-reveal>
            <x-section-heading eyebrow="Prices" title="See the exact price and what you get.">
                Tap a plan. Then pick Monthly, Quarterly, Half-Yearly or Yearly. Yearly costs less every month.
            </x-section-heading>
        </div>

        <div class="js-pack-explorer mt-10" data-default-cat="regular" data-default-duration="yearly">
            <div class="grid gap-3 sm:grid-cols-2" role="tablist" aria-label="Marketing package categories">
                @foreach (['regular' => $regular, 'festival' => $festival] as $key => $category)
                    <button
                        type="button"
                        class="pack-cat js-pack-cat {{ $key === 'regular' ? 'is-active' : '' }}"
                        data-cat="{{ $key }}"
                        role="tab"
                        aria-selected="{{ $key === 'regular' ? 'true' : 'false' }}"
                    >
                        <span class="pack-cat-title">{{ $category['title'] }}</span>
                        <span class="pack-cat-text">{{ $category['teaser'] }}</span>
                    </button>
                @endforeach
            </div>

            <a href="#complete-growth" class="mt-3 flex items-center justify-between gap-4 rounded-xl border border-gold/40 bg-gold/10 px-5 py-4">
                <span>
                    <span class="text-[15px] font-extrabold text-navy">{{ $growth['title'] }} <span class="ml-2 inline-flex rounded-full bg-navy px-2 py-0.5 text-[10px] font-bold uppercase tracking-[0.12em] text-gold">Most Popular</span></span>
                    <span class="mt-0.5 block text-[13.5px] leading-snug text-navy/65">{{ $growth['teaser'] }} From {{ sarvix_inr($growth['plans']['monthly']['price']) }} / month - only ₹1,000 more than Regular.</span>
                </span>
                <span class="shrink-0 text-sm font-semibold text-navy">View →</span>
            </a>

            <div class="mt-9">
                @foreach ([$regular, $festival] as $category)
                    <div class="js-pack-cat-panel {{ $category['key'] === 'regular' ? '' : 'hidden' }}" data-cat="{{ $category['key'] }}" @if ($category['key'] !== 'regular') hidden @endif>
                        <p class="kicker">{{ $category['title'] }}</p>
                        <h2 class="text-[1.85rem] font-extrabold leading-tight tracking-tight text-navy sm:text-4xl">{{ $category['heading'] }}</h2>
                        <p class="mt-3 max-w-3xl text-[17px] leading-relaxed text-muted">{{ $category['subheading'] }}</p>
                        @if (! empty($category['best_if']))
                            <p class="mt-2 text-[15px] font-semibold text-navy">{{ $category['best_if'] }}</p>
                        @endif

                        <div class="mt-6 grid grid-cols-2 gap-2.5 md:grid-cols-4" role="tablist" aria-label="{{ $category['title'] }} durations">
                            @foreach ($category['plans'] as $durationKey => $plan)
                                <button
                                    type="button"
                                    class="pack-duration js-pack-duration {{ $durationKey === 'yearly' ? 'is-active' : '' }}"
                                    data-cat="{{ $category['key'] }}"
                                    data-duration="{{ $durationKey }}"
                                    data-sticky-name="{{ $category['service'] }} · {{ $plan['label'] }}"
                                    data-sticky-price="{{ \App\Support\MarketingPackages::priceHeadline($category['service'], $plan['label']) }}"
                                    data-sticky-cta="{{ package_enquiry_url($category['service'], $plan['label']) }}"
                                    data-sticky-wa="{{ package_whatsapp_url($category['service'], $plan['label']) }}"
                                    role="tab"
                                    aria-selected="{{ $durationKey === 'yearly' ? 'true' : 'false' }}"
                                >
                                    <span class="pack-duration-label">{{ $plan['label'] }}</span>
                                    <span class="pack-duration-price">{{ sarvix_inr($plan['price']) }}</span>
                                    @if ($plan['badge'])
                                        <span class="pack-duration-badge">{{ $plan['badge'] }}</span>
                                    @endif
                                </button>
                            @endforeach
                        </div>

                        <div class="mt-6">
                            @foreach ($category['plans'] as $durationKey => $plan)
                                <article class="js-pack-plan card-surface {{ $durationKey === 'yearly' ? '' : 'hidden' }} overflow-hidden" data-cat="{{ $category['key'] }}" data-duration="{{ $durationKey }}" @if ($durationKey !== 'yearly') hidden @endif>
                                    <div class="grid lg:grid-cols-12">
                                        <div class="border-b border-line bg-mist px-6 py-7 sm:px-8 lg:col-span-4 lg:border-b-0 lg:border-r">
                                            @if ($plan['badge'])
                                                <p class="mb-3 inline-flex rounded-full bg-gold px-3 py-1 text-[10px] font-bold uppercase tracking-[0.16em] text-navy-deep">{{ $plan['badge'] }}</p>
                                            @endif
                                            <h3 class="text-2xl font-extrabold text-navy sm:text-[1.85rem]">{{ $plan['label'] }}</h3>
                                            <p class="mt-2 text-[15px] font-semibold text-navy/60">Duration: {{ $plan['duration'] }}</p>
                                            @include('sections.marketing.price-stack', ['service' => $category['service'], 'plan' => $plan, 'tone' => 'light'])
                                            @if ($category['key'] === 'regular' && $durationKey === 'monthly')
                                                <p class="mt-4 rounded-xl border border-gold/35 bg-gold/10 px-3 py-3 text-[13.5px] font-semibold leading-snug text-navy">Need festivals too? Complete Growth is only ₹1,000 more and includes them.</p>
                                            @endif
                                            <div class="mt-7 flex flex-col gap-3">
                                                <x-button href="{{ package_enquiry_url($category['service'], $plan['label']) }}" size="lg" class="w-full">Get This Plan</x-button>
                                                <x-button href="{{ package_whatsapp_url($category['service'], $plan['label']) }}" variant="outline" size="lg" class="w-full">Ask on WhatsApp</x-button>
                                            </div>
                                        </div>
                                        <div class="px-6 py-7 sm:px-8 lg:col-span-8">
                                            @if (! empty($plan['monthly_pace']))
                                                <p class="text-xs font-semibold uppercase tracking-[0.16em] text-gold-dark">What you get every month</p>
                                                <ul class="mt-3 grid gap-2 sm:grid-cols-2">
                                                    @foreach ($plan['monthly_pace'] as $item)
                                                        <li class="flex items-start gap-2 text-[15.5px] leading-snug text-navy">
                                                            <span class="mt-2 h-1.5 w-1.5 shrink-0 rounded-full bg-gold"></span>
                                                            <span>{{ $item }}</span>
                                                        </li>
                                                    @endforeach
                                                </ul>
                                                <p class="mt-5 text-xs font-semibold uppercase tracking-[0.16em] text-navy/45">Full {{ strtolower($plan['label']) }} total</p>
                                            @else
                                                <p class="text-xs font-semibold uppercase tracking-[0.16em] text-navy/45">What you get</p>
                                            @endif
                                            <ul class="mt-4 grid gap-2.5 sm:grid-cols-2">
                                                @foreach ($plan['includes'] as $item)
                                                    <li class="flex items-start gap-2 text-[16px] leading-snug text-navy/85">
                                                        <span class="mt-2 h-1.5 w-1.5 shrink-0 rounded-full bg-gold"></span>
                                                        <span>{{ $item }}</span>
                                                    </li>
                                                @endforeach
                                            </ul>
                                            <div class="mt-6 space-y-2 border-t border-line pt-5 text-[14px] leading-relaxed text-muted">
                                                <p>{{ config('sarvix.marketing.notes.tax') }}</p>
                                                <p>{{ config('sarvix.marketing.notes.timeline') }}</p>
                                                <p>{{ config('sarvix.marketing.notes.payment') }}</p>
                                                <p>{{ config('sarvix.marketing.notes.support') }}</p>
                                                <p>{{ config('sarvix.marketing.notes.reels') }}</p>
                                                @if ($category['key'] === 'festival')
                                                    <p>{{ config('sarvix.marketing.notes.festival') }}</p>
                                                    <p>{{ config('sarvix.marketing.notes.delivery') }}</p>
                                                @endif
                                                <p>{{ config('sarvix.marketing.notes.third_party') }}</p>
                                            </div>
                                        </div>
                                    </div>
                                </article>
                            @endforeach
                        </div>
                    </div>
                @endforeach
            </div>
        </div>
    </div>
</section>
