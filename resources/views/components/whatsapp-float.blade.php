@php
    $waHref = whatsapp_url();
    $isWhatsApp = str_starts_with($waHref, 'https://wa.me');
    $logoVer = (int) @filemtime(public_path('assets/brand/cebinova-c-icon.png'));
    $logoPng = asset('assets/brand/cebinova-c-icon.png').($logoVer ? '?v='.$logoVer : '');
@endphp

<div
    id="floatWhatsapp"
    class="wa-widget"
    data-wa-widget
>
    <div
        class="wa-widget-panel"
        id="waWidgetPanel"
        role="dialog"
        aria-modal="false"
        aria-labelledby="waWidgetTitle"
        hidden
    >
        <button type="button" class="wa-widget-close" data-wa-close aria-label="Close WhatsApp chat">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M6 6l12 12M18 6L6 18"/></svg>
        </button>

        <div class="wa-widget-head">
            <span class="wa-widget-brand" aria-hidden="true">
                <img src="{{ $logoPng }}" alt="" width="48" height="40" decoding="async">
            </span>
            <div class="wa-widget-copy">
                <p id="waWidgetTitle" class="wa-widget-title">Talk to CEBINOVA</p>
                <p class="wa-widget-kicker">Technology team on WhatsApp</p>
            </div>
        </div>

        <p class="wa-widget-text">
            Share what your business needs - our team typically replies within a few hours during working hours.
        </p>

        <a
            href="{{ $waHref }}"
            class="wa-widget-cta"
            data-wa-start
            @if ($isWhatsApp) target="_blank" rel="noopener noreferrer" @endif
        >
            <svg class="wa-widget-cta-mark" viewBox="0 0 24 24" fill="currentColor" aria-hidden="true" focusable="false">
                <path d="M12.04 2C6.58 2 2.15 6.4 2.15 11.84c0 1.99.58 3.84 1.58 5.42L2 22l4.9-1.68a9.9 9.9 0 0 0 5.14 1.42h.01c5.46 0 9.89-4.4 9.89-9.84C21.94 6.4 17.5 2 12.04 2zm5.77 13.99c-.24.68-1.4 1.25-1.93 1.33-.5.07-1.13.1-1.82-.11-.42-.13-.96-.31-1.65-.61-2.9-1.26-4.79-4.19-4.93-4.38-.14-.19-1.15-1.53-1.15-2.92 0-1.39.73-2.07.99-2.36.26-.29.57-.36.76-.36h.56c.18 0 .42-.07.66.5.24.58.82 2 .89 2.15.07.15.12.32.02.52-.1.19-.14.32-.29.49-.14.17-.3.38-.43.51-.14.14-.29.29-.12.56.16.28.73 1.2 1.56 1.95 1.08.96 1.98 1.26 2.26 1.4.28.14.44.12.61-.07.16-.19.7-.81.89-1.09.19-.28.38-.23.64-.14.26.1 1.66.78 1.95.92.28.14.47.21.54.33.07.12.07.68-.17 1.36z"/>
            </svg>
            Start Conversation
        </a>

        <div class="wa-widget-foot">
            <p class="wa-widget-hours-label">Working hours</p>
            <p class="wa-widget-hours">Monday - Saturday: 10:00 AM - 7:00 PM</p>
        </div>
    </div>

    <button
        type="button"
        class="wa-widget-launch"
        data-wa-toggle
        aria-expanded="false"
        aria-controls="waWidgetPanel"
        aria-label="Open WhatsApp chat"
    >
        <span class="wa-widget-launch-ring" aria-hidden="true"></span>
        <span class="wa-widget-launch-icon" aria-hidden="true">
            <svg class="wa-widget-launch-mark" viewBox="0 0 24 24" fill="currentColor" focusable="false">
                <path d="M12.04 2C6.58 2 2.15 6.4 2.15 11.84c0 1.99.58 3.84 1.58 5.42L2 22l4.9-1.68a9.9 9.9 0 0 0 5.14 1.42h.01c5.46 0 9.89-4.4 9.89-9.84C21.94 6.4 17.5 2 12.04 2zm5.77 13.99c-.24.68-1.4 1.25-1.93 1.33-.5.07-1.13.1-1.82-.11-.42-.13-.96-.31-1.65-.61-2.9-1.26-4.79-4.19-4.93-4.38-.14-.19-1.15-1.53-1.15-2.92 0-1.39.73-2.07.99-2.36.26-.29.57-.36.76-.36h.56c.18 0 .42-.07.66.5.24.58.82 2 .89 2.15.07.15.12.32.02.52-.1.19-.14.32-.29.49-.14.17-.3.38-.43.51-.14.14-.29.29-.12.56.16.28.73 1.2 1.56 1.95 1.08.96 1.98 1.26 2.26 1.4.28.14.44.12.61-.07.16-.19.7-.81.89-1.09.19-.28.38-.23.64-.14.26.1 1.66.78 1.95.92.28.14.47.21.54.33.07.12.07.68-.17 1.36z"/>
            </svg>
        </span>
    </button>
</div>
