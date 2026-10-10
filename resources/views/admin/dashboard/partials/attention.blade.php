@php
    $focus = $focus ?? '';
    $shown = $focus === '' ? $attentionItems : array_values(array_filter($attentionItems, fn ($item) => $item['kind'] === $focus));
    $statusBadge = fn (string $status) => match ($status) {
        'New' => 'is-new',
        'Contacted' => 'is-contacted',
        'Follow-up', 'Qualified', 'Proposal Sent' => 'is-follow',
        'Converted', 'Won' => 'is-won',
        'Lost' => 'is-lost',
        default => '',
    };
@endphp
@forelse ($shown as $item)
    <a class="dash-attention dash-attention-{{ $item['tone'] }}" href="{{ $item['url'] }}">
        <span class="dash-attention-mark" aria-hidden="true">
            @if ($item['kind'] === 'overdue')
                <x-mark name="alert" class="h-4 w-4" />
            @else
                <x-mark name="clock" class="h-4 w-4" />
            @endif
        </span>
        <span class="dash-attention-body">
            <strong>{{ $item['title'] }}</strong>
            <span>{{ $item['label'] }} · {{ $item['context'] }}</span>
        </span>
        <span class="dash-attention-meta">
            <span class="admin-badge {{ $statusBadge($item['status']) }}">{{ $item['status'] }}</span>
            <em>{{ $item['when'] }}</em>
        </span>
    </a>
@empty
    <div class="admin-empty">Nothing needs action right now.</div>
@endforelse
