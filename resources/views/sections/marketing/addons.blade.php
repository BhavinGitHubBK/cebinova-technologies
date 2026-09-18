<section class="mkt-page-addons section-pad-lg bg-white">
    <div class="container-wide">
        <div class="mkt-page-head" data-reveal>
            <p class="mkt-page-kicker">
                <span class="mkt-page-dot" aria-hidden="true"></span>
                Optional add-ons
            </p>
            <h2 class="section-title">Not included - add only if needed.</h2>
            <p class="section-support">
                Marketing plans cover design and content. These extras are quoted separately.
            </p>
        </div>
        <ul class="mkt-page-addons-grid" data-stagger>
            @foreach (config('cebinova.marketing.addons') as $item)
                <li class="mkt-page-addon-chip">{{ $item }}</li>
            @endforeach
        </ul>
        <p class="mkt-page-addons-note">Start with the plan. Add extras later only if they help your business.</p>
    </div>
</section>
