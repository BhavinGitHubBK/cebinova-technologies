<a class="dash-kpi" href="{{ route('admin.leads.index') }}">
    <span class="dash-kpi-icon" aria-hidden="true"><x-mark name="users" class="h-4 w-4" /></span>
    <p class="dash-kpi-label">Leads</p>
    <p class="dash-kpi-value">{{ $totalLeads }}</p>
    <p class="dash-kpi-hint">{{ $range === 'all' ? 'Every enquiry on record' : 'Created in '.strtolower($rangeLabel) }}</p>
</a>
<a class="dash-kpi is-pipe" href="{{ route('admin.leads.index') }}">
    <span class="dash-kpi-icon" aria-hidden="true"><x-mark name="layers" class="h-4 w-4" /></span>
    <p class="dash-kpi-label">Still open</p>
    <p class="dash-kpi-value">{{ $pipelineCount }}</p>
    <p class="dash-kpi-hint">Not won or lost yet</p>
</a>
<a class="dash-kpi is-won" href="{{ route('admin.leads.index', ['status' => 'Converted']) }}">
    <span class="dash-kpi-icon" aria-hidden="true"><x-mark name="check" class="h-4 w-4" /></span>
    <p class="dash-kpi-label">Won</p>
    <p class="dash-kpi-value">{{ $wonCount }}</p>
    <p class="dash-kpi-hint">Converted in this period</p>
</a>
<article class="dash-kpi is-rate">
    <span class="dash-kpi-icon" aria-hidden="true"><x-mark name="trend" class="h-4 w-4" /></span>
    <p class="dash-kpi-label">Conversion</p>
    <p class="dash-kpi-value">{{ $conversionRate }}%</p>
    <p class="dash-kpi-hint">Won ÷ leads in this view</p>
</article>
<a class="dash-kpi {{ ($followUpsOverdue + $followUpsTodayCount) > 0 ? 'is-alert' : '' }}" href="#needs-attention" data-dash-jump>
    <span class="dash-kpi-icon" aria-hidden="true"><x-mark name="alert" class="h-4 w-4" /></span>
    <p class="dash-kpi-label">Needs attention</p>
    <p class="dash-kpi-value">{{ $followUpsOverdue + $followUpsTodayCount }}</p>
    <p class="dash-kpi-hint">{{ $followUpsOverdue }} overdue · {{ $followUpsTodayCount }} due today</p>
</a>
