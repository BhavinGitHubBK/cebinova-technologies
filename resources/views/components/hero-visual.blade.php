@php
    $nodes = [
        ['label' => 'Website'],
        ['label' => 'eCommerce'],
        ['label' => 'Software'],
        ['label' => 'Mobile App', 'lines' => ['Mobile App', 'Android · iOS']],
        ['label' => 'AI'],
        ['label' => 'Automation'],
        ['label' => 'Marketing'],
        ['label' => 'Growth'],
    ];
    $cx = 320;
    $cy = 268;
    $radius = 186;
    $count = count($nodes);
    foreach ($nodes as $i => $node) {
        $deg = -90 + ($i * (360 / $count));
        $rad = deg2rad($deg);
        $nodes[$i]['x'] = (int) round($cx + $radius * cos($rad));
        $nodes[$i]['y'] = (int) round($cy + $radius * sin($rad));
        $nodes[$i]['delay'] = number_format($i * 0.35, 2);
        $midX = (int) round($cx + ($nodes[$i]['x'] - $cx) * 0.55);
        $midY = (int) round($cy + ($nodes[$i]['y'] - $cy) * 0.55 + (($i % 2 === 0) ? -18 : 18));
        $nodes[$i]['path'] = "M{$cx} {$cy} Q {$midX} {$midY} {$nodes[$i]['x']} {$nodes[$i]['y']}";
    }
@endphp

<figure class="relative mx-auto w-full max-w-xl lg:max-w-none" aria-label="Your business at the centre, connected to website, eCommerce, software, mobile app, AI, automation, marketing and growth">
    <div class="space-y-3 rounded-[1.4rem] border border-navy/10 bg-navy p-4 sm:p-5 lg:hidden">
        <div class="mx-auto flex h-[4.75rem] w-[4.75rem] flex-col items-center justify-center rounded-full border border-gold bg-navy-mid text-center">
            <span class="text-[10px] font-extrabold tracking-[0.12em] text-white">YOUR</span>
            <span class="text-[10px] font-extrabold tracking-[0.12em] text-white">BUSINESS</span>
        </div>
        <ul class="grid grid-cols-2 gap-2">
            @foreach ($nodes as $node)
                <li class="rounded-xl border border-white/10 bg-white/[0.06] px-3 py-2.5 text-[13px] font-semibold text-white">
                    {{ $node['label'] }}
                    @if (! empty($node['lines'][1]))
                        <span class="mt-0.5 block text-[11px] font-medium text-white/55">{{ $node['lines'][1] }}</span>
                    @endif
                </li>
            @endforeach
        </ul>
        <p class="text-center text-[12px] font-medium tracking-wide text-white/55">Business at the centre. Technology around it.</p>
    </div>

    <div class="relative hidden overflow-hidden rounded-[1.6rem] border border-navy/10 bg-[#071428] lg:block">
        <div class="pointer-events-none absolute inset-0 bg-dots-light opacity-70"></div>
        <svg viewBox="0 0 640 540" class="relative h-auto w-full" role="img">
            <title>SARVIX technology journey around your business</title>
            <defs>
                <radialGradient id="hubGlow" cx="50%" cy="48%" r="58%">
                    <stop offset="0%" stop-color="#C9A227" stop-opacity="0.18"/>
                    <stop offset="100%" stop-color="#071428" stop-opacity="0"/>
                </radialGradient>
                <filter id="softGlow" x="-20%" y="-20%" width="140%" height="140%">
                    <feGaussianBlur stdDeviation="2.2" result="blur"/>
                    <feMerge>
                        <feMergeNode in="blur"/>
                        <feMergeNode in="SourceGraphic"/>
                    </feMerge>
                </filter>
            </defs>

            <rect width="640" height="540" fill="#071428"/>
            <circle class="js-hub-pulse" cx="{{ $cx }}" cy="{{ $cy }}" r="210" fill="url(#hubGlow)"/>
            <circle class="js-orbit-ring" cx="{{ $cx }}" cy="{{ $cy }}" r="{{ $radius }}" fill="none" stroke="#ffffff" stroke-opacity="0.08" stroke-dasharray="3 11"/>
            <circle cx="{{ $cx }}" cy="{{ $cy }}" r="108" fill="none" stroke="#C9A227" stroke-opacity="0.28"/>

            @foreach ([48, 92, 140, 188, 236, 410, 470, 520] as $i => $px)
                <circle class="js-float-node" cx="{{ $px }}" cy="{{ 36 + ($i * 17) % 80 }}" r="1.4" fill="#C9A227" fill-opacity="0.55" style="animation-delay: {{ $i * 0.28 }}s"/>
            @endforeach

            <g fill="none" stroke="#C9A227" stroke-opacity="0.42" stroke-width="1.35">
                @foreach ($nodes as $node)
                    <path class="js-orbit-ring" d="{{ $node['path'] }}" />
                @endforeach
            </g>

            @foreach ($nodes as $node)
                <circle class="js-data-dot" r="3" fill="#C9A227" style="offset-path: path('{{ $node['path'] }}'); animation-delay: {{ $node['delay'] }}s;" />
            @endforeach

            <g filter="url(#softGlow)">
                <circle cx="{{ $cx }}" cy="{{ $cy }}" r="64" fill="#143056" stroke="#C9A227" stroke-width="1.7"/>
                <text x="{{ $cx }}" y="{{ $cy - 6 }}" text-anchor="middle" fill="#ffffff" font-size="12" font-family="Plus Jakarta Sans, Outfit, sans-serif" font-weight="800" letter-spacing="1.4">YOUR</text>
                <text x="{{ $cx }}" y="{{ $cy + 12 }}" text-anchor="middle" fill="#C9A227" font-size="13" font-family="Plus Jakarta Sans, Outfit, sans-serif" font-weight="800" letter-spacing="1.6">BUSINESS</text>
            </g>

            @foreach ($nodes as $i => $node)
                <g transform="translate({{ $node['x'] }}, {{ $node['y'] }})">
                    <g class="js-orbit-node js-float-node" data-node="{{ $i }}" style="animation-delay: {{ $node['delay'] }}s">
                        <circle r="42" fill="#0B1F3A" stroke="#C9A227" stroke-width="1.25" stroke-opacity="0.88"/>
                        @if (! empty($node['lines']))
                            <text y="-1" text-anchor="middle" fill="#ffffff" font-size="10" font-family="Outfit, sans-serif" font-weight="700">{{ $node['lines'][0] }}</text>
                            <text y="12" text-anchor="middle" fill="#C9A227" font-size="8" font-family="Outfit, sans-serif" font-weight="600">{{ $node['lines'][1] }}</text>
                        @else
                            <text y="4" text-anchor="middle" fill="#ffffff" font-size="11" font-family="Outfit, sans-serif" font-weight="700">{{ $node['label'] }}</text>
                        @endif
                    </g>
                </g>
            @endforeach
        </svg>
    </div>
    <p class="mt-3 hidden text-center text-sm font-medium tracking-wide text-muted lg:block">Business at the centre. Technology around it.</p>
    <figcaption class="sr-only">Your business at the centre, connected to website, eCommerce, software, Android and iOS mobile apps, AI, automation, marketing and growth.</figcaption>
</figure>
