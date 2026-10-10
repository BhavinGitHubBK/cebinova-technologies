<details class="admin-card visitor-visit visitor-fold">
    <summary>
        <p class="visitor-visit-kicker">Earlier visit</p>
        <h2>{{ ist($visit->last_seen_at, 'd M Y, h:i A') }}</h2>
        <p>Opened {{ page_label($visit->landing_path) }} · Last page {{ page_label($visit->exit_path) }} · {{ $visit->pageviews->count() }} {{ \Illuminate\Support\Str::plural('page', $visit->pageviews->count()) }}</p>
    </summary>
    @include('admin.analytics.partials.timeline', ['views' => $visit->timeline])
</details>
