<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class SolutionSection extends Model
{
    public const TYPES = ['features', 'modules', 'benefits', 'process', 'business_problem', 'overview', 'custom'];
    protected $fillable = ['solution_id', 'type', 'title', 'subtitle', 'content', 'is_active', 'sort_order'];
    protected function casts(): array { return ['is_active' => 'boolean', 'sort_order' => 'integer']; }
    public function solution(): BelongsTo { return $this->belongsTo(Solution::class); }
    public function items(): HasMany { return $this->hasMany(SolutionSectionItem::class)->orderBy('sort_order')->orderBy('id'); }
    public function scopeActive(Builder $query): Builder { return $query->where('is_active', true); }
}
