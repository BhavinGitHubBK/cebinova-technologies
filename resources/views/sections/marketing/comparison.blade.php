@php
    $matrix = config('cebinova.marketing.matrix.rows', []);
    $growth = \App\Support\MarketingPackages::packageArray('growth') ?? [];
@endphp
<section class="mkt-page-compare section-pad-lg bg-white" id="choose-plan">
    <div class="container-wide">
        <div class="mkt-page-head" data-reveal>
            <p class="mkt-page-kicker">
                <span class="mkt-page-dot" aria-hidden="true"></span>
                Start here
            </p>
            <h2 class="section-title">Which plan is for you?</h2>
            <p class="section-support">
                Regular is everyday posts. Festival is occasions only. Complete Growth is both - and only ₹1,000 more than Regular each month.
            </p>
        </div>

        <div class="mkt-page-matrix-wrap">
            <table class="pack-matrix mkt-page-matrix min-w-[640px] w-full text-left">
                <thead>
                    <tr>
                        <th>What you get</th>
                        <th>Regular</th>
                        <th>Festival</th>
                        <th>
                            Complete Growth
                            <span class="mkt-page-matrix-badge">Recommended</span>
                        </th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($matrix as $row)
                        <tr>
                            <th>{{ $row['label'] }}</th>
                            <td>{{ $row['regular'] }}</td>
                            <td>{{ $row['festival'] }}</td>
                            <td class="mkt-page-matrix-growth">{{ $row['growth'] }}</td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>

        <div class="mkt-page-compare-cards" data-stagger>
            @foreach (config('cebinova.marketing.comparison') as $item)
                @php $recommended = ! empty($item['recommended']); @endphp
                <a href="{{ $item['key'] === 'growth' ? '#complete-growth' : '#marketing-plans' }}" class="mkt-page-compare-card js-pack-jump {{ $recommended ? 'is-recommended' : '' }}" data-cat="{{ $item['key'] }}">
                    <p class="mkt-page-compare-card-kicker">{{ $item['title'] }}</p>
                    <p class="mkt-page-compare-card-title">{{ $item['if'] }}</p>
                    <p class="mkt-page-compare-card-text">{{ $item['gets'] }} →</p>
                </a>
            @endforeach
        </div>

        <article class="mkt-page-compare-foot">
            <p class="mkt-page-kicker">
                <span class="mkt-page-dot" aria-hidden="true"></span>
                Why Complete Growth
            </p>
            <h3 class="mkt-page-compare-foot-title">₹1,000 more than Regular. Festival creatives included.</h3>
            <p class="mkt-page-compare-foot-text">{{ $growth['why'] }}</p>
            <div class="mkt-page-compare-foot-actions">
                <x-button href="#complete-growth" class="js-pack-jump" data-cat="growth">See Complete Growth</x-button>
                <x-button href="{{ package_whatsapp_url('Complete Growth', 'Yearly') }}" variant="outline">Ask on WhatsApp</x-button>
            </div>
        </article>
    </div>
</section>
