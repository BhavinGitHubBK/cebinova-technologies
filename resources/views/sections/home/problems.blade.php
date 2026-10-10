@php
    $options = $homeData['problems'] ?? App\Services\HomepageBuilder::problemOptions();
@endphp

@if (count($options) >= 2)
    <section class="home-problems section-pad-lg" aria-label="Business problems">
        <div class="container-wide">
            <div class="home-svc-head">
                <p class="home-svc-kicker">
                    <span class="home-svc-dot" aria-hidden="true"></span>
                    Business problems
                </p>
                <h2 class="section-title">Start with the problem in front of you.</h2>
                <p class="section-support">Each card is one business problem and the solution that fits it.</p>
            </div>

            <div class="home-problems-grid">
                @foreach ($options as $option)
                    <a class="home-problems-card" href="{{ $option['href'] }}">
                        <span class="home-problems-body">
                            <span class="home-problems-kicker">The problem</span>
                            <span class="home-problems-name">{{ $option['name'] }}</span>
                            <span class="home-problems-problem">{{ $option['problem'] }}</span>
                            <span class="home-problems-rule" aria-hidden="true"></span>
                            <span class="home-problems-kicker">The solution</span>
                            <span class="home-problems-outcome">{{ $option['outcome'] }}</span>
                        </span>
                        <span class="home-problems-cta">View this solution</span>
                    </a>
                @endforeach
            </div>
        </div>
    </section>
@endif
