@props(['column', 'label', 'sort', 'dir' => 'asc', 'descFirst' => false, 'table' => null])

@php
    $active = (string) $sort === (string) $column && ($table === null || (string) request('table') === (string) $table);
    $next = $active ? ($dir === 'asc' ? 'desc' : 'asc') : ($descFirst ? 'desc' : 'asc');
    $query = array_merge(request()->except('page'), [
        'sort' => $column,
        'dir' => $next,
    ]);
    if ($table !== null) {
        $query['table'] = $table;
    }
@endphp

<a class="lead-sort {{ $active ? 'is-active' : '' }}" href="{{ request()->url().'?'.http_build_query($query) }}">
    {{ $label }}
    <span aria-hidden="true">{{ $active ? ($dir === 'asc' ? '↑' : '↓') : '↕' }}</span>
</a>
