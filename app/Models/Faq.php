<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Faq extends Model
{
    protected $fillable = ['service_id', 'solution_id', 'page', 'question', 'answer', 'is_active', 'sort_order'];

    protected function casts(): array
    {
        return [
            'is_active' => 'boolean',
            'sort_order' => 'integer',
        ];
    }

    public function scopeActive(Builder $query): Builder
    {
        return $query->where('is_active', true);
    }

    public function scopeForPage(Builder $query, string $page): Builder
    {
        return $query->where('page', $page);
    }

    public function service(): BelongsTo
    {
        return $this->belongsTo(Service::class);
    }

    public function solution(): BelongsTo
    {
        return $this->belongsTo(Solution::class);
    }
}
