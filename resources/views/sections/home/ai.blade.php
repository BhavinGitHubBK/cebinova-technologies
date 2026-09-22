@php    $meta = [
        ['icon' => 'chat', 'cat' => 'Conversations', 'note' => 'Answer common questions after hours.'],
        ['icon' => 'trend', 'cat' => 'Capture', 'note' => 'Route new enquiries to the right person.'],
        ['icon' => 'whatsapp', 'cat' => 'Messaging', 'note' => 'Send updates where customers already chat.'],
        ['icon' => 'nodes', 'cat' => 'Operations', 'note' => 'Remove repeated internal steps.'],
        ['icon' => 'clock', 'cat' => 'Alerts', 'note' => 'Keep teams and customers informed.'],
        ['icon' => 'spark', 'cat' => 'Content', 'note' => 'Draft useful copy for offers and follow-ups.'],
        ['icon' => 'layers', 'cat' => 'Intelligence', 'note' => 'See what needs attention next.'],
        ['icon' => 'cpu', 'cat' => 'Connect', 'note' => 'Connect the tools you already use.'],
    ];
    $features = config('cebinova.ai_features');
@endphp

<section class="home-ai section-pad-lg" aria-label="AI and automation">
    <div class="pointer-events-none absolute inset-0 bg-dots-light"></div>
    <svg class="home-ai-lines" viewBox="0 0 1200 620" aria-hidden="true">
        <g fill="none" stroke="#FBB50B" stroke-opacity="0.28" stroke-width="1.1">
            <path class="js-ai-line" d="M60 130C220 70 340 210 510 170C680 130 760 70 980 150"/>
            <path class="js-ai-line" d="M90 430C250 360 410 510 580 450C780 370 900 500 1140 410"/>
            <path class="js-ai-line" d="M40 300C200 260 360 320 540 280C720 240 880 310 1160 250"/>
        </g>
    </svg>

    <div class="container-wide relative z-10">
        <div class="home-ai-head" data-reveal>
            <p class="home-ai-kicker">
                <span class="home-ai-dot" aria-hidden="true"></span>
                AI &amp; Automation
            </p>
            <h2 class="section-title text-white">Make Technology Work for You.</h2>
            <p class="home-ai-support section-support">
                CEBINOVA helps businesses reduce manual work through intelligent automation and AI-powered solutions.
            </p>
            <p class="home-ai-lead">Enquiry in. Follow-up out. Reporting in between - without adding another disconnected tool.</p>
            <ul class="home-ai-flow" aria-hidden="true">
                <li>Enquiry in</li>
                <li>Follow-up out</li>
                <li>Reporting</li>
            </ul>
            <p class="home-ai-cta">
                <x-button href="{{ route('services.show', 'ai-automation') }}" variant="primary">
                    Explore AI Solutions
                    <svg class="arrow-shift h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M5 12h14M13 6l6 6-6 6"/></svg>
                </x-button>
            </p>
        </div>

        <ul class="home-ai-grid" data-stagger>
            @foreach ($features as $index => $feature)
                @php $item = $meta[$index] ?? ['icon' => 'spark', 'cat' => 'Capability', 'note' => '']; @endphp
                <li>
                    <article class="home-ai-card" tabindex="0" data-ai-block>
                        <span class="home-ai-icon">
                            <x-mark :name="$item['icon']" class="h-4 w-4" />                        </span>
                        <span class="home-ai-body">
                            <span class="home-ai-cat">{{ $item['cat'] }}</span>
                            <h3 class="home-ai-title">{{ $feature }}</h3>
                            <p class="home-ai-note">{{ $item['note'] }}</p>
                        </span>
                    </article>
                </li>
            @endforeach
        </ul>
    </div>
</section>
