<?php

namespace App\Models;

use App\Enums\PromotionTargetType;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PromotionTarget extends Model
{
    protected $fillable = ['promotion_id', 'mode', 'target_type', 'target_id', 'target_key'];

    protected function casts(): array
    {
        return [
            'target_type' => PromotionTargetType::class,
            'target_id' => 'integer',
        ];
    }

    public function campaign(): BelongsTo
    {
        return $this->belongsTo(PromotionCampaign::class, 'promotion_id');
    }
}
