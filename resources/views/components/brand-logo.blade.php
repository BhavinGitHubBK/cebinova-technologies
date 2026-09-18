@props([
    'variant' => 'header',
    'tone' => 'light',
])

<x-cebinova-logo :variant="$variant" :tone="$tone" {{ $attributes }} />
