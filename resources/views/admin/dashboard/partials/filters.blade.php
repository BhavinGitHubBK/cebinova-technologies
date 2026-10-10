@php
    $groups = [];
    if (in_array('range', $fields, true)) {
        $groups[] = ['name' => 'range', 'label' => 'Period', 'value' => $range, 'options' => $ranges];
    }
    if (in_array('status', $fields, true)) {
        $groups[] = ['name' => 'status', 'label' => 'Stage', 'value' => $status, 'options' => ['' => 'All stages'] + array_combine(\App\Models\Lead::STATUSES, \App\Models\Lead::STATUSES)];
    }
    if (in_array('service', $fields, true)) {
        $serviceOptions = ['' => 'All services'];
        foreach ($services as $serviceName) {
            $serviceOptions[$serviceName] = $serviceName;
        }
        $groups[] = ['name' => 'service', 'label' => 'Service', 'value' => $service, 'options' => $serviceOptions];
    }
    if (in_array('focus', $fields, true)) {
        $groups[] = ['name' => 'focus', 'label' => 'Show', 'value' => $focus ?? '', 'options' => ['' => 'All due', 'overdue' => 'Overdue', 'today' => 'Due today']];
    }
@endphp
<div class="dash-section-filters">
    @foreach ($groups as $group)
        <div class="dash-choice" data-dash-choice>
            <span>{{ $group['label'] }}</span>
            <div role="radiogroup" aria-label="{{ $group['label'] }}">
                <input type="hidden" name="{{ $group['name'] }}" value="{{ $group['value'] }}" data-dash-filter>
                @foreach ($group['options'] as $value => $label)
                    <button type="button" class="{{ (string) $group['value'] === (string) $value ? 'is-active' : '' }}" data-value="{{ $value }}" aria-pressed="{{ (string) $group['value'] === (string) $value ? 'true' : 'false' }}">{{ $label }}</button>
                @endforeach
            </div>
        </div>
    @endforeach
</div>
