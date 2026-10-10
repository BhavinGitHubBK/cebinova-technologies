<?php

use App\Support\MarketingPackages;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Route;

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

if (! function_exists('service_enquiry_url')) {
    function service_enquiry_url(array $service): string
    {
        return route('contact', array_filter([
            'service' => $service['title'],
            'service_id' => $service['id'] ?? null,
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
        return "\u{20B9}".number_format((int) $amount, 0, '.', ',');
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

        if ($route === 'offers.index') {
            return request()->routeIs('offers.*');
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

if (! function_exists('admin_site_link')) {
    function admin_site_link(): Closure
    {
        return function (string $attribute, mixed $value, Closure $fail): void {
            if (! is_string($value) || trim($value) === '') {
                return;
            }

            $link = trim($value);
            if (preg_match('~^(https?://|mailto:|tel:)~i', $link) && filter_var($link, FILTER_VALIDATE_URL)) {
                return;
            }
            if (preg_match('~^/[\w./?&=%+#\-]*$~', $link) || preg_match('/^#[\w\-]+$/', $link)) {
                return;
            }

            $fail('Enter a site path such as /contact, or a full link starting with http:// or https://.');
        };
    }
}

if (! function_exists('admin_rows')) {
    function admin_rows(?string $value, int $min = 2): int
    {
        $text = trim((string) $value);
        if ($text === '') {
            return $min;
        }

        $lines = 0;
        foreach (preg_split("/\r\n|\r|\n/", $text) ?: [] as $line) {
            $lines += max(1, (int) ceil(max(1, mb_strlen($line)) / 48));
        }

        return max($min, min($lines, 80));
    }
}

if (! function_exists('admin_sort')) {
    /**
     * @param  list<string>  $allowed
     * @return array{0: string, 1: string}
     */
    function admin_sort(Request $request, array $allowed, string $default, string $defaultDir = 'asc'): array
    {
        $sort = in_array($request->query('sort'), $allowed, true) ? (string) $request->query('sort') : $default;
        $dir = $request->query('dir');

        return [$sort, $dir === 'asc' || $dir === 'desc' ? $dir : $defaultDir];
    }
}

if (! function_exists('admin_sort_rows')) {
    function admin_sort_rows(array $rows, int $column, string $dir): array
    {
        usort($rows, function ($left, $right) use ($column, $dir) {
            $pick = function ($row) use ($column) {
                $value = $row[$column] ?? '';

                return is_array($value) ? (string) ($value['text'] ?? '') : (string) $value;
            };
            $compared = strnatcasecmp($pick($left), $pick($right));

            return $dir === 'desc' ? -$compared : $compared;
        });

        return $rows;
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

if (! function_exists('admin_index_url')) {
    function admin_index_url(): ?string
    {
        $name = request()->route()?->getName();
        if (! is_string($name) || ! preg_match('/^(admin\..+)\.(edit|create|show|update)$/', $name, $match)) {
            return null;
        }

        $index = $match[1].'.index';
        if (! Route::has($index)) {
            return null;
        }

        $params = [];
        $package = request()->route('package');
        if (request('group') === 'marketing' || (is_object($package) && method_exists($package, 'isMarketing') && $package->isMarketing())) {
            $params['group'] = 'marketing';
        }

        return route($index, $params);
    }
}

if (! function_exists('page_label')) {
    function page_label(?string $path): string
    {
        $path = trim((string) $path);
        if ($path === '') {
            return '—';
        }

        $path = '/'.ltrim((string) (parse_url($path, PHP_URL_PATH) ?: $path), '/');
        $path = $path === '/' ? '/' : rtrim($path, '/');

        $known = [
            '/' => 'Home',
            '/about' => 'About',
            '/services' => 'Services',
            '/solutions' => 'Solutions',
            '/industries' => 'Industries',
            '/pricing' => 'Pricing',
            '/contact' => 'Contact',
            '/demos' => 'Demos',
            '/marketing-packages' => 'Marketing packages',
            '/privacy-policy' => 'Privacy policy',
            '/terms' => 'Terms',
        ];

        if (isset($known[$path])) {
            return $known[$path];
        }

        return ucwords(str_replace(['-', '_'], ' ', basename($path)));
    }
}

if (! function_exists('ist')) {
    function ist(?DateTimeInterface $time, string $format = 'd M, h:i A'): string
    {
        if ($time === null) {
            return '—';
        }

        return Carbon::parse($time)->timezone('Asia/Kolkata')->format($format).' IST';
    }
}
