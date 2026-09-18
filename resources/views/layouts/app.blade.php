<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    @php
        $pageTitle = trim($__env->yieldContent('title')) ?: config('cebinova.seo.default_title');
        $pageDescription = trim($__env->yieldContent('description')) ?: config('cebinova.seo.default_description');
        $canonical = trim($__env->yieldContent('canonical')) ?: url()->current();
        $ogImage = asset('images/branding/logo-original.png');
    @endphp
    <title>{{ $pageTitle }}</title>
    <meta name="description" content="{{ $pageDescription }}">
    <link rel="canonical" href="{{ $canonical }}">
    <meta property="og:type" content="website">
    <meta property="og:site_name" content="CEBINOVA Technologies">
    <meta property="og:title" content="{{ $pageTitle }}">
    <meta property="og:description" content="{{ $pageDescription }}">
    <meta property="og:url" content="{{ $canonical }}">
    <meta property="og:image" content="{{ $ogImage }}">
    <meta name="twitter:card" content="summary_large_image">
    <meta name="twitter:title" content="{{ $pageTitle }}">
    <meta name="twitter:description" content="{{ $pageDescription }}">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <link rel="icon" type="image/png" sizes="64x64" href="{{ asset('favicon.png') }}?v=cebinova">
    <link rel="icon" type="image/svg+xml" href="{{ asset('favicon.svg') }}?v=cebinova">
    <link rel="shortcut icon" href="{{ asset('favicon.ico') }}?v=cebinova">
    <link rel="apple-touch-icon" href="{{ asset('apple-touch-icon.png') }}?v=cebinova">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Outfit:wght@400;500;600;700;800&family=Plus+Jakarta+Sans:wght@500;600;700;800&display=swap" rel="stylesheet">
        @vite(['resources/css/app.css', 'resources/js/app.js'])
        @stack('head')
    <script type="application/ld+json">
        {!! json_encode([
            '@context' => 'https://schema.org',
            '@type' => 'Organization',
            'name' => 'CEBINOVA Technologies',
            'slogan' => 'Technology for Every Business.',
            'url' => url('/'),
            'logo' => asset('images/branding/logo.png'),
            'email' => config('cebinova.contact.email'),
            'address' => [
                '@type' => 'PostalAddress',
                'addressLocality' => 'Ahmedabad',
                'addressRegion' => 'Gujarat',
                'addressCountry' => 'IN',
            ],
        ], JSON_UNESCAPED_SLASHES|JSON_UNESCAPED_UNICODE) !!}
    </script>
</head>
<body class="bg-paper text-ink">
    <a href="#main" class="sr-only focus:not-sr-only focus:absolute focus:left-4 focus:top-4 focus:z-50 focus:rounded-md focus:bg-gold focus:px-4 focus:py-2 focus:text-navy-deep">Skip to content</a>
    <x-navbar />
    <main id="main">
        @yield('content')
    </main>
    <x-footer />
    <x-whatsapp-float />
    @stack('scripts')
</body>
</html>
