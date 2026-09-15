<?php

namespace App\Http\Controllers;

use Illuminate\Http\Response;
use Symfony\Component\HttpFoundation\BinaryFileResponse;

class DemoSiteController extends Controller
{
    private const DEMOS = [
        'kirana' => 'kirana',
        'professional-services' => 'professional-services',
        'jewellery-retail' => 'jewellery-retail',
    ];

    public function show(string $demo, ?string $path = null): BinaryFileResponse|Response
    {
        abort_unless(isset(self::DEMOS[$demo]), 404);

        if ($path === null || $path === '') {
            $relative = 'index.html';
        } else {
            $relative = $this->normalizePath($path);
        }
        $root = realpath(public_path('assets/demos/'.self::DEMOS[$demo]));
        abort_unless($root, 404);

        $absolute = realpath($root.DIRECTORY_SEPARATOR.str_replace('/', DIRECTORY_SEPARATOR, $relative));

        if ($absolute === false && is_dir($root.DIRECTORY_SEPARATOR.str_replace('/', DIRECTORY_SEPARATOR, $relative))) {
            $absolute = realpath($root.DIRECTORY_SEPARATOR.str_replace('/', DIRECTORY_SEPARATOR, $relative).DIRECTORY_SEPARATOR.'index.html');
        }

        abort_unless($absolute && str_starts_with($absolute, $root), 404);
        abort_unless(is_file($absolute), 404);

        $mime = $this->mimeType($absolute);

        if (str_contains($mime, 'html')) {
            $html = $this->brandHtml((string) file_get_contents($absolute), $demo);

            return response($html, 200)->header('Content-Type', 'text/html; charset=UTF-8');
        }

        return response()->file($absolute, [
            'Content-Type' => $mime,
        ]);
    }

    private function normalizePath(?string $path): string
    {
        $path = trim((string) $path, '/');

        if ($path === '' || $path === '.') {
            return 'index.html';
        }

        if (str_contains($path, '..')) {
            abort(404);
        }

        if (! pathinfo($path, PATHINFO_EXTENSION)) {
            return rtrim($path, '/').'/index.html';
        }

        return $path;
    }

    private function mimeType(string $path): string
    {
        $ext = strtolower((string) pathinfo($path, PATHINFO_EXTENSION));

        return match ($ext) {
            'html', 'htm' => 'text/html; charset=UTF-8',
            'css' => 'text/css; charset=UTF-8',
            'js' => 'application/javascript; charset=UTF-8',
            'json' => 'application/json',
            'svg' => 'image/svg+xml',
            'png' => 'image/png',
            'jpg', 'jpeg' => 'image/jpeg',
            'gif' => 'image/gif',
            'webp' => 'image/webp',
            'woff' => 'font/woff',
            'woff2' => 'font/woff2',
            'ttf' => 'font/ttf',
            'ico' => 'image/x-icon',
            'map' => 'application/json',
            default => 'application/octet-stream',
        };
    }

    private function brandHtml(string $html, string $demo): string
    {
        $html = str_ireplace(['BizPilot', 'Biz Pilot'], 'SARVIX', $html);
        $html = str_ireplace('bizpilot', 'sarvix', $html);
        $html = preg_replace('#file:///[^\s"\']+#i', '#', $html) ?? $html;
        $html = preg_replace('#C:\\\\xampp\\\\[^\s"\']+#i', '#', $html) ?? $html;

        $titles = [
            'kirana' => 'Kirana & Grocery',
            'professional-services' => 'Professional Services',
            'jewellery-retail' => 'Retail & Jewellery',
        ];

        $enquiry = e(solution_enquiry_url([
            'solution' => $titles[$demo] ?? 'Business Solution',
            'source' => 'SARVIX Demos',
        ]));
        $demos = e(route('demos'));
        $home = e(route('home'));
        $base = e(url('/demos/'.$demo).'/');

        $bar = <<<HTML
<base href="{$base}">
<div class="sarvix-demo-bar">
  <a class="sarvix-demo-bar__brand" href="{$home}">SARVIX Technologies</a>
  <span class="sarvix-demo-bar__label">Live solution demo</span>
  <span class="sarvix-demo-bar__actions">
    <a href="{$demos}">All demos</a>
    <a href="{$enquiry}">Request this solution</a>
  </span>
</div>
<style>
.sarvix-demo-bar{position:sticky;top:0;z-index:9999;display:flex;flex-wrap:wrap;align-items:center;gap:.75rem 1rem;padding:.7rem 1rem;background:#0B1F3A;color:#fff;font-family:Outfit,sans-serif;font-size:13px;line-height:1.4}
.sarvix-demo-bar__brand{font-weight:700;letter-spacing:.08em;text-transform:uppercase;color:#C9A227;text-decoration:none}
.sarvix-demo-bar__label{color:rgba(255,255,255,.7)}
.sarvix-demo-bar__actions{margin-left:auto;display:flex;gap:1rem}
.sarvix-demo-bar a{color:#fff;text-decoration:none;font-weight:600}
.sarvix-demo-bar a:hover{color:#C9A227}
@media (max-width:720px){.sarvix-demo-bar{font-size:12px}.sarvix-demo-bar__actions{margin-left:0;width:100%}}
</style>
HTML;

        $inject = $bar;
        if (preg_match('/<head[^>]*>/i', $html)) {
            $html = preg_replace('/<head[^>]*>/i', '$0<base href="'.$base.'">', $html, 1) ?? $html;
            $inject = preg_replace('/^<base[^>]*>\s*/', '', $bar) ?? $bar;
        }

        if (preg_match('/<body[^>]*>/i', $html)) {
            return preg_replace('/<body[^>]*>/i', '$0'.$inject, $html, 1) ?? ($inject.$html);
        }

        return $inject.$html;
    }
}
