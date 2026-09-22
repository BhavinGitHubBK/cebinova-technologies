@extends('layouts.admin')

@section('title', 'Dashboard')
@section('breadcrumb', 'Admin / Dashboard')
@section('heading', 'Dashboard')

@section('content')
@php
    $hour = (int) now()->format('G');
    $greeting = $hour < 12 ? 'Good morning' : ($hour < 17 ? 'Good afternoon' : 'Good evening');
@endphp

<section class="dash-hero">
    <div class="dash-hero-copy">
        <p class="dash-hero-kicker">
            <span class="dash-hero-dot" aria-hidden="true"></span>
            CEBINOVA Admin
        </p>
        <h2 class="dash-hero-title">{{ $greeting }}, {{ auth()->user()->name }}</h2>
        <p class="dash-hero-sub">{{ now()->format('l, j F Y') }} · Pipeline overview and quick actions</p>
    </div>
    <div class="dash-hero-chips">
        <a class="dash-chip" href="{{ route('admin.leads.index', ['status' => 'New']) }}">
            <strong>{{ $newLeadsCount }}</strong>
            <span>New leads</span>
        </a>
        <a class="dash-chip dash-chip-accent" href="{{ route('admin.leads.index') }}">
            <strong>{{ $leadsToday }}</strong>
            <span>Today</span>
        </a>
        <a class="dash-chip {{ $followUpsOverdue > 0 ? 'dash-chip-warn' : '' }}" href="{{ route('admin.leads.index') }}">
            <strong>{{ $followUpsDue->count() }}</strong>
            <span>Follow-ups due</span>
        </a>
    </div>
</section>

<section class="dash-kpi-grid">
    <article class="dash-kpi">
        <p class="dash-kpi-label">Total leads</p>
        <p class="dash-kpi-value">{{ $totalLeads }}</p>
    </article>
    <article class="dash-kpi dash-kpi-new">
        <p class="dash-kpi-label">New</p>
        <p class="dash-kpi-value">{{ $newLeadsCount }}</p>
    </article>
    <article class="dash-kpi dash-kpi-converted">
        <p class="dash-kpi-label">Converted</p>
        <p class="dash-kpi-value">{{ $convertedCount }}</p>
    </article>
    <article class="dash-kpi dash-kpi-rate">
        <p class="dash-kpi-label">Conversion</p>
        <p class="dash-kpi-value">{{ $conversionRate }}%</p>
    </article>
    <article class="dash-kpi">
        <p class="dash-kpi-label">Services</p>
        <p class="dash-kpi-value">{{ $servicesCount }}</p>
    </article>
    <article class="dash-kpi dash-kpi-gold">
        <p class="dash-kpi-label">Active packages</p>
        <p class="dash-kpi-value">{{ $activePackages }}</p>
    </article>
</section>

<section class="dash-pipeline" aria-label="Lead pipeline">
    <div class="dash-pipe"><span class="admin-badge admin-badge-new">New</span><strong>{{ $newLeadsCount }}</strong></div>
    <div class="dash-pipe"><span class="admin-badge">Contacted</span><strong>{{ $contactedCount }}</strong></div>
    <div class="dash-pipe"><span class="admin-badge">Follow-up</span><strong>{{ $followUpCount }}</strong></div>
    <div class="dash-pipe"><span class="admin-badge admin-badge-converted">Converted</span><strong>{{ $convertedCount }}</strong></div>
    <div class="dash-pipe"><span class="admin-badge admin-badge-lost">Lost</span><strong>{{ $lostCount }}</strong></div>
</section>

<section class="dash-actions" aria-label="Quick actions">
    <a class="admin-btn admin-btn-primary" href="{{ route('admin.leads.index') }}">Manage leads</a>
    <a class="admin-btn admin-btn-accent" href="{{ route('admin.services.create') }}">Add service</a>
    <a class="admin-btn admin-btn-ghost" href="{{ route('admin.packages.create') }}">Add package</a>
    <a class="admin-btn admin-btn-ghost" href="{{ route('admin.blog-posts.create') }}">New blog post</a>
    <a class="admin-btn admin-btn-ghost" href="{{ route('admin.page-sections.index') }}">Page sections</a>
    <a class="admin-btn admin-btn-ghost" href="{{ route('admin.settings.edit') }}">Settings</a>
</section>

<section class="dash-grid-2">
    <div class="admin-card dash-panel">
        <div class="dash-panel-head">
            <h2>Monthly leads</h2>
            <span>Last 12 months</span>
        </div>
        <canvas id="monthlyChart" height="160"></canvas>
    </div>
    <div class="admin-card dash-panel">
        <div class="dash-panel-head">
            <h2>Leads by service</h2>
            <span>Top 8</span>
        </div>
        <canvas id="serviceChart" height="160"></canvas>
    </div>
</section>

<section class="dash-grid-3">
    <div class="admin-card dash-panel">
        <div class="dash-panel-head">
            <h2>Recent enquiries</h2>
            <a href="{{ route('admin.leads.index') }}">View all</a>
        </div>
        @forelse ($recentLeads as $lead)
            @php
                $badge = match ($lead->status) {
                    'New' => 'admin-badge-new',
                    'Converted' => 'admin-badge-converted',
                    'Lost' => 'admin-badge-lost',
                    default => '',
                };
            @endphp
            <a class="dash-row" href="{{ route('admin.leads.show', $lead) }}">
                <div>
                    <strong>{{ $lead->name }}</strong>
                    <div class="dash-row-meta">{{ $lead->service ?: 'General' }} · {{ $lead->created_at?->diffForHumans() }}</div>
                </div>
                <span class="admin-badge {{ $badge }}">{{ $lead->status }}</span>
            </a>
        @empty
            <div class="admin-empty">No leads yet.</div>
        @endforelse
    </div>

    <div class="admin-card dash-panel">
        <div class="dash-panel-head">
            <h2>Follow-ups due</h2>
            @if ($followUpsOverdue > 0)
                <span class="dash-warn">{{ $followUpsOverdue }} overdue</span>
            @else
                <span>Today &amp; overdue</span>
            @endif
        </div>
        @forelse ($followUpsDue as $lead)
            <a class="dash-row" href="{{ route('admin.leads.show', $lead) }}">
                <div>
                    <strong>{{ $lead->name }}</strong>
                    <div class="dash-row-meta">
                        {{ $lead->follow_up_at?->format('d M Y, H:i') }}
                        @if ($lead->follow_up_at?->lt(now()->startOfDay()))
                            · <em>Overdue</em>
                        @endif
                    </div>
                </div>
                <span class="admin-badge">{{ $lead->status }}</span>
            </a>
        @empty
            <div class="admin-empty">No follow-ups due. Nice work.</div>
        @endforelse
    </div>

    <div class="admin-card dash-panel">
        <div class="dash-panel-head">
            <h2>CMS shortcuts</h2>
            <span>Content</span>
        </div>
        <div class="dash-cms">
            <a href="{{ route('admin.projects.index') }}"><strong>{{ $projectsCount }}</strong><span>Projects</span></a>
            <a href="{{ route('admin.blog-posts.index') }}"><strong>{{ $blogPostsCount }}</strong><span>Blog posts</span></a>
            <a href="{{ route('admin.faqs.index') }}"><strong>{{ $faqsCount }}</strong><span>FAQs</span></a>
            <a href="{{ route('admin.media.index') }}"><strong>{{ $mediaCount }}</strong><span>Media</span></a>
        </div>
        <div class="dash-panel-head" style="margin-top:1rem;">
            <h2>Recent activity</h2>
        </div>
        @forelse ($recentActivities as $log)
            <div class="dash-activity">
                <strong>{{ $log->user?->name ?? 'System' }}</strong>
                <span>{{ $log->action }}</span>
                <div class="dash-row-meta">{{ $log->description }} · {{ $log->created_at?->diffForHumans() }}</div>
            </div>
        @empty
            <div class="admin-empty">No activity yet.</div>
        @endforelse
    </div>
</section>
@endsection

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.1/dist/chart.umd.min.js"></script>
<script>
const monthlyLabels = @json($monthlyLabels);
const monthlyValues = @json($monthlyValues);
const serviceLabels = @json($serviceLabels);
const serviceValues = @json($serviceValues);

new Chart(document.getElementById('monthlyChart'), {
    type: 'line',
    data: {
        labels: monthlyLabels,
        datasets: [{
            label: 'Leads',
            data: monthlyValues,
            borderColor: '#0b2c5f',
            backgroundColor: 'rgba(11, 44, 95, 0.12)',
            fill: true,
            tension: 0.35,
            pointBackgroundColor: '#d4a017',
            pointBorderColor: '#0b2c5f',
            pointRadius: 3,
        }]
    },
    options: {
        plugins: { legend: { display: false } },
        scales: {
            y: { beginAtZero: true, ticks: { precision: 0 }, grid: { color: 'rgba(226,232,240,.9)' } },
            x: { grid: { display: false } }
        }
    }
});

new Chart(document.getElementById('serviceChart'), {
    type: 'bar',
    data: {
        labels: serviceLabels,
        datasets: [{
            label: 'Leads',
            data: serviceValues,
            backgroundColor: '#d4a017',
            borderRadius: 6,
            maxBarThickness: 28,
        }]
    },
    options: {
        plugins: { legend: { display: false } },
        scales: {
            y: { beginAtZero: true, ticks: { precision: 0 }, grid: { color: 'rgba(226,232,240,.9)' } },
            x: { grid: { display: false } }
        }
    }
});
</script>
@endpush
