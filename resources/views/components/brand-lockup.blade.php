@props([
    'tone' => 'light',
    'tagline' => true,
])

<x-sarvix-logo
    :variant="$tagline ? 'full' : 'compact'"
    :tone="$tone"
    {{ $attributes }}
/>
