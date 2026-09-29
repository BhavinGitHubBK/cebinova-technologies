<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Industry extends Model
{
    use SoftDeletes;
    protected $fillable = ['name', 'slug', 'group', 'icon', 'short_description', 'description', 'hero_title', 'hero_subtitle', 'image', 'is_featured', 'is_active', 'sort_order', 'seo_title', 'seo_description'];
    protected function casts(): array { return ['is_featured' => 'boolean', 'is_active' => 'boolean', 'sort_order' => 'integer']; }
    public function solutions(): BelongsToMany { return $this->belongsToMany(Solution::class)->withPivot('sort_order')->orderByPivot('sort_order'); }
    public function leads(): HasMany { return $this->hasMany(Lead::class); }
    public function scopeActive(Builder $query): Builder { return $query->where('is_active', true); }
    public function toPublicArray(bool $includeSolutions = true): array
    {
        return ['id' => $this->id, 'slug' => $this->slug, 'title' => $this->name, 'group' => $this->group, 'icon' => $this->icon, 'need' => $this->short_description, 'description' => $this->description, 'featured' => $this->is_featured, 'solutions' => $includeSolutions && $this->relationLoaded('solutions') ? $this->solutions->map(fn ($solution) => ['id' => $solution->id, 'slug' => $solution->slug, 'title' => $solution->name])->all() : []];
    }
}
