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
        return ['consent_at' => 'datetime', 'expected_rate' => 'integer', 'resume_size' => 'integer', 'latitude' => 'decimal:7', 'longitude' => 'decimal:7', 'location_accuracy' => 'integer', 'location_captured_at' => 'datetime'];
    }

    public function job(): BelongsTo { return $this->belongsTo(CareerJob::class, 'career_job_id')->withTrashed(); }
    public function statusHistory(): HasMany { return $this->hasMany(CareerApplicationStatusHistory::class)->latest(); }

    public function detectedPlace(): string
    {
        if ($this->location_source === 'local_network') return 'Local network';
        $parts = array_filter([$this->location_city, $this->location_region, $this->location_country]);
        return $parts ? implode(', ', $parts) : ($this->location_captured_at ? 'Location unavailable' : 'Not captured');
    }

    public function mapUrl(): ?string
    {
        return $this->latitude !== null && $this->longitude !== null ? 'https://www.google.com/maps?q='.$this->latitude.','.$this->longitude : null;
    }

    public function accuracyLabel(): ?string
    {
        if ($this->location_accuracy === null) return null;
        return $this->location_accuracy < 1000 ? 'Within '.$this->location_accuracy.' metres' : 'Within '.rtrim(rtrim(number_format($this->location_accuracy / 1000, 1), '0'), '.').' km';
    }
}
