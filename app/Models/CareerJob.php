<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class CareerJob extends Model
{
    use SoftDeletes;

    public const EMPLOYMENT_TYPES = ['full-time' => 'Full-time', 'part-time' => 'Part-time', 'freelance' => 'Freelance', 'internship' => 'Internship', 'contract' => 'Contract'];
    public const WORK_MODES = ['on-site' => 'On-site', 'remote' => 'Remote', 'hybrid' => 'Hybrid'];
    public const STATUSES = ['draft' => 'Draft', 'published' => 'Published', 'closed' => 'Closed', 'archived' => 'Archived'];

    protected $guarded = ['id'];

    protected function casts(): array
    {
        return [
            'published_at' => 'datetime', 'application_deadline' => 'datetime',
            'show_compensation' => 'boolean', 'is_featured' => 'boolean',
            'experience_min' => 'integer', 'experience_max' => 'integer',
            'compensation_min' => 'integer', 'compensation_max' => 'integer', 'sort_order' => 'integer',
        ];
    }

    public function department(): BelongsTo { return $this->belongsTo(CareerDepartment::class, 'career_department_id'); }
    public function applications(): HasMany { return $this->hasMany(CareerApplication::class); }

    public function scopePublished(Builder $query): Builder
    {
        return $query->where('status', 'published')->whereNotNull('published_at')->where('published_at', '<=', now());
    }

    public function acceptsApplications(): bool
    {
        return $this->status === 'published'
            && $this->published_at?->lte(now())
            && (! $this->application_deadline || $this->application_deadline->isFuture());
    }

    public function experienceLabel(): string
    {
        if ($this->experience_min === null && $this->experience_max === null) return 'Open experience level';
        if ($this->experience_max === null) return $this->experience_min.'+ years';
        return $this->experience_min.'–'.$this->experience_max.' years';
    }

    public function compensationLabel(): ?string
    {
        if (! $this->show_compensation || $this->compensation_min === null) return null;
        $range = '₹'.number_format($this->compensation_min);
        if ($this->compensation_max) $range .= ' – ₹'.number_format($this->compensation_max);
        return $range.($this->compensation_period ? ' / '.$this->compensation_period : '');
    }
}
