@props([
    'tone' => 'light',
    'tagline' => true,
])

<x-cebinova-logo
    :variant="$tagline ? 'full' : 'compact'"
    :tone="$tone"
    {{ $attributes }}
/>
