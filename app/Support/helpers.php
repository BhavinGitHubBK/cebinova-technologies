<?php

use App\Support\MarketingPackages;

if (! function_exists('whatsapp_url')) {
    function whatsapp_url(?string $message = null): string
    {
        $number = preg_replace('/\D+/', '', (string) config('cebinova.contact.whatsapp'));
        $text = urlencode($message ?: (string) config('cebinova.contact.whatsapp_message'));

        if ($number !== '') {
            return "https://wa.me/{$number}?text={$text}";
        }

        return route('contact', ['via' => 'whatsapp', 'source' => 'WhatsApp']);
    }
}

if (! function_exists('package_whatsapp_message')) {
    function package_whatsapp_message(string $package, string $duration): string
    {
        $template = (string) config('cebinova.contact.whatsapp_package_message');

        return strtr($template, [
            '{package}' => $package,
            '{duration}' => $duration,
        ]);
    }
}

if (! function_exists('package_whatsapp_url')) {
    function package_whatsapp_url(string $package, string $duration): string
    {
        return whatsapp_url(package_whatsapp_message($package, $duration));
    }
}

if (! function_exists('consultation_url')) {
    function consultation_url(?string $service = null): string
    {
        return route('contact', array_filter([
            'service' => $service,
            'source' => 'Free Consultation',
        ]));
    }
}

if (! function_exists('page_next_url')) {
    function page_next_url(?array $guide, ?string $fallbackService = null): string
    {
        $route = $guide['next_route'] ?? null;

        if (! is_string($route) || $route === '') {
            return consultation_url($fallbackService);
        }

        if (! empty($guide['next_param'])) {
            return route($route, $guide['next_param']);
        }

        return route($route);
    }
}

if (! function_exists('page_whatsapp_url')) {
    function page_whatsapp_url(?string $topic = null): string
    {
        if (! $topic) {
            return whatsapp_url();
        }

        return whatsapp_url('Hi CEBINOVA, I want to know about '.$topic.'.');
    }
}

if (! function_exists('technology_enquiry_url')) {
    function technology_enquiry_url(string $service): string
    {
        return route('contact', [
            'service' => $service,
            'source' => 'Technology Solution',
        ]);
    }
}

if (! function_exists('cebinova_phone')) {
    function cebinova_phone(): ?string
    {
        $phone = trim((string) config('cebinova.contact.phone'));

        return $phone !== '' ? $phone : null;
    }
}

if (! function_exists('cebinova_inr')) {
    function cebinova_inr(int|string $amount): string
    {
        return '₹'.number_format((int) $amount, 0, '.', ',');
    }
}

if (! function_exists('package_price_amount')) {
    function package_price_amount(?string $category, ?string $duration): ?int
    {
        return MarketingPackages::priceAmount($category, $duration);
    }
}

if (! function_exists('package_enquiry_url')) {
    function package_enquiry_url(string $category, string $duration, array $extra = []): string
    {
        return route('contact', array_merge([
            'service' => MarketingPackages::SERVICE,
            'package_category' => $category,
            'plan_duration' => $duration,
            'source' => $category,
        ], $extra));
    }
}

if (! function_exists('solution_enquiry_url')) {
    function solution_enquiry_url(array $context = []): string
    {
        $solution = $context['solution'] ?? null;
        $plan = $context['plan'] ?? null;
        $price = $context['price'] ?? null;

        $lines = array_values(array_filter([
            $solution ? 'Solution: '.$solution : null,
            $plan ? 'Plan: '.$plan : null,
            $price ? 'Indicative price: '.$price : null,
            $context['message'] ?? null,
        ]));

        return route('contact', array_filter([
            'service' => $context['service'] ?? 'Business Solution',
            'business_type' => $context['business_type'] ?? null,
            'source' => $context['source'] ?? 'CEBINOVA Solutions',
            'solution' => $solution,
            'plan' => $plan,
            'price' => $price,
            'message' => $lines ? implode("\n", $lines) : null,
        ]));
    }
}

if (! function_exists('solution_page_url')) {
    function solution_page_url(array $item): string
    {
        if (($item['slug'] ?? '') === 'kirana') {
            return route('pricing');
        }

        return route('solutions.show', $item['slug']);
    }
}

if (! function_exists('demo_url')) {
    function demo_url(string $slug): string
    {
        return url('/demos/'.$slug);
    }
}

if (! function_exists('nav_item_active')) {
    function nav_item_active(array $item): bool
    {
        $route = $item['route'] ?? '';

        if ($route === 'home') {
            return request()->routeIs('home');
        }

        if (($item['type'] ?? null) === 'mega' || $route === 'solutions') {
            return request()->routeIs('solutions')
                || request()->routeIs('solutions.*')
                || request()->routeIs('industries');
        }

        if ($route === 'services.index') {
            return request()->routeIs('services.*');
        }

        return request()->routeIs($route) || request()->routeIs($route.'.*');
    }
}

if (! function_exists('nav_solution_href')) {
    function nav_solution_href(array $item): string
    {
        if (($item['type'] ?? 'solution') === 'anchor') {
            return route('solutions').'#'.$item['slug'];
        }

        return route('solutions.show', $item['slug']);
    }
}

if (! function_exists('is_active_route')) {
    function is_active_route(string $route, bool $startsWith = false): bool
    {
        if ($startsWith) {
            return request()->routeIs($route) || request()->routeIs($route.'.*');
        }

        return request()->routeIs($route);
    }
}
