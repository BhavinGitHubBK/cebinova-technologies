<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Service extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'name',
        'slug',
        'category',
        'icon',
        'short_description',
        'full_description',
        'featured_image',
        'hero_title',
        'hero_subtitle',
        'badge',
        'audience',
        'outcome',
        'next_label',
        'next_route',
        'next_param',
        'features',
        'cta_label',
        'cta_url',
        'sort_order',
        'is_featured',
        'is_active',
        'seo_title',
        'seo_description',
    ];

    protected function casts(): array
    {
        return [
            'features' => 'array',
            'is_featured' => 'boolean',
            'is_active' => 'boolean',
            'sort_order' => 'integer',
        ];
    }

    public function scopeActive(Builder $query): Builder
    {
        return $query->where('is_active', true);
    }

    public function sections(): HasMany
    {
        return $this->hasMany(ServiceSection::class)->orderBy('sort_order')->orderBy('id');
    }

    public function faqs(): HasMany
    {
        return $this->hasMany(Faq::class)->orderBy('sort_order')->orderBy('id');
    }

    public function leads(): HasMany
    {
        return $this->hasMany(Lead::class);
    }

    public function solutions(): BelongsToMany
    {
        return $this->belongsToMany(Solution::class)->withPivot('sort_order')->orderByPivot('sort_order');
    }

    public function toPublicArray(): array
    {
        return [
            'slug' => $this->slug,
            'icon' => $this->icon,
            'category' => $this->category,
            'title' => $this->name,
            'short' => $this->short_description,
            'summary' => $this->full_description,
            'hero_title' => $this->hero_title ?: $this->name,
            'hero_subtitle' => $this->hero_subtitle ?: $this->full_description,
            'badge' => $this->badge,
            'audience' => $this->audience,
            'outcome' => $this->outcome,
            'next_label' => $this->next_label,
            'next_route' => $this->next_route,
            'next_param' => $this->next_param,
            'includes' => $this->relationLoaded('sections')
                ? $this->sections->firstWhere('type', 'features')?->items->where('is_active', true)->pluck('title')->values()->all() ?? []
                : ($this->features ?? []),
            'sections' => $this->relationLoaded('sections') ? $this->sections->map(fn (ServiceSection $section) => [
                'type' => $section->type,
                'title' => $section->title,
                'subtitle' => $section->subtitle,
                'content' => $section->content,
                'items' => $section->items->where('is_active', true)->map(fn (ServiceSectionItem $item) => [
                    'title' => $item->title,
                    'description' => $item->description,
                    'icon' => $item->icon,
                    'value' => $item->value,
                    'link' => $item->link,
                ])->values()->all(),
            ])->values()->all() : [],
            'faq' => $this->relationLoaded('faqs') ? $this->faqs->where('is_active', true)->map(fn (Faq $faq) => ['q' => $faq->question, 'a' => $faq->answer])->values()->all() : [],
            'id' => $this->id,
            'cta_label' => $this->cta_label,
            'cta_url' => $this->cta_url,
            'featured_image' => $this->featured_image,
            'seo_title' => $this->seo_title,
            'seo_description' => $this->seo_description,
        ];
    }
}
