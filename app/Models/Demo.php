<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Demo extends Model
{
    use SoftDeletes;

    protected $guarded = ['id'];

    protected function casts(): array
    {
        return ['features' => 'array', 'technologies' => 'array', 'screenshots' => 'array', 'is_active' => 'boolean', 'is_featured' => 'boolean'];
    }

    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    public function services()
    {
        return $this->belongsToMany(Service::class, 'demo_service');
    }

    public function solutions()
    {
        return $this->belongsToMany(Solution::class, 'demo_solution');
    }

    public function industries()
    {
        return $this->belongsToMany(Industry::class, 'demo_industry');
    }

    public function leads()
    {
        return $this->hasMany(Lead::class);
    }

    public function liveLink(): ?string
    {
        return $this->local_demo ? url('/demos/'.$this->local_demo) : $this->live_url;
    }

    public function enquiryUrl(): string
    {
        return route('contact', ['demo_id' => $this->id, 'service' => 'Business Solution']);
    }
}
