@props(['name' => 'globe'])

@php
    $icons = [
        'globe' => '<circle cx="12" cy="12" r="9"/><path d="M3 12h18M12 3c3 3.5 3 14.5 0 18M12 3c-3 3.5-3 14.5 0 18"/>',
        'store' => '<path d="M4 10h16v10H4z"/><path d="M4 10l1.5-5h13L20 10"/><path d="M9 20v-6h6v6"/>',
        'spark' => '<path d="M12 3v4M12 17v4M5.6 5.6l2.8 2.8M15.6 15.6l2.8 2.8M3 12h4M17 12h4M5.6 18.4l2.8-2.8M15.6 8.4l2.8-2.8"/><circle cx="12" cy="12" r="2.5"/>',
        'trend' => '<path d="M4 16l5-5 4 3 7-8"/><path d="M14 6h6v6"/>',
        'layers' => '<path d="M12 4l9 5-9 5-9-5 9-5z"/><path d="M3 13l9 5 9-5"/><path d="M3 17l9 5 9-5"/>',
        'nodes' => '<circle cx="6" cy="7" r="2.2"/><circle cx="18" cy="7" r="2.2"/><circle cx="12" cy="17" r="2.2"/><path d="M8 8.2l2.6 6.1M16 8.2l-2.6 6.1M8.2 7h7.6"/>',
        'chat' => '<path d="M5 6h14v10H8l-3 3V6z"/>',
        'check' => '<path d="M5 12l5 5L20 7"/>',
        'arrow' => '<path d="M5 12h14M13 6l6 6-6 6"/>',
        'phone' => '<path d="M7 3h4l1 4-3 2a12 12 0 006 6l2-3 4 1v4c0 1-1 2-2 2C10 19 5 14 5 5c0-1 1-2 2-2z"/>',
        'device' => '<rect x="7" y="3" width="10" height="18" rx="2"/><path d="M11 18h2"/>',
        'mail' => '<path d="M4 6h16v12H4z"/><path d="M4 7l8 6 8-6"/>',
        'pin' => '<path d="M12 21s7-6.2 7-11a7 7 0 10-14 0c0 4.8 7 11 7 11z"/><circle cx="12" cy="10" r="2.2"/>',
        'whatsapp' => '<path d="M12 4.5A7.5 7.5 0 006.2 16.3L5 19.5l3.3-1.1A7.5 7.5 0 1012 4.5z"/><path d="M9.2 9.6c.2-.5.4-.5.7-.5h.6c.2 0 .4.1.5.4l.8 1.8c.1.2 0 .4-.1.6l-.4.5c-.1.1-.1.3 0 .4.3.5.8 1 1.4 1.4.1.1.3.1.4 0l.5-.4c.2-.1.4-.2.6-.1l1.8.8c.3.1.4.3.4.5v.6c0 .3 0 .5-.5.7A4.8 4.8 0 019.2 9.6z"/>',
        'menu' => '<path d="M4 7h16M4 12h16M4 17h16"/>',
        'close' => '<path d="M6 6l12 12M18 6L6 18"/>',
        'linkedin' => '<path d="M6 9h3v9H6zM7.5 6.2a1.6 1.6 0 110-3.2 1.6 1.6 0 010 3.2zM11 9h2.8v1.2h.1c.4-.7 1.4-1.5 2.9-1.5 3 0 3.6 2 3.6 4.6V18h-3v-4.2c0-1 0-2.3-1.4-2.3s-1.6 1.1-1.6 2.2V18H11V9z"/>',
        'instagram' => '<rect x="4" y="4" width="16" height="16" rx="4"/><circle cx="12" cy="12" r="3.5"/><circle cx="17.2" cy="6.8" r="0.8" fill="currentColor" stroke="none"/>',
        'facebook' => '<path d="M14 9h3V6h-3c-2.2 0-4 1.8-4 4v2H8v3h2v7h3v-7h2.2l.8-3H13v-2c0-.6.4-1 1-1z"/>',
        'bag' => '<path d="M6 8h12l-1 12H7L6 8z"/><path d="M9 8V7a3 3 0 016 0v1"/>',
        'briefcase' => '<rect x="4" y="7" width="16" height="12" rx="2"/><path d="M9 7V6a2 2 0 012-2h2a2 2 0 012 2v1M4 13h16"/>',
        'building' => '<path d="M5 20V6l7-3 7 3v14"/><path d="M9 20v-6h6v6"/><path d="M9 9h.01M12 9h.01M15 9h.01M9 13h.01M12 13h.01M15 13h.01"/>',
        'factory' => '<path d="M3 20h18M4 20V10l5 3V10l5 3V8h6v12"/>',
        'heart' => '<path d="M12 19s-7-4.4-7-9.2A4 4 0 0112 7a4 4 0 017 2.8C19 14.6 12 19 12 19z"/>',
        'book' => '<path d="M5 5h10a3 3 0 013 3v11H8a3 3 0 00-3 3V5z"/><path d="M5 19a3 3 0 013-3h13"/>',
        'home' => '<path d="M4 11l8-7 8 7v9H4v-9z"/><path d="M10 20v-6h4v6"/>',
        'rocket' => '<path d="M12 19c-3 0-5-2-5-2s2 0 5-3 5 3 5 3-2 2-5 2z"/><path d="M12 14s5-6 8-9c0 0-1-3-4-3-3 3-9 8-9 8"/><circle cx="15.5" cy="8.5" r="1"/>',
        'users' => '<circle cx="9" cy="8" r="3"/><path d="M3 19c.5-3 2.5-5 6-5s5.5 2 6 5"/><circle cx="17" cy="9" r="2.2"/><path d="M16 19c.3-1.8 1.4-3.2 3.4-4"/>',
        'cart' => '<path d="M4 5h2l2.2 10h9.3l2-7H8"/><circle cx="10" cy="19" r="1.4"/><circle cx="17" cy="19" r="1.4"/>',
        'cpu' => '<rect x="7" y="7" width="10" height="10" rx="1.5"/><path d="M12 3v4M12 17v4M3 12h4M17 12h4M5.5 5.5l2.5 2.5M16 16l2.5 2.5M18.5 5.5L16 8M8 16l-2.5 2.5"/>',
        'megaphone' => '<path d="M4 11v2c2 0 3 .5 5 2V9c-2 1.5-3 2-5 2z"/><path d="M9 9l11-4v14L9 15"/><path d="M6.5 13.5l.8 4.2H9"/>',
        'shield' => '<path d="M12 3l8 3v6c0 5-3.5 8-8 9-4.5-1-8-4-8-9V6l8-3z"/>',
        'clock' => '<circle cx="12" cy="12" r="8"/><path d="M12 8v5l3 2"/>',
    ];
    $path = $icons[$name] ?? $icons['globe'];
@endphp

<svg {{ $attributes->merge(['class' => 'h-5 w-5']) }} viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.6" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
    {!! $path !!}
</svg>
