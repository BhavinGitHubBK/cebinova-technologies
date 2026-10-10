@php
    $packages = $homeData['packages'] ?? App\Services\HomepageBuilder::featuredPackages();
@endphp

@if ($packages !== [])
    <section class="home-packs section-pad-lg" aria-label="Featured website packages">
        <div class="container-wide">
            <div class="home-svc-head">
                <p class="home-svc-kicker">
                    <span class="home-svc-dot" aria-hidden="true"></span>
                    Packages
                </p>
                <h2 class="section-title">Featured website packages.</h2>
                <p class="section-support">Three ways to start. Marketing retainers stay on their own page.</p>
            </div>

            <div class="home-packs-grid">
                @foreach ($packages as $package)
                    <a class="home-packs-card{{ ! empty($package['highlighted']) ? ' is-highlighted' : '' }}" href="{{ $package['href'] }}" data-package="{{ $package['title'] }}">
                        <span class="home-packs-body">
                            @if (! empty($package['badge']))
                                <span class="home-packs-badge">{{ $package['badge'] }}</span>
                            @endif
                            <span class="home-packs-title">{{ $package['title'] }}</span>
                            @if (! empty($package['price']))
                                <span class="home-packs-price">{{ cebinova_inr($package['price']) }}@if (! empty($package['period'])) <small>{{ $package['period'] }}</small>@endif</span>
                            @endif
                            @if (($package['summary'] ?? '') !== '')
                                <span class="home-packs-summary">{{ $package['summary'] }}</span>
                            @endif
                            @if (! empty($package['features']))
                                <ul class="home-packs-features">
                                    @foreach ($package['features'] as $feature)
                                        <li>{{ $feature }}</li>
                                    @endforeach
                                </ul>
                            @endif
                        </span>
                        <span class="home-packs-link">See pricing</span>
                    </a>
                @endforeach
            </div>

            <p class="home-packs-more">
                <a href="{{ route('marketing-packages') }}">Explore marketing packages</a>
            </p>
        </div>
    </section>
@endif
