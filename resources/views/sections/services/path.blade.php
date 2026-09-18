<<<<<<< Updated upstream
﻿@php
=======
@php
>>>>>>> Stashed changes
    $steps = [
        ['icon' => 'globe', 'step' => '01', 'title' => 'Presence', 'text' => 'Website, catalogue and a clear way for customers to enquire.'],
        ['icon' => 'store', 'step' => '02', 'title' => 'Sell & manage', 'text' => 'eCommerce, software and operations tools when the process needs them.'],
        ['icon' => 'spark', 'step' => '03', 'title' => 'Automate & grow', 'text' => 'AI, workflows and marketing once the foundation is in place.'],
    ];
@endphp

<section class="svc-page-path section-pad-lg" aria-label="How we start">
    <div class="container-wide">
        <div class="svc-page-head" data-reveal>
            <p class="svc-page-kicker">
                <span class="svc-page-dot" aria-hidden="true"></span>
                How we start
            </p>
            <h2 class="section-title">Right-sized technology. One connected journey.</h2>
            <p class="section-support">
                You do not need every service on day one. We start with the work that matches how the business operates today.
            </p>
        </div>

        <div class="svc-page-path-grid" data-stagger>
            @foreach ($steps as $item)
                <article class="svc-page-path-card">
                    <span class="svc-page-path-icon">
<<<<<<< Updated upstream
                        <x-mark :name="$item['icon']" class="h-4 w-4" />
=======
                        <x-icon :name="$item['icon']" class="h-4 w-4" />
>>>>>>> Stashed changes
                    </span>
                    <span class="svc-page-path-step">{{ $item['step'] }}</span>
                    <h3 class="svc-page-path-title">{{ $item['title'] }}</h3>
                    <p class="svc-page-path-text">{{ $item['text'] }}</p>
                </article>
            @endforeach
        </div>
    </div>
</section>
