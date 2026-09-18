<section class="home-xform section-pad-lg" aria-label="Business transformation">
    <div class="container-wide">
        <div class="home-xform-head" data-reveal>
            <p class="home-xform-kicker">
                <span class="home-xform-dot" aria-hidden="true"></span>
                Business transformation
            </p>
            <h2 class="section-title">From Local Business to Digital Business.</h2>
            <p class="section-support">
                CEBINOVA helps businesses grow step by step instead of forcing complicated technology from day one.
            </p>
        </div>

        <div class="home-xform-grid" data-stagger>
            <article class="home-xform-card is-from">
                <p class="home-xform-cat">Traditional business</p>
                <h3 class="home-xform-title">How work looks today</h3>
                <ul class="home-xform-list">
                    @foreach (['Manual enquiries', 'Offline-only sales', 'Spreadsheet tracking', 'Repeated tasks', 'Limited visibility'] as $item)
                        <li>{{ $item }}</li>
                    @endforeach
                </ul>
            </article>

            <article class="home-xform-path">
                <p class="home-xform-cat is-gold">CEBINOVA pathway</p>
                <p class="home-xform-path-title">Start → Digital → Grow</p>
                <ol class="home-xform-nodes" aria-hidden="true">
                    <li>Start</li>
                    <li>Digital</li>
                    <li>Grow</li>
                </ol>
                <p class="home-xform-path-note">A staged move from local operations to connected systems, at the pace your team can use.</p>
            </article>

            <article class="home-xform-card is-to">
                <p class="home-xform-cat is-gold">Digital business</p>
                <h3 class="home-xform-title">Where the business can go</h3>
                <ul class="home-xform-list is-gold">
                    @foreach (['Online presence', 'eCommerce', 'Central software', 'Automated workflow', 'Marketing & insights'] as $item)
                        <li>{{ $item }}</li>
                    @endforeach
                </ul>
            </article>
        </div>
    </div>
</section>
