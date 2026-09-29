<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Solution extends Model
{
    use SoftDeletes;

    protected $fillable = ['name', 'slug', 'category', 'icon', 'badge', 'short_description', 'description', 'hero_title', 'hero_subtitle', 'audience', 'outcome', 'image', 'demo_slug', 'business_type', 'cta_label', 'next_route', 'next_param', 'is_featured', 'is_active', 'sort_order', 'seo_title', 'seo_description'];

    protected function casts(): array
    {
        return ['is_featured' => 'boolean', 'is_active' => 'boolean', 'sort_order' => 'integer'];
    }

    public function sections(): HasMany { return $this->hasMany(SolutionSection::class)->orderBy('sort_order')->orderBy('id'); }
    public function faqs(): HasMany { return $this->hasMany(Faq::class)->orderBy('sort_order')->orderBy('id'); }
    public function services(): BelongsToMany { return $this->belongsToMany(Service::class)->withPivot('sort_order')->orderByPivot('sort_order'); }
    public function industries(): BelongsToMany { return $this->belongsToMany(Industry::class)->withPivot('sort_order')->orderByPivot('sort_order'); }
    public function leads(): HasMany { return $this->hasMany(Lead::class); }
    public function scopeActive(Builder $query): Builder { return $query->where('is_active', true); }

    public function toPublicArray(): array
    {
        $features = $this->sections->firstWhere('type', 'features');
        return [
            'id' => $this->id, 'slug' => $this->slug, 'title' => $this->name, 'heading' => $this->hero_title ?: $this->name,
            'industry' => $this->category, 'icon' => $this->icon, 'label' => $this->badge, 'summary' => $this->hero_subtitle ?: $this->description,
            'capabilities' => $features?->items->pluck('title')->values()->all() ?? [], 'demo' => $this->demo_slug, 'preview' => $this->image,
            'business_type' => $this->business_type, 'audience' => $this->audience, 'outcome' => $this->outcome,
            'next_label' => $this->cta_label, 'next_route' => $this->next_route, 'next_param' => $this->next_param,
            'seo_title' => $this->seo_title, 'seo_description' => $this->seo_description,
            'sections' => $this->sections->map(fn ($section) => ['type' => $section->type, 'title' => $section->title, 'subtitle' => $section->subtitle, 'content' => $section->content, 'items' => $section->items->map(fn ($item) => ['title' => $item->title, 'description' => $item->description, 'value' => $item->value])->all()])->all(),
            'faq' => $this->faqs->map(fn ($faq) => ['q' => $faq->question, 'a' => $faq->answer])->all(),
            'services' => $this->services->map(fn ($service) => $service->toPublicArray())->all(),
            'industries' => $this->industries->map(fn ($industry) => $industry->toPublicArray(false))->all(),
        ];
    }
}
