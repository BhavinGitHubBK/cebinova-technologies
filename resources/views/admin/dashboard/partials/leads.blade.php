@php
    $statusBadge = fn (string $status) => match ($status) {
        'New' => 'is-new',
        'Contacted' => 'is-contacted',
        'Follow-up', 'Qualified', 'Proposal Sent' => 'is-follow',
        'Converted', 'Won' => 'is-won',
        'Lost' => 'is-lost',
        default => '',
    };
@endphp
@forelse ($recentLeads as $lead)
    <div class="dash-lead">
        <a class="dash-lead-main" href="{{ route('admin.leads.show', $lead) }}">
            <span class="dash-avatar" aria-hidden="true">{{ strtoupper(substr($lead->name, 0, 1)) }}</span>
            <span>
                <strong>{{ $lead->name }}</strong>
                <span class="dash-row-meta">{{ $lead->service ?: 'General' }} · {{ $lead->created_at?->diffForHumans() }}</span>
            </span>
        </a>
        <span class="admin-badge {{ $statusBadge($lead->status) }}">{{ $lead->status }}</span>
        <span class="dash-lead-actions">
            <a href="tel:{{ preg_replace('/\D+/', '', $lead->phone) }}" title="Call">Call</a>
            @if ($lead->email)
                <a href="mailto:{{ $lead->email }}" title="Email">Email</a>
            @endif
            @if ($lead->whatsapp || $lead->phone)
                <a href="https://wa.me/{{ preg_replace('/\D+/', '', $lead->whatsapp ?: $lead->phone) }}" target="_blank" rel="noopener" title="WhatsApp">WA</a>
            @endif
        </span>
    </div>
@empty
    <div class="admin-empty">No leads match this view.</div>
@endforelse
