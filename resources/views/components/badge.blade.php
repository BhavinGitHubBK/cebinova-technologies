@props([
    'tone' => 'gold',
])

@php
    $tones = [
        'gold' => 'sx-badge-gold',
        'blue' => 'sx-badge-blue',
        'grey' => 'sx-badge-grey',
        'navy' => 'sx-badge-navy',
    ];
@endphp

<span {{ $attributes->merge(['class' => 'sx-badge '.($tones[$tone] ?? $tones['gold'])]) }}>{{ $slot }}</span>
