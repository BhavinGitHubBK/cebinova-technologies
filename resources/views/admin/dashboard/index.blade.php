@extends('layouts.admin')

@section('title', 'Dashboard')
@section('breadcrumb', 'Admin / Dashboard')
@section('heading', 'Dashboard')

@section('content')
@php
    $hour = (int) now()->format('G');
    $greeting = $hour < 12 ? 'Good morning' : ($hour < 17 ? 'Good afternoon' : 'Good evening');
    $statusBadge = fn (string $status) => match ($status) {
        'New' => 'is-new',
        'Contacted' => 'is-contacted',
        'Follow-up', 'Qualified', 'Proposal Sent' => 'is-follow',
        'Converted', 'Won' => 'is-won',
        'Lost' => 'is-lost',
        default => '',
    };
@endphp

<section class="dash-hero">
    <div class="dash-hero-bg" aria-hidden="true"></div>
    <div class="dash-hero-copy">
        <p class="dash-hero-kicker"><span class="dash-hero-dot" aria-hidden="true"></span> Business overview</p>
        <h2 class="dash-hero-title">{{ $greeting }}, {{ auth()->user()->name }}</h2>
        <p class="dash-hero-sub">{{ now()->format('l, j F Y') }} · Priorities for today</p>
    </div>
    <div class="dash-hero-aside">
        @can('admin.analytics.view')
            <a class="admin-btn dash-hero-link" href="{{ route('admin.analytics.index') }}">View analytics</a>
        @endcan
        <div class="dash-quick" data-menu>
            <button type="button" class="admin-btn admin-btn-accent dash-quick-toggle" data-menu-toggle aria-expanded="false" aria-haspopup="true">
                Quick actions
                <x-mark name="chevron" class="h-4 w-4" />
            </button>
            <div class="admin-menu dash-quick-menu" data-menu-panel hidden>
                @can('admin.leads.view')
                    <a href="{{ route('admin.leads.index') }}">Manage leads</a>
                @endcan
                @can('admin.services.manage')
                    <a href="{{ route('admin.services.create') }}">Add service</a>
                @endcan
                @can('admin.packages.manage')
                    <a href="{{ route('admin.packages.create') }}">Add pricing package</a>
                @endcan
                @can('admin.content.view')
                    <a href="{{ route('admin.page-sections.index') }}">Manage homepage</a>
                @endcan
                @can('admin.reports.view')
                    @if (Route::has('admin.reports.index'))
                        <a href="{{ route('admin.reports.index') }}">View reports</a>
                    @endif
                @endcan
                @can('admin.settings.view')
                    <a href="{{ route('admin.settings.edit') }}">Settings</a>
                @endcan
            </div>
        </div>
    </div>
</section>

<section class="dash-block" aria-label="Key metrics">
    @can('admin.leads.view')
        <div class="dash-kpi-grid" data-dash-body>
            @include('admin.dashboard.partials.metrics')
        </div>
    @endcan
</section>

@can('admin.leads.view')
<section class="dash-pipeline dash-block" data-dash-section="pipeline" data-dash-url="{{ route('admin.dashboard.section', 'pipeline') }}" aria-label="Sales pipeline">
    <div class="dash-section-head">
        <div>
            <h2>Sales pipeline</h2>
            <p>Lead stages from enquiry to outcome</p>
        </div>
    </div>
    <div class="dash-pipe-grid" data-dash-body>
        @include('admin.dashboard.partials.pipeline')
    </div>
</section>

<section class="dash-analytics">
    <div class="admin-card dash-panel dash-panel-wide dash-block dash-trend" data-dash-section="trend" data-dash-url="{{ route('admin.dashboard.section', 'trend') }}" data-chart="line">
        <div class="dash-panel-head">
            <div>
                <h2>Lead trend</h2>
                <span data-dash-caption>{{ $range === 'all' ? 'Last 12 months' : $rangeLabel }}</span>
            </div>
            @php
                $trendTotal = (int) collect($trendValues)->sum();
                $trendPeak = (int) collect($trendValues)->max();
                $trendPeakAt = $trendLabels[collect($trendValues)->search($trendPeak)] ?? '—';
                $trendLatest = (int) collect($trendValues)->last();
                $trendLatestAt = $trendLabels[count($trendLabels) - 1] ?? '—';
            @endphp
            <div class="dash-trend-stats" data-trend-stats @if ($trendTotal === 0) hidden @endif>
                <p class="dash-trend-stat"><strong data-trend-total>{{ $trendTotal }}</strong><span>Enquiries</span></p>
                <p class="dash-trend-stat is-peak"><strong data-trend-peak>{{ $trendPeak }}</strong><span data-trend-peak-at>Busiest · {{ $trendPeakAt }}</span></p>
                <p class="dash-trend-stat"><strong data-trend-latest>{{ $trendLatest }}</strong><span data-trend-latest-at>Latest · {{ $trendLatestAt }}</span></p>
            </div>
        </div>
        @include('admin.dashboard.partials.filters', ['fields' => ['range']])
        <div data-dash-body>
            @if (collect($trendValues)->sum() === 0)
                <div class="admin-empty">No enquiries in this period yet.</div>
            @else
                <div class="dash-trend-chart"><canvas id="monthlyChart" aria-label="Lead trend chart"></canvas></div>
            @endif
        </div>
    </div>
    <div class="admin-card dash-panel dash-block dash-service" data-dash-section="services" data-dash-url="{{ route('admin.dashboard.section', 'services') }}" data-chart="bar">
        <div class="dash-panel-head">
            <div>
                <h2>Leads by service</h2>
                <span data-dash-caption>{{ $status !== '' ? $status : 'Top enquiry sources' }}</span>
            </div>
            @php
                $serviceList = collect($serviceLabels)->values();
                $serviceCounts = collect($serviceValues)->map(fn ($value) => (int) $value)->values();
                $serviceTotal = (int) $serviceCounts->sum();
                $serviceTop = (int) ($serviceCounts->max() ?? 0);
                $serviceTopAt = $serviceList[$serviceCounts->search($serviceTop)] ?? '—';
                $serviceShare = $serviceTotal > 0 ? (int) round(($serviceTop / $serviceTotal) * 100) : 0;
            @endphp
            <div class="dash-trend-stats" data-service-stats @if ($serviceList->isEmpty()) hidden @endif>
                <p class="dash-trend-stat"><strong data-service-count>{{ $serviceList->count() }}</strong><span>Services</span></p>
                <p class="dash-trend-stat is-peak"><strong data-service-top>{{ $serviceTop }}</strong><span data-service-top-at>Top · {{ $serviceTopAt }}</span></p>
                <p class="dash-trend-stat"><strong data-service-share>{{ $serviceShare }}%</strong><span>Share of the top service</span></p>
            </div>
        </div>
        @include('admin.dashboard.partials.filters', ['fields' => ['range', 'status']])
        <div data-dash-body>
            @if ($serviceLabels->isEmpty())
                <div class="admin-empty">No service-tagged leads yet.</div>
            @else
                <div class="dash-service-chart" style="height: {{ max(180, $serviceList->count() * 58 + 28) }}px"><canvas id="serviceChart" aria-label="Leads by service chart"></canvas></div>
            @endif
        </div>
    </div>
</section>

<section class="dash-block dash-attention-panel" id="needs-attention" aria-label="Needs attention">
    <div class="dash-section-head">
        <div>
            <h2>Needs attention</h2>
            <p>{{ $followUpsOverdue }} overdue · {{ $followUpsTodayCount }} due today</p>
        </div>
    </div>
    <div class="dash-attention-list">
        @include('admin.dashboard.partials.attention')
    </div>
</section>
@endcan
@endsection

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.1/dist/chart.umd.min.js"></script>
<script>
const navy = '#0b2c5f';
const gold = '#d4a017';
const charts = {};

Chart.register({
    id: 'barValues',
    afterDatasetsDraw(chart) {
        if (chart.config.type !== 'bar') return;
        const { ctx } = chart;
        const meta = chart.getDatasetMeta(0);
        ctx.save();
        ctx.fillStyle = '#0b2c5f';
        ctx.font = '700 13px Outfit, sans-serif';
        ctx.textBaseline = 'middle';
        meta.data.forEach((bar, index) => {
            ctx.fillText(String(chart.data.datasets[0].data[index]), bar.x + 8, bar.y);
        });
        ctx.restore();
    }
});

const chartOptions = {
    line: {
        maintainAspectRatio: false,
        interaction: { mode: 'index', intersect: false },
        plugins: {
            legend: { display: false },
            tooltip: {
                backgroundColor: '#0b2c5f',
                titleColor: '#fff',
                bodyColor: '#fbb50b',
                titleFont: { family: 'Outfit, sans-serif', size: 13, weight: '700' },
                bodyFont: { family: 'Outfit, sans-serif', size: 15, weight: '700' },
                padding: 12,
                displayColors: false,
                callbacks: {
                    label: (item) => item.parsed.y + (item.parsed.y === 1 ? ' enquiry' : ' enquiries')
                }
            }
        },
        scales: {
            y: { beginAtZero: true, ticks: { precision: 0, color: '#475569', font: { family: 'Outfit, sans-serif', size: 12, weight: '600' } }, grid: { color: 'rgba(226,232,240,.9)' }, border: { display: false } },
            x: { grid: { display: false }, ticks: { color: '#334155', font: { family: 'Outfit, sans-serif', size: 12, weight: '600' }, maxRotation: 0, autoSkip: true, maxTicksLimit: 8 }, border: { display: false } }
        }
    },
    bar: {
        maintainAspectRatio: false,
        indexAxis: 'y',
        layout: { padding: { right: 40 } },
        plugins: {
            legend: { display: false },
            tooltip: {
                backgroundColor: '#0b2c5f',
                titleColor: '#fff',
                bodyColor: '#fbb50b',
                titleFont: { family: 'Outfit, sans-serif', size: 13, weight: '700' },
                bodyFont: { family: 'Outfit, sans-serif', size: 15, weight: '700' },
                padding: 12,
                displayColors: false,
                callbacks: {
                    label: (item) => item.parsed.x + (item.parsed.x === 1 ? ' enquiry' : ' enquiries')
                }
            }
        },
        scales: {
            x: { beginAtZero: true, ticks: { precision: 0, color: '#475569', font: { family: 'Outfit, sans-serif', size: 12, weight: '600' } }, grid: { color: 'rgba(226,232,240,.9)' }, border: { display: false } },
            y: { grid: { display: false }, ticks: { color: '#0b2c5f', font: { family: 'Outfit, sans-serif', size: 13, weight: '700' } }, border: { display: false } }
        }
    }
};

function lineDataset(canvas, values) {
    const peak = Math.max(0, ...values);
    const gradient = canvas.getContext('2d').createLinearGradient(0, 0, 0, canvas.parentElement?.clientHeight || 340);
    gradient.addColorStop(0, 'rgba(11, 44, 95, 0.28)');
    gradient.addColorStop(1, 'rgba(11, 44, 95, 0.02)');
    return {
        label: 'Leads',
        data: values,
        borderColor: '#0b2c5f',
        backgroundColor: gradient,
        fill: true,
        tension: 0.42,
        borderWidth: 3,
        pointBackgroundColor: values.map((value) => value === peak && peak > 0 ? '#fbb50b' : '#fff'),
        pointBorderColor: '#0b2c5f',
        pointBorderWidth: 2,
        pointRadius: values.map((value) => value === peak && peak > 0 ? 7 : 4),
        pointHoverRadius: 9,
        pointHoverBackgroundColor: '#fbb50b',
    };
}

function barDataset(values) {
    const peak = Math.max(0, ...values);
    return {
        label: 'Leads',
        data: values,
        backgroundColor: values.map((value) => value === peak && peak > 0 ? '#fbb50b' : '#013a9d'),
        borderRadius: 0,
        barThickness: 22,
    };
}

function paintServiceStats(section, labels, values) {
    const stats = section.querySelector('[data-service-stats]');
    if (!stats) return;
    if (!values.length) {
        stats.hidden = true;
        return;
    }
    stats.hidden = false;
    let peakIndex = 0;
    values.forEach((value, index) => {
        if (value >= values[peakIndex]) peakIndex = index;
    });
    const total = values.reduce((sum, value) => sum + value, 0);
    stats.querySelector('[data-service-count]').textContent = String(labels.length);
    stats.querySelector('[data-service-top]').textContent = String(values[peakIndex]);
    stats.querySelector('[data-service-top-at]').textContent = 'Top · ' + (labels[peakIndex] || '—');
    stats.querySelector('[data-service-share]').textContent = (total > 0 ? Math.round((values[peakIndex] / total) * 100) : 0) + '%';
}

function paintTrendStats(section, labels, values) {
    const stats = section.querySelector('[data-trend-stats]');
    if (!stats) return;
    if (!values.length) {
        stats.hidden = true;
        return;
    }
    stats.hidden = false;
    let peakIndex = 0;
    values.forEach((value, index) => {
        if (value >= values[peakIndex]) peakIndex = index;
    });
    const latest = values.length - 1;
    stats.querySelector('[data-trend-total]').textContent = String(values.reduce((sum, value) => sum + value, 0));
    stats.querySelector('[data-trend-peak]').textContent = String(values[peakIndex]);
    stats.querySelector('[data-trend-peak-at]').textContent = 'Busiest · ' + (labels[peakIndex] || '—');
    stats.querySelector('[data-trend-latest]').textContent = String(values[latest]);
    stats.querySelector('[data-trend-latest-at]').textContent = 'Latest · ' + (labels[latest] || '—');
}

function paintChart(section, payload) {
    const body = section.querySelector('[data-dash-body]');
    const caption = section.querySelector('[data-dash-caption]');
    const key = section.dataset.dashSection;
    const values = (payload.values || []).map((value) => Number(value));
    if (caption && payload.caption) caption.textContent = payload.caption;
    if (section.dataset.chart === 'line') paintTrendStats(section, payload.labels || [], values);
    if (section.dataset.chart === 'bar') paintServiceStats(section, payload.labels || [], values);
    if (values.reduce((sum, value) => sum + value, 0) === 0) {
        charts[key]?.destroy();
        delete charts[key];
        body.innerHTML = '<div class="admin-empty"></div>';
        body.querySelector('.admin-empty').textContent = payload.empty || 'Nothing to show.';
        return;
    }
    let canvas = body.querySelector('canvas');
    if (!canvas) {
        body.innerHTML = section.dataset.chart === 'line'
            ? '<div class="dash-trend-chart"><canvas aria-label="Lead trend chart"></canvas></div>'
            : '<div class="dash-service-chart"><canvas aria-label="Leads by service chart"></canvas></div>';
        canvas = body.querySelector('canvas');
    }
    if (section.dataset.chart === 'bar' && canvas.parentElement) {
        canvas.parentElement.style.height = Math.max(180, values.length * 58 + 28) + 'px';
    }
    const dataset = section.dataset.chart === 'bar'
        ? barDataset(values)
        : lineDataset(canvas, values);
    if (charts[key]) {
        charts[key].data.labels = payload.labels;
        Object.assign(charts[key].data.datasets[0], dataset);
        charts[key].resize();
        charts[key].update();
        return;
    }
    charts[key] = new Chart(canvas, {
        type: section.dataset.chart,
        data: { labels: payload.labels, datasets: [dataset] },
        options: chartOptions[section.dataset.chart]
    });
}

document.querySelectorAll('[data-dash-section]').forEach((section) => {
    const refresh = async () => {
        const params = new URLSearchParams();
        section.querySelectorAll('[data-dash-filter]').forEach((field) => {
            if (field.value) params.set(field.name, field.value);
        });
        section.classList.add('is-loading');
        window.startAdminProgress?.();
        try {
            const response = await fetch(section.dataset.dashUrl + '?' + params.toString(), {
                headers: { 'Accept': 'application/json', 'X-Requested-With': 'XMLHttpRequest' }
            });
            if (!response.ok) return;
            const payload = await response.json();
            if (section.dataset.chart) {
                paintChart(section, payload);
            } else if (payload.html != null) {
                const body = section.querySelector('[data-dash-body]');
                if (body) body.innerHTML = payload.html;
                const note = section.querySelector('[data-dash-note]');
                if (note && payload.note) note.textContent = payload.note;
            }
        } finally {
            section.classList.remove('is-loading');
            window.finishAdminProgress?.();
        }
    };

    section.querySelectorAll('[data-dash-filter]').forEach((field) => {
        field.addEventListener('change', refresh);
    });

    section.addEventListener('click', (event) => {
        const choice = event.target.closest('[data-dash-choice] [data-value]');
        if (choice && section.contains(choice)) {
            const group = choice.closest('[data-dash-choice]');
            const input = group.querySelector('[data-dash-filter]');
            if (!input || input.value === choice.dataset.value) return;
            input.value = choice.dataset.value;
            group.querySelectorAll('[data-value]').forEach((item) => {
                const active = item === choice;
                item.classList.toggle('is-active', active);
                item.setAttribute('aria-pressed', active ? 'true' : 'false');
            });
            input.dispatchEvent(new Event('change'));
            return;
        }

        const stage = event.target.closest('[data-dash-stage]');
        if (!stage) return;
        const input = document.querySelector('[data-dash-section="leads"] [name="status"]');
        if (!input) return;
        const next = input.value === stage.dataset.dashStage ? '' : stage.dataset.dashStage;
        input.value = next;
        input.closest('[data-dash-choice]')?.querySelectorAll('[data-value]').forEach((item) => {
            const active = item.dataset.value === next;
            item.classList.toggle('is-active', active);
            item.setAttribute('aria-pressed', active ? 'true' : 'false');
        });
        input.dispatchEvent(new Event('change'));
    });
});

document.querySelectorAll('[data-dash-jump]').forEach((link) => {
    link.addEventListener('click', (event) => {
        const target = document.querySelector(link.getAttribute('href'));
        const scroller = document.querySelector('.admin-main');
        if (!target || !scroller) return;
        event.preventDefault();
        const top = target.getBoundingClientRect().top - scroller.getBoundingClientRect().top + scroller.scrollTop - 16;
        scroller.scrollTo({ top, behavior: 'smooth' });
        history.replaceState(null, '', link.getAttribute('href'));
    });
});

document.querySelectorAll('[data-chart] canvas').forEach((canvas) => {
    const section = canvas.closest('[data-dash-section]');
    if (!section || typeof Chart === 'undefined') return;
    const initial = section.dataset.chart === 'bar'
        ? { labels: @json($serviceLabels), values: @json($serviceValues), caption: @json($status !== '' ? $status : 'Top enquiry sources'), empty: 'No service-tagged leads yet.' }
        : { labels: @json($trendLabels), values: @json($trendValues), caption: @json($range === 'all' ? 'Last 12 months' : $rangeLabel), empty: 'No enquiries in this period yet.' };
    paintChart(section, initial);
});
</script>
@endpush
