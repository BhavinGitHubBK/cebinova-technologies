@props([
    'variant' => 'header',
    'tone' => 'light',
])

<x-sarvix-logo :variant="$variant" :tone="$tone" {{ $attributes }} />
