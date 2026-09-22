<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Project extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'title', 'slug', 'client_name', 'category', 'preview', 'type', 'technologies',
        'short_description', 'full_case_study', 'thumbnail', 'gallery', 'project_url',
        'completed_at', 'is_featured', 'sort_order', 'status', 'seo_title', 'seo_description',
    ];

    protected function casts(): array
    {
        return [
            'technologies' => 'array',
            'gallery' => 'array',
            'is_featured' => 'boolean',
            'completed_at' => 'date',
            'sort_order' => 'integer',
        ];
    }

    public function scopePublished(Builder $query): Builder
    {
        return $query->where('status', 'published');
    }

    public function toPublicArray(): array
    {
        return [
            'slug' => $this->slug,
            'preview' => $this->preview ?: 'website',
            'title' => $this->title,
            'type' => $this->type ?: 'Project',
            'summary' => $this->short_description,
            'tags' => $this->technologies ?? [],
        ];
    }
}
