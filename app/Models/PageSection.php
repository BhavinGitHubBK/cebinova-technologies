<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;

class PageSection extends Model
{
    protected $fillable = [
        'page', 'key', 'heading', 'subheading', 'body', 'image_path',
        'cta_label', 'cta_url', 'secondary_cta_label', 'secondary_cta_url',
        'stats', 'is_visible', 'sort_order',
    ];

    protected function casts(): array
    {
        return [
            'stats' => 'array',
            'is_visible' => 'boolean',
            'sort_order' => 'integer',
        ];
    }

    public function scopeVisible(Builder $query): Builder
    {
        return $query->where('is_visible', true);
    }
}
