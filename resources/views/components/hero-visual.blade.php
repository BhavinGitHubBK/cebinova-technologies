@php
    $nodes = [
        ['label' => 'Website', 'icon' => 'globe', 'copy' => 'Digital presence, done properly', 'href' => route('services.show', 'web-development')],
        ['label' => 'eCommerce', 'icon' => 'store', 'copy' => 'A storefront that sells 24/7', 'href' => route('services.show', 'ecommerce')],
        ['label' => 'Software', 'icon' => 'layers', 'copy' => 'Systems built around your process', 'href' => route('services.show', 'custom-software')],
<<<<<<< Updated upstream
        ['label' => 'Mobile App', 'icon' => 'device', 'copy' => 'Android and iOS, one journey', 'lines' => ['Mobile App', 'Android Â· iOS'], 'href' => route('pricing')],
=======
        ['label' => 'Mobile App', 'icon' => 'device', 'copy' => 'Android and iOS, one journey', 'lines' => ['Mobile App', 'Android · iOS'], 'href' => route('pricing')],
>>>>>>> Stashed changes
        ['label' => 'AI', 'icon' => 'spark', 'copy' => 'Practical intelligence in the workflow', 'href' => route('services.show', 'ai-automation')],
        ['label' => 'Automation', 'icon' => 'nodes', 'copy' => 'Repetitive work, handled', 'href' => route('services.show', 'ai-automation')],
        ['label' => 'Marketing', 'icon' => 'megaphone', 'copy' => 'Reach the people who convert', 'href' => route('marketing-packages')],
        ['label' => 'Growth', 'icon' => 'trend', 'copy' => 'A plan that keeps compounding', 'href' => route('services.show', 'digital-growth')],
    ];
    $icons = [
        'globe' => '<circle cx="12" cy="12" r="9"/><path d="M3 12h18M12 3c3 3.5 3 14.5 0 18M12 3c-3 3.5-3 14.5 0 18"/>',
        'store' => '<path d="M4 10h16v10H4z"/><path d="M4 10l1.5-5h13L20 10"/><path d="M9 20v-6h6v6"/>',
        'layers' => '<path d="M12 4l9 5-9 5-9-5 9-5z"/><path d="M3 13l9 5 9-5"/><path d="M3 17l9 5 9-5"/>',
        'device' => '<rect x="7" y="3" width="10" height="18" rx="2"/><path d="M11 18h2"/>',
        'spark' => '<path d="M12 3v4M12 17v4M5.6 5.6l2.8 2.8M15.6 15.6l2.8 2.8M3 12h4M17 12h4M5.6 18.4l2.8-2.8M15.6 8.4l2.8-2.8"/><circle cx="12" cy="12" r="2.5"/>',
        'nodes' => '<circle cx="6" cy="7" r="2.2"/><circle cx="18" cy="7" r="2.2"/><circle cx="12" cy="17" r="2.2"/><path d="M8 8.2l2.6 6.1M16 8.2l-2.6 6.1M8.2 7h7.6"/>',
        'megaphone' => '<path d="M4 11v2c2 0 3 .5 5 2V9c-2 1.5-3 2-5 2z"/><path d="M9 9l11-4v14L9 15"/><path d="M6.5 13.5l.8 4.2H9"/>',
        'trend' => '<path d="M4 16l5-5 4 3 7-8"/><path d="M14 6h6v6"/>',
    ];
    $cx = 320;
    $cy = 270;
    $radius = 188;
    $count = count($nodes);
    foreach ($nodes as $i => $node) {
        $deg = -90 + ($i * (360 / $count));
        $rad = deg2rad($deg);
        $nodes[$i]['x'] = (int) round($cx + $radius * cos($rad));
        $nodes[$i]['y'] = (int) round($cy + $radius * sin($rad));
        $nodes[$i]['delay'] = number_format($i * 0.38, 2);
        $nodes[$i]['mx'] = (int) round($cx + ($nodes[$i]['x'] - $cx) * 0.52);
        $nodes[$i]['my'] = (int) round($cy + ($nodes[$i]['y'] - $cy) * 0.52);
        $nodes[$i]['path'] = "M{$cx} {$cy} L {$nodes[$i]['x']} {$nodes[$i]['y']}";
        $nodes[$i]['iconPath'] = $icons[$node['icon']] ?? $icons['globe'];
    }
@endphp

<figure class="relative mx-auto w-full max-w-xl lg:max-w-none" aria-label="Your business at the centre, connected to website, eCommerce, software, mobile app, AI, automation, marketing and growth">
    <div class="hub-mobile lg:hidden" data-hub-map>
        <div class="hub-mobile-core">
            <span>YOUR</span>
            <strong>BUSINESS</strong>
        </div>
        <p class="hub-mobile-caption" aria-live="polite">
            <strong data-hub-status-label>{{ $nodes[0]['label'] }}</strong>
            <span data-hub-status-copy>{{ $nodes[0]['copy'] }}</span>
        </p>
        <ul class="hub-mobile-grid">
            @foreach ($nodes as $i => $node)
                <li>
                    <a
                        href="{{ $node['href'] }}"
                        class="hub-mobile-card"
                        data-hub-node="{{ $i }}"
                        data-hub-label="{{ $node['label'] }}"
                        data-hub-copy="{{ $node['copy'] }}"
                    >
                        <span class="hub-mobile-icon">
<<<<<<< Updated upstream
                            <x-mark :name="$node['icon']" class="h-4 w-4" />
=======
                            <x-icon :name="$node['icon']" class="h-4 w-4" />
>>>>>>> Stashed changes
                        </span>
                        <span>
                            <span class="block text-[13px] font-semibold text-white">{{ $node['label'] }}</span>
                            @if (! empty($node['lines'][1]))
                                <span class="mt-0.5 block text-[11px] font-medium text-gold">{{ $node['lines'][1] }}</span>
                            @endif
                        </span>
                    </a>
                </li>
            @endforeach
        </ul>
    </div>

    <div class="hub-desktop relative hidden overflow-hidden rounded-none border border-white/8 bg-[#010b24] lg:block" data-hub-map>
        <div class="pointer-events-none absolute inset-0 bg-dots-light opacity-55"></div>
        <div class="pointer-events-none absolute inset-0 hub-desktop-sheen"></div>
        <svg viewBox="0 0 640 540" class="relative h-auto w-full" role="img">
            <title>CEBINOVA technology journey around your business</title>
            <defs>
                <radialGradient id="hubGlow" cx="50%" cy="48%" r="62%">
                    <stop offset="0%" stop-color="#FBB50B" stop-opacity="0.22"/>
                    <stop offset="42%" stop-color="#013A9D" stop-opacity="0.16"/>
                    <stop offset="100%" stop-color="#010b24" stop-opacity="0"/>
                </radialGradient>
                <linearGradient id="scanGrad" x1="0%" y1="0%" x2="100%" y2="0%">
                    <stop offset="0%" stop-color="#FBB50B" stop-opacity="0"/>
                    <stop offset="70%" stop-color="#FBB50B" stop-opacity="0.2"/>
                    <stop offset="100%" stop-color="#FBB50B" stop-opacity="0.85"/>
                </linearGradient>
                <filter id="softGlow" x="-35%" y="-35%" width="170%" height="170%">
                    <feGaussianBlur stdDeviation="2.4" result="blur"/>
                    <feMerge>
                        <feMergeNode in="blur"/>
                        <feMergeNode in="SourceGraphic"/>
                    </feMerge>
                </filter>
            </defs>

            <rect width="640" height="540" fill="#010b24"/>
            <circle class="js-hub-pulse" cx="{{ $cx }}" cy="{{ $cy }}" r="228" fill="url(#hubGlow)"/>

            <g class="hub-orbit hub-orbit--slow">
                <circle cx="{{ $cx }}" cy="{{ $cy }}" r="{{ $radius + 8 }}" fill="none" stroke="#ffffff" stroke-opacity="0.07" stroke-dasharray="2 12"/>
            </g>
            <g class="hub-orbit hub-orbit--reverse">
                <circle cx="{{ $cx }}" cy="{{ $cy }}" r="132" fill="none" stroke="#FBB50B" stroke-opacity="0.18" stroke-dasharray="4 14"/>
            </g>
            <g class="hub-orbit hub-orbit--scan">
                <path d="M {{ $cx }} {{ $cy - $radius }} A {{ $radius }} {{ $radius }} 0 0 1 {{ $cx + $radius }} {{ $cy }}" fill="none" stroke="url(#scanGrad)" stroke-width="2.4" stroke-linecap="round"/>
            </g>
            <circle cx="{{ $cx }}" cy="{{ $cy }}" r="96" fill="none" stroke="#013A9D" stroke-opacity="0.55" stroke-width="10"/>

            @foreach ([42, 88, 128, 176, 214, 402, 458, 508, 548] as $i => $px)
                <circle class="js-float-node" cx="{{ $px }}" cy="{{ 28 + ($i * 19) % 86 }}" r="1.5" fill="#FBB50B" fill-opacity="0.5" style="animation-delay: {{ $i * 0.24 }}s"/>
            @endforeach

            <g fill="none" stroke="#FBB50B" stroke-width="1.35">
                @foreach ($nodes as $i => $node)
                    <path class="hub-spoke" data-hub-spoke="{{ $i }}" d="{{ $node['path'] }}" />
                @endforeach
            </g>

            @foreach ($nodes as $i => $node)
                <circle class="hub-bead" data-hub-bead="{{ $i }}" cx="{{ $node['mx'] }}" cy="{{ $node['my'] }}" r="3.4" fill="#FBB50B"/>
            @endforeach

            @foreach ($nodes as $node)
                <circle class="js-data-dot" r="3.2" fill="#FBB50B" style="offset-path: path('{{ $node['path'] }}'); animation-delay: {{ $node['delay'] }}s;" />
            @endforeach

            <g filter="url(#softGlow)">
                <circle cx="{{ $cx }}" cy="{{ $cy }}" r="70" fill="#013A9D" stroke="#FBB50B" stroke-width="2"/>
                <circle cx="{{ $cx }}" cy="{{ $cy }}" r="70" fill="none" stroke="#7EB0FF" stroke-width="3.2" stroke-linecap="round" pathLength="440" stroke-dasharray="358 82" stroke-dashoffset="41"/>
                <text x="{{ $cx }}" y="{{ $cy - 4 }}" text-anchor="middle" fill="#ffffff" font-size="12" font-family="Plus Jakarta Sans, Outfit, sans-serif" font-weight="800" letter-spacing="1.5">YOUR</text>
                <text x="{{ $cx }}" y="{{ $cy + 16 }}" text-anchor="middle" fill="#FBB50B" font-size="13" font-family="Plus Jakarta Sans, Outfit, sans-serif" font-weight="800" letter-spacing="1.7">BUSINESS</text>
            </g>

            @foreach ($nodes as $i => $node)
                <g transform="translate({{ $node['x'] }}, {{ $node['y'] }})">
                    <a
                        href="{{ $node['href'] }}"
                        class="hub-node js-orbit-node"
                        data-hub-node="{{ $i }}"
                        data-hub-label="{{ $node['label'] }}"
                        data-hub-copy="{{ $node['copy'] }}"
                    >
                        <circle class="hub-node-halo" r="54" fill="none" stroke="#FBB50B" stroke-width="1.15"/>
                        <circle class="hub-node-disc" r="46" fill="#013A9D" stroke="#FBB50B" stroke-width="1.4"/>
                        <g class="hub-node-icon" transform="translate(-10,-24) scale(0.83)" fill="none" stroke="#FBB50B" stroke-width="1.7" stroke-linecap="round" stroke-linejoin="round">
                            {!! $node['iconPath'] !!}
                        </g>
                        @if (! empty($node['lines']))
                            <text y="12" text-anchor="middle" fill="#ffffff" font-size="10" font-family="Outfit, sans-serif" font-weight="700">{{ $node['lines'][0] }}</text>
                            <text y="24" text-anchor="middle" fill="#FBB50B" font-size="8" font-family="Outfit, sans-serif" font-weight="600">{{ $node['lines'][1] }}</text>
                        @else
                            <text y="16" text-anchor="middle" fill="#ffffff" font-size="11" font-family="Outfit, sans-serif" font-weight="700">{{ $node['label'] }}</text>
                        @endif
                    </a>
                </g>
            @endforeach
        </svg>
        <p class="hub-caption" aria-live="polite">
            <strong data-hub-status-label>{{ $nodes[0]['label'] }}</strong>
            <span data-hub-status-copy>{{ $nodes[0]['copy'] }}</span>
        </p>
    </div>
    <figcaption class="sr-only">Your business at the centre, connected to website, eCommerce, software, Android and iOS mobile apps, AI, automation, marketing and growth.</figcaption>
</figure>
