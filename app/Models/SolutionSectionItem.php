<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class SolutionSectionItem extends Model
{
    protected $fillable = ['solution_section_id', 'title', 'description', 'icon', 'value', 'is_active', 'sort_order'];
    protected function casts(): array { return ['is_active' => 'boolean', 'sort_order' => 'integer']; }
    public function section(): BelongsTo { return $this->belongsTo(SolutionSection::class, 'solution_section_id'); }
    public function scopeActive(Builder $query): Builder { return $query->where('is_active', true); }
}
