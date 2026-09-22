<?php

namespace App\Support;

use App\Models\Faq;
use App\Models\PageSection;
use App\Models\Project;
use App\Models\TeamMember;
use App\Models\Testimonial;
use Illuminate\Support\Facades\Schema;

class CmsContent
{
    public static function portfolio(): array
    {
        if (self::has('projects') && Project::query()->published()->exists()) {
            return Project::query()->published()->orderBy('sort_order')->get()
                ->map(fn (Project $p) => $p->toPublicArray())->all();
        }

        return config('cebinova.portfolio', []);
    }

    public static function faqs(string $page = 'general'): array
    {
        if (self::has('faqs') && Faq::query()->active()->forPage($page)->exists()) {
            return Faq::query()->active()->forPage($page)->orderBy('sort_order')->get()
                ->map(fn (Faq $f) => ['q' => $f->question, 'a' => $f->answer])->all();
        }

        if ($page === 'general' || $page === 'page') {
            return config('cebinova.page.faq', []);
        }

        return config('cebinova.page.faq', []);
    }

    public static function testimonials(): array
    {
        if (! self::has('testimonials')) {
            return [];
        }

        return Testimonial::query()->active()->orderBy('sort_order')->get()->all();
    }

    public static function team(): array
    {
        if (! self::has('team_members')) {
            return [];
        }

        return TeamMember::query()->active()->orderBy('sort_order')->get()->all();
    }

    public static function section(string $page, string $key): ?PageSection
    {
        if (! self::has('page_sections')) {
            return null;
        }

        return PageSection::query()->visible()->where('page', $page)->where('key', $key)->first();
    }

    private static function has(string $table): bool
    {
        try {
            return Schema::hasTable($table);
        } catch (\Throwable) {
            return false;
        }
    }
}
