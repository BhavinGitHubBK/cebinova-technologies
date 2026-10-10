@extends('layouts.admin')

@section('title', 'CRM Pipeline')
@section('breadcrumb', 'Admin / CRM / Pipeline')
@section('heading', 'Sales pipeline')

@section('content')
<div class="admin-card" style="display:flex;flex-wrap:wrap;align-items:center;justify-content:space-between;gap:1rem;margin-bottom:1rem;">
    <p style="margin:0;">Open pipeline value: <strong>{{ cebinova_inr((int) $pipelineValue) }}</strong></p>
    <a class="admin-btn admin-btn-ghost" href="{{ route('admin.leads.index') }}">All leads</a>
</div>

<div style="display:grid;grid-auto-columns:minmax(280px,340px);grid-auto-flow:column;gap:.8rem;overflow-x:auto;align-items:start;padding-bottom:1rem;">
@foreach($statuses as $status)
    <section class="admin-card" aria-label="{{ $status }} leads" style="padding:.8rem;min-width:0;">
        <header style="display:flex;align-items:center;justify-content:space-between;gap:.5rem;margin-bottom:.75rem;">
            <h2 style="font-size:1rem;margin:0;">{{ $status }}</h2>
            <span class="admin-badge">{{ $counts[$status] ?? 0 }}</span>
        </header>
        <div style="display:grid;gap:.65rem;">
        @forelse($leadsByStage[$status] as $lead)
            <article class="admin-card" style="padding:.8rem;">
                <div style="display:flex;align-items:flex-start;justify-content:space-between;gap:.5rem;">
                    <div style="min-width:0;">
                        <a href="{{ route('admin.leads.show', $lead) }}"><strong>{{ $lead->name }}</strong></a>
                        @if($lead->business_name)<div class="admin-stat-label">{{ $lead->business_name }}</div>@endif
                    </div>
                    <span class="admin-badge">{{ $lead->priority }}</span>
                </div>
                <p style="margin:.55rem 0;font-size:.85rem;">{{ $lead->service ?: 'Service not specified' }}@if($lead->package_category) · {{ $lead->package_category }}@endif</p>
                @if($lead->estimated_value !== null)<p style="margin:.25rem 0;font-weight:700;">Deal estimate: {{ cebinova_inr((int) $lead->estimated_value) }}</p>@endif
                <p class="admin-stat-label" style="margin:.3rem 0;">Assigned: {{ $lead->assignee?->name ?? 'Unassigned' }}</p>
                @php($nextTask = $lead->tasks->first())
                @if($nextTask)
                    <p class="admin-stat-label" style="margin:.3rem 0;">Next: {{ $nextTask->due_at->format('d M, H:i') }} · {{ $nextTask->title }}</p>
                @elseif($lead->follow_up_at)
                    <p class="admin-stat-label" style="margin:.3rem 0;">Follow-up: {{ $lead->follow_up_at->format('d M, H:i') }}</p>
                @endif
                <div style="display:flex;gap:.4rem;flex-wrap:wrap;margin-top:.6rem;">
                    <a class="admin-btn admin-btn-ghost" href="tel:{{ preg_replace('/\D+/', '', $lead->phone) }}">Call</a>
                    @if($lead->email)<a class="admin-btn admin-btn-ghost" href="mailto:{{ $lead->email }}">Email</a>@endif
                    <a class="admin-btn admin-btn-ghost" href="{{ route('admin.leads.show', $lead) }}">Open</a>
                </div>
                @can('admin.leads.manage')
                <form method="POST" action="{{ route('admin.leads.stage', $lead) }}" style="display:grid;grid-template-columns:1fr 1fr auto;gap:.35rem;margin-top:.65rem;">
                    @csrf
                    <select class="admin-select" name="status" aria-label="Change stage for {{ $lead->name }}">
                        @foreach($statuses as $stage)<option value="{{ $stage }}" @selected($lead->status === $stage)>{{ $stage }}</option>@endforeach
                    </select>
                    <select class="admin-select" name="lost_reason" aria-label="Lost reason for {{ $lead->name }}">
                        <option value="">Lost reason</option>
                        @foreach($lostReasons as $reason)<option value="{{ $reason }}">{{ $reason }}</option>@endforeach
                    </select>
                    <button class="admin-btn admin-btn-primary" type="submit">Move</button>
                </form>
                @endcan
            </article>
        @empty
            <p class="admin-empty">No leads in this stage.</p>
        @endforelse
        </div>
        @if(($counts[$status] ?? 0) > 40)
            <p class="admin-stat-label" style="margin:.7rem 0 0;">Showing latest 40. <a href="{{ route('admin.leads.index', ['status' => $status]) }}">View all</a></p>
        @endif
    </section>
@endforeach
</div>
@endsection