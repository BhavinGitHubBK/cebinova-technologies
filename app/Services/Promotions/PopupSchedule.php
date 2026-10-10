<?php

namespace App\Services\Promotions;

use App\Models\PromotionCampaign;
use Illuminate\Support\Carbon;

/**
 * Client-side popup frequency uses the same rules. Closing a popup stores a dismissal
 * the browser must honour. The server never extends a discount because a timer is still visible.
 */
final class PopupSchedule
{
    /**
     * @param  array{at?: string, mode?: string}|null  $dismissal
     */
    public static function shouldShow(PromotionCampaign $campaign, ?array $dismissal, ?\DateTimeInterface $now = null): bool
    {
        if (! $dismissal || empty($dismissal['at'])) {
            return true;
        }

        $closedAt = Carbon::parse($dismissal['at']);
        $now = Carbon::parse($now ?? now());
        $mode = $campaign->popup_frequency ?: 'session';

        return match ($mode) {
            'session' => (int) $campaign->popup_repeat_minutes > 0
                && $closedAt->copy()->addMinutes((int) $campaign->popup_repeat_minutes)->lte($now),
            'campaign' => false,
            'cooldown' => $closedAt->copy()->addHours(max(1, (int) $campaign->popup_cooldown_hours))->lte($now),
            default => ! $closedAt->isSameDay($now),
        };
    }
}
