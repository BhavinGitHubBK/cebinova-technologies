<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
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

    public function toPublicArray(): array
    {
        return [
            'slug' => $this->slug,
            'icon' => $this->icon,
            'category' => $this->category,
            'title' => $this->name,
            'short' => $this->short_description,
            'summary' => $this->full_description,
            'includes' => $this->features ?? [],
            'cta_label' => $this->cta_label,
            'cta_url' => $this->cta_url,
            'featured_image' => $this->featured_image,
            'seo_title' => $this->seo_title,
            'seo_description' => $this->seo_description,
        ];
    }
}
