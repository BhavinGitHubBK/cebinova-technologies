@php
    $steps = [
        ['label' => 'Customer', 'note' => 'Walk-in, call or message', 'cat' => 'Origin', 'icon' => 'users'],
        ['label' => 'Website / WhatsApp', 'note' => 'First digital touch', 'cat' => 'Presence', 'icon' => 'globe'],
        ['label' => 'Lead / Order', 'note' => 'Captured in one place', 'cat' => 'Capture', 'icon' => 'bag'],
        ['label' => 'CRM / Software', 'note' => 'Team follows through', 'cat' => 'Operations', 'icon' => 'layers'],
        ['label' => 'Automation', 'note' => 'Reminders and routing', 'cat' => 'Intelligence', 'icon' => 'spark'],
        ['label' => 'Reports / Growth', 'note' => 'What to do next', 'cat' => 'Growth', 'icon' => 'trend'],
    ];
    $signals = [
        ['label' => 'New Lead', 'icon' => 'chat'],
        ['label' => 'Order Received', 'icon' => 'cart'],
        ['label' => 'Customer Follow-up', 'icon' => 'phone'],
        ['label' => 'Automation Completed', 'icon' => 'check'],
    ];
@endphp

<section class="home-action section-pad-lg" aria-label="How CEBINOVA connects a customer request">
    <div class="pointer-events-none absolute inset-0 bg-dots-light"></div>
    <div class="container-wide relative">
        <div class="home-action-head" data-reveal>
            <p class="home-action-kicker">
                <span class="home-action-dot" aria-hidden="true"></span>
                Technology in Action
            </p>
            <h2 class="section-title text-white">See How CEBINOVA Connects Your Business.</h2>
            <p class="home-action-support section-support">
                An illustrative workflow - not live client data - showing how a customer request can move from first contact to follow-up and growth.
            </p>
        </div>

        <div class="home-action-wrap">
            <div class="home-action-line" aria-hidden="true"></div>
            <ol class="home-action-list" data-stagger>
                @foreach ($steps as $step)
                    <li class="home-action-cell">
                        <span class="home-action-node" aria-hidden="true"></span>
                        <div class="home-action-step">
                            <span class="home-action-icon">
                                <x-mark :name="$step['icon']" class="h-4 w-4" />
                            </span>
                            <span class="home-action-cat">{{ $step['cat'] }}</span>
                            <h3 class="home-action-title">{{ $step['label'] }}</h3>
                            <p class="home-action-note">{{ $step['note'] }}</p>
                            @if (! $loop->last)
                                <span class="home-action-arrow" aria-hidden="true">
                                    <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8"><path stroke-linecap="round" stroke-linejoin="round" d="M5 12h14M13 6l6 6-6 6"/></svg>
                                </span>
                            @endif
                        </div>
                    </li>
                @endforeach
            </ol>
        </div>

        <div class="home-action-events">
            <p class="home-action-events-kicker">Illustrative system events</p>
            <ul class="home-action-signals">
                @foreach ($signals as $index => $signal)
                    <li class="js-signal-card home-action-signal" style="animation-delay: {{ $index * 0.55 }}s">
                        <span class="home-action-signal-icon" aria-hidden="true">
                            <x-mark :name="$signal['icon']" class="h-3.5 w-3.5" />
                        </span>
                        {{ $signal['label'] }}
                    </li>
                @endforeach
            </ul>
        </div>
    </div>
</section>
