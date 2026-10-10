@extends('layouts.admin')
@section('title', 'Management reports')
@section('heading', 'Management reports')
@section('content')
<p class="report-intro">Track business growth, sales performance and opportunities that need your attention.</p>
<form method="GET" action="{{ route('admin.reports.index') }}" class="admin-card report-range">
    @include('admin.reports.range')
    <button class="admin-btn admin-btn-primary" type="submit">Update reports</button>
</form>

<section class="mgmt-kpis" aria-label="Key figures">
    @foreach ($kpis as $kpi)
        <article class="admin-card mgmt-kpi is-{{ $kpi['tone'] }}" title="{{ $kpi['exact'] }}">
            <p>{{ $kpi['label'] }}</p>
            <strong>{{ $kpi['value'] }}</strong>
            <span>{{ $kpi['note'] }}</span>
        </article>
    @endforeach
</section>

<section class="dash-analytics" aria-label="Business growth charts">
    @php
        $growthPeak = $growth['enquiries'] === [] ? 0 : max($growth['enquiries']);
        $growthPeakIndex = $growth['enquiries'] === [] ? 0 : array_search($growthPeak, $growth['enquiries'], true);
        $growthLatestIndex = count($growth['enquiries']) - 1;
    @endphp
    <div class="admin-card dash-panel dash-panel-wide dash-block dash-trend">
        <div class="dash-panel-head">
            <div>
                <h2>Business growth</h2>
                <span>Enquiries and won deals</span>
            </div>
            @if ($growth['has_data'])
                <div class="dash-trend-stats">
                    <p class="dash-trend-stat"><strong>{{ array_sum($growth['enquiries']) }}</strong><span>Enquiries</span></p>
                    <p class="dash-trend-stat is-peak"><strong>{{ $growthPeak }}</strong><span>Busiest · {{ $growth['labels'][$growthPeakIndex] ?? '—' }}</span></p>
                    <p class="dash-trend-stat"><strong>{{ $growth['enquiries'][$growthLatestIndex] ?? 0 }}</strong><span>Latest · {{ $growth['labels'][$growthLatestIndex] ?? '—' }}</span></p>
                </div>
            @endif
        </div>
        @if ($growth['has_data'])
            <div class="dash-trend-chart"><canvas id="mgmt-growth" aria-label="Enquiries and won deals over time"></canvas></div>
        @else
            <div class="admin-empty">No enquiries recorded for this period.</div>
        @endif
    </div>

    <section class="dash-work dash-work-bottom">
        <article class="admin-card dash-panel dash-block dash-service">
            <div class="dash-panel-head">
                <div>
                    <h2>Where enquiries come from</h2>
                    <span>Lead sources in this period</span>
                </div>
            </div>
            @if ($sources !== [])
                <div class="dash-service-chart" style="height: {{ max(180, count($sources) * 58 + 28) }}px"><canvas id="mgmt-sources" aria-label="Enquiries by source"></canvas></div>
            @else
                <div class="admin-empty">No lead-source data available.</div>
            @endif
        </article>

        <article class="admin-card dash-panel dash-block dash-service">
            <div class="dash-panel-head">
                <div>
                    <h2>Sales funnel</h2>
                    <span>Current status of this period’s enquiries</span>
                </div>
            </div>
            @if ($funnel['has_data'])
                <div class="dash-service-chart" style="height: {{ max(180, count($funnel['stages']) * 58 + 28) }}px"><canvas id="mgmt-funnel" aria-label="Sales funnel by current status"></canvas></div>
                @if ($funnel['rates'] !== [])
                    <ul class="mgmt-rates">
                        @foreach ($funnel['rates'] as $rate)
                            <li><span>{{ $rate['label'] }}</span><strong>{{ $rate['value'] }}</strong></li>
                        @endforeach
                    </ul>
                @endif
                <p class="mgmt-note">Based on each enquiry’s current status. Earlier stages are not stored separately.</p>
            @else
                <div class="admin-empty">No enquiries recorded for this period.</div>
            @endif
        </article>

        <article class="admin-card dash-panel dash-block dash-service">
            <div class="dash-panel-head">
                <div>
                    <h2>Most requested services</h2>
                    <span>What customers asked for</span>
                </div>
                @if ($services !== [])
                    @php
                        $servicePeak = max(array_column($services, 'total'));
                        $serviceTop = collect($services)->firstWhere('total', $servicePeak);
                    @endphp
                    <div class="dash-trend-stats">
                        <p class="dash-trend-stat"><strong>{{ count($services) }}</strong><span>Services</span></p>
                        <p class="dash-trend-stat is-peak"><strong>{{ $servicePeak }}</strong><span>Top · {{ $serviceTop['label'] ?? '—' }}</span></p>
                        <p class="dash-trend-stat"><strong>{{ $serviceTop['share'] ?? 0 }}%</strong><span>Share of the top service</span></p>
                    </div>
                @endif
            </div>
            @if ($services !== [])
                <div class="dash-service-chart" style="height: {{ max(180, count($services) * 58 + 28) }}px"><canvas id="mgmt-services" aria-label="Enquiries by service"></canvas></div>
            @else
                <div class="admin-empty">No service data for this period.</div>
            @endif
        </article>

        <article class="admin-card dash-panel dash-block dash-service">
            <div class="dash-panel-head">
                <div>
                    <h2>Won vs lost</h2>
                    <span>Win rate {{ $closing['rate_label'] }} of enquiries in this period</span>
                </div>
            </div>
            @if (($closing['won'] + $closing['lost']) > 0)
                <div class="dash-service-chart" style="height: 180px"><canvas id="mgmt-closing" aria-label="Won and lost enquiries"></canvas></div>
                @if ($closing['reasons'] !== [])
                    <ul class="mgmt-rates">
                        @foreach ($closing['reasons'] as $reason)
                            <li><span>{{ $reason['label'] }}</span><strong>{{ $reason['total'] }}</strong></li>
                        @endforeach
                    </ul>
                @endif
            @else
                <div class="admin-empty">No won or lost deals in this period.</div>
            @endif
        </article>
    </section>
</section>

<section class="admin-card mgmt-block" aria-label="Business insights">
    <h2>Business insights</h2>
    @if ($insights === [])
        <p class="mgmt-empty">Not enough activity in this period to suggest a decision.</p>
    @else
        <div class="mgmt-insights">
            @foreach ($insights as $insight)
                <article class="is-{{ $insight['tone'] }}">
                    <h3>{{ $insight['title'] }}</h3>
                    <p>{{ $insight['detail'] }}</p>
                </article>
            @endforeach
        </div>
    @endif
</section>

<section class="admin-card mgmt-block" aria-label="Needs attention">
    <h2>Needs attention</h2>
    @if ($attention === [])
        <p class="mgmt-empty">Nothing needs action right now.</p>
    @else
        <div class="mgmt-attention">
            @foreach ($attention as $item)
                <a href="{{ $item['href'] }}">
                    <strong>{{ $item['title'] }}</strong>
                    <span>{{ $item['detail'] }}</span>
                </a>
            @endforeach
        </div>
    @endif
</section>

@if ($pipeline['count'] > 0 || $cities !== [])
<section class="mgmt-split">
    @if ($pipeline['count'] > 0)
        <article class="admin-card mgmt-block">
            <h2>Pipeline snapshot</h2>
            <ul class="mgmt-rates">
                <li><span>Open opportunities</span><strong>{{ $pipeline['count'] }}</strong></li>
                <li><span>Pipeline value</span><strong>{{ $pipeline['value_label'] }}</strong></li>
                <li><span>Average deal value</span><strong>{{ $pipeline['average_label'] }}</strong></li>
                <li><span>High-value opportunities</span><strong title="Above the average valued open deal">{{ $pipeline['high'] }}</strong></li>
            </ul>
        </article>
    @endif
    @if ($cities !== [])
        <article class="admin-card mgmt-block">
            <h2>Top markets</h2>
            <ul class="mgmt-rates">
                @foreach ($cities as $city)
                    <li><span>{{ $city['label'] }}</span><strong>{{ $city['total'] }}</strong></li>
                @endforeach
            </ul>
        </article>
    @endif
</section>
@endif

<section class="admin-card mgmt-records">
    <div>
        <h2>Detailed records</h2>
        <p>View the enquiries behind these reports.</p>
    </div>
    <div>
        <a class="admin-btn admin-btn-ghost" href="{{ $records['leads'] }}">View leads</a>
        <a class="admin-btn admin-btn-primary" href="{{ $records['export'] }}">Export report</a>
    </div>
</section>

<style>
    .mgmt-kpis, .mgmt-split { display: grid; gap: .85rem; }
    .mgmt-kpis { grid-template-columns: repeat(4, minmax(0, 1fr)); margin-bottom: 1rem; }
    .mgmt-split { grid-template-columns: 1fr 1fr; }
    .mgmt-kpi, .mgmt-block { display: grid; gap: .35rem; align-content: start; }
    .mgmt-kpi p, .mgmt-block h2, .mgmt-records h2 { margin: 0; color: #0b2c5f; }
    .mgmt-kpi p { font-size: .78rem; font-weight: 700; letter-spacing: .03em; text-transform: uppercase; color: #64748b; }
    .mgmt-kpi strong { font-size: 1.7rem; line-height: 1.1; color: #0b2c5f; }
    .mgmt-kpi span, .mgmt-note, .mgmt-empty, .mgmt-records p, .mgmt-insights p { margin: 0; color: #475569; font-size: .88rem; line-height: 1.4; }
    .mgmt-kpi.is-positive strong, .mgmt-kpi.is-positive span { color: #15803d; }
    .mgmt-kpi.is-attention strong, .mgmt-kpi.is-attention span { color: #9f1239; }
    .mgmt-records { grid-column: 1 / -1; }
    .mgmt-rates { list-style: none; margin: .75rem 0 0; padding: 0; display: grid; gap: .35rem; }
    .mgmt-rates li, .mgmt-records, .mgmt-attention a, .mgmt-insights { display: flex; justify-content: space-between; gap: .75rem; }
    .mgmt-rates strong { color: #0b2c5f; }
    .mgmt-insights { display: grid; grid-template-columns: repeat(auto-fit, minmax(220px, 1fr)); gap: .75rem; }
    .mgmt-insights article { padding: .75rem .8rem; border-left: 3px solid #013a9d; background: #f8fafc; }
    .mgmt-insights h3 { margin: 0 0 .2rem; font-size: .92rem; color: #0b2c5f; }
    .mgmt-insights .is-positive { border-left-color: #15803d; }
    .mgmt-insights .is-attention { border-left-color: #9f1239; }
    .mgmt-insights .is-opportunity { border-left-color: #c48e08; }
    .mgmt-attention { display: grid; gap: .55rem; }
    .mgmt-attention a { display: grid; padding: .7rem .8rem; border: 1px solid #e6ebf2; text-decoration: none; color: inherit; }
    .mgmt-attention a:hover { border-color: #013a9d; }
    .mgmt-attention strong { color: #0b2c5f; }
    .mgmt-records { display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; }
    .mgmt-records div:last-child { display: flex; gap: .5rem; flex-wrap: wrap; }
    @media (max-width: 1100px) { .mgmt-kpis { grid-template-columns: 1fr 1fr; } }
    @media (max-width: 800px) { .mgmt-kpis, .mgmt-split { grid-template-columns: 1fr; } }
</style>
@endsection

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.1/dist/chart.umd.min.js"></script>
<script>
const mgmt = {!! json_encode(['growth' => $growth, 'sources' => $sources, 'funnel' => $funnel, 'services' => $services, 'closing' => ['won' => $closing['won'], 'lost' => $closing['lost']]], JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_AMP | JSON_HEX_QUOT) !!};

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

const tip = {
    backgroundColor: '#0b2c5f',
    titleColor: '#fff',
    bodyColor: '#fbb50b',
    titleFont: { family: 'Outfit, sans-serif', size: 13, weight: '700' },
    bodyFont: { family: 'Outfit, sans-serif', size: 15, weight: '700' },
    padding: 12,
    displayColors: false
};
const lineOptions = {
    maintainAspectRatio: false,
    animation: { duration: 1000, easing: 'easeOutQuart' },
    interaction: { mode: 'index', intersect: false },
    plugins: {
        legend: { display: false },
        tooltip: { ...tip, callbacks: { label: (item) => item.dataset.label + ': ' + item.parsed.y } }
    },
    scales: {
        y: { beginAtZero: true, ticks: { precision: 0, color: '#475569', font: { family: 'Outfit, sans-serif', size: 12, weight: '600' } }, grid: { color: 'rgba(226,232,240,.9)' }, border: { display: false } },
        x: { grid: { display: false }, ticks: { color: '#334155', font: { family: 'Outfit, sans-serif', size: 12, weight: '600' }, maxRotation: 0, autoSkip: true, maxTicksLimit: 8 }, border: { display: false } }
    }
};
const barOptions = {
    maintainAspectRatio: false,
    animation: { duration: 1000, easing: 'easeOutQuart' },
    indexAxis: 'y',
    layout: { padding: { right: 40 } },
    plugins: {
        legend: { display: false },
        tooltip: { ...tip, callbacks: { label: (item) => item.parsed.x + (item.parsed.x === 1 ? ' enquiry' : ' enquiries') } }
    },
    scales: {
        x: { beginAtZero: true, ticks: { precision: 0, color: '#475569', font: { family: 'Outfit, sans-serif', size: 12, weight: '600' } }, grid: { color: 'rgba(226,232,240,.9)' }, border: { display: false } },
        y: { grid: { display: false }, ticks: { color: '#0b2c5f', font: { family: 'Outfit, sans-serif', size: 13, weight: '700' } }, border: { display: false } }
    }
};

function bars(values) {
    const peak = Math.max(0, ...values);
    return {
        data: values,
        backgroundColor: values.map((value) => value === peak && peak > 0 ? '#fbb50b' : '#013a9d'),
        borderRadius: 0,
        barThickness: 22
    };
}

if (mgmt.growth.has_data && window.Chart) {
    const canvas = document.getElementById('mgmt-growth');
    const enquiries = mgmt.growth.enquiries;
    const peak = Math.max(0, ...enquiries);
    const gradient = canvas.getContext('2d').createLinearGradient(0, 0, 0, canvas.parentElement?.clientHeight || 340);
    gradient.addColorStop(0, 'rgba(11, 44, 95, 0.28)');
    gradient.addColorStop(1, 'rgba(11, 44, 95, 0.02)');
    new Chart(canvas, {
        type: 'line',
        data: {
            labels: mgmt.growth.labels,
            datasets: [
                {
                    label: 'Enquiries',
                    data: enquiries,
                    borderColor: '#0b2c5f',
                    backgroundColor: gradient,
                    fill: true,
                    tension: 0.42,
                    borderWidth: 3,
                    pointBackgroundColor: enquiries.map((value) => value === peak && peak > 0 ? '#fbb50b' : '#fff'),
                    pointBorderColor: '#0b2c5f',
                    pointBorderWidth: 2,
                    pointRadius: enquiries.map((value) => value === peak && peak > 0 ? 7 : 4),
                    pointHoverRadius: 9,
                    pointHoverBackgroundColor: '#fbb50b'
                },
                {
                    label: 'Won deals',
                    data: mgmt.growth.won,
                    borderColor: '#fbb50b',
                    backgroundColor: '#fbb50b',
                    fill: false,
                    tension: 0.42,
                    borderWidth: 3,
                    pointBackgroundColor: '#fff',
                    pointBorderColor: '#fbb50b',
                    pointBorderWidth: 2,
                    pointRadius: 4,
                    pointHoverRadius: 9,
                    pointHoverBackgroundColor: '#fbb50b'
                }
            ]
        },
        options: lineOptions
    });
}

function paintBars(id, labels, values) {
    const canvas = document.getElementById(id);
    if (!canvas || !window.Chart || !values.length) return;
    new Chart(canvas, { type: 'bar', data: { labels, datasets: [bars(values)] }, options: barOptions });
}

paintBars('mgmt-sources', mgmt.sources.map((row) => row.label), mgmt.sources.map((row) => row.total));
if (mgmt.funnel.has_data) paintBars('mgmt-funnel', mgmt.funnel.stages.map((row) => row.label), mgmt.funnel.stages.map((row) => row.total));
paintBars('mgmt-services', mgmt.services.map((row) => row.label), mgmt.services.map((row) => row.total));
if ((mgmt.closing.won + mgmt.closing.lost) > 0) paintBars('mgmt-closing', ['Won', 'Lost'], [mgmt.closing.won, mgmt.closing.lost]);
</script>
@endpush
