@php
    $stats = $homeData['proof'] ?? App\Services\HomepageBuilder::proofStats();
@endphp

@if ($stats !== [])
    <section class="home-proof section-pad" aria-label="CEBINOVA proof">
        <div class="container-wide">
            <div class="home-svc-head">
                <p class="home-svc-kicker">
                    <span class="home-svc-dot" aria-hidden="true"></span>
                    Proof
                </p>
                <h2 class="section-title">Why businesses can trust CEBINOVA.</h2>
                <p class="section-support">Real counts from the work we publish. A number stays off this page until it is true.</p>
            </div>
            @php
                $proofIcons = [
                    'projects' => 'briefcase',
                    'industries' => 'store',
                    'years' => 'trend',
                    'demos' => 'layers',
                    'support' => 'shield',
                ];
            @endphp
            <ul class="home-proof-list">
                @foreach ($stats as $stat)
                    <li class="home-proof-item" data-proof="{{ $stat['key'] }}">
                        <span class="home-proof-icon" aria-hidden="true">
                            <x-mark :name="$proofIcons[$stat['key']] ?? 'globe'" class="h-4 w-4" />
                        </span>
                        <span class="home-proof-copy">
                            <span class="home-proof-value">{{ $stat['value'] }}</span>
                            <span class="home-proof-label">{{ $stat['label'] }}</span>
                        </span>
                    </li>
                @endforeach
            </ul>
        </div>
    </section>
@endif
