<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class CareerApplicationStatusHistory extends Model
{
    protected $guarded = ['id'];
    public function application(): BelongsTo { return $this->belongsTo(CareerApplication::class, 'career_application_id'); }
    public function user(): BelongsTo { return $this->belongsTo(User::class, 'changed_by'); }
}
