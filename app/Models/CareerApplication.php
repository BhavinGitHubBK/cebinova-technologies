<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class CareerApplication extends Model
{
    public const STATUSES = [
        'new' => 'New', 'reviewing' => 'Reviewing', 'shortlisted' => 'Shortlisted',
        'interview_scheduled' => 'Interview Scheduled', 'selected' => 'Selected',
        'rejected' => 'Rejected', 'withdrawn' => 'Withdrawn',
    ];

    protected $guarded = ['id'];

    protected function casts(): array
    {
        return ['consent_at' => 'datetime', 'expected_rate' => 'integer', 'resume_size' => 'integer'];
    }

    public function job(): BelongsTo { return $this->belongsTo(CareerJob::class, 'career_job_id')->withTrashed(); }
    public function statusHistory(): HasMany { return $this->hasMany(CareerApplicationStatusHistory::class)->latest(); }
}
