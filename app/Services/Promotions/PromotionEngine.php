<?php

namespace App\Services\Promotions;

use App\Enums\PromotionCampaignType;
use App\Enums\PromotionDiscountType;
use App\Enums\PromotionPublication;
use App\Enums\PromotionTargetType;
use App\Models\AnalyticsVisitor;
use App\Models\Lead;
use App\Models\PromotionCampaign;
use App\Models\PromotionCoupon;
use App\Models\PromotionEvent;
use App\Models\PromotionRedemption;
use App\Models\PromotionTarget;
use App\Services\Analytics\VisitorCapture;
use App\Support\Promotions\Money;
use Illuminate\Support\Carbon;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

/**
 * Single authority for promotional pricing.
 *
 * Tax order follows the existing pricing calculator: package and option amounts are
 * pre-GST. A promotion reduces that subtotal. GST stays extra
 * (discounted subtotal × configured GST percent) and is not folded into leads.estimated_total.
 *
 * Multiple automatic campaigns: largest discount, then lowest id.
 * A coupon cannot be combined with a different live offer.
 * Expired, scheduled, draft, and disabled campaigns never change a price.
 */
class PromotionEngine
{
    public const CACHE_KEY = 'promotions.surface';

    /**
     * @param  list<array{type: string, id?: int|null, package_id?: int|null, option_type?: string|null, label?: string, amount: int|string|float}>  $lines
     * @return array<string, mixed>
     */
    public function quote(array $lines, ?string $couponCode = null, ?string $customerKey = null, ?Carbon $at = null): array
    {
        $at ??= now();
        $normalized = $this->normalizeLines($lines);
        $subtotal = array_sum(array_column($normalized, 'paise'));
        $couponCode = $couponCode !== null ? PromotionCoupon::normalize($couponCode) : null;

        $campaigns = $this->liveCampaigns($at);
        $automatic = $campaigns->filter(fn (PromotionCampaign $campaign) => ! $campaign->coupon_required);
        $ranked = $this->rank($automatic, $normalized, $at);

        $primary = $ranked[0]['campaign'] ?? null;
        $primaryDiscount = $ranked[0]['discount'] ?? 0;
        $adjustments = [];
        $coupon = null;
        $message = null;

        if ($primary && $primaryDiscount > 0) {
            $adjustments[] = $this->campaignAdjustment($primary, $primaryDiscount);
        }

        if ($couponCode !== null && $couponCode !== '') {
            $coupon = $this->resolveCoupon($couponCode, $at);
            $couponResult = $this->couponDiscount($coupon, $normalized, $primary, $primaryDiscount, $customerKey, $at);
            if ($couponResult['replace']) {
                $adjustments = [];
                $primary = $couponResult['campaign'];
                $primaryDiscount = $couponResult['campaign_discount'];
                if ($primary && $primaryDiscount > 0) {
                    $adjustments[] = $this->campaignAdjustment($primary, $primaryDiscount);
                }
            } elseif ($couponResult['campaign_discount'] > 0 && $couponResult['campaign']) {
                $adjustments[] = $this->campaignAdjustment($couponResult['campaign'], $couponResult['campaign_discount']);
                $primary = $couponResult['campaign'];
            }
            if ($couponResult['discount'] > 0) {
                $adjustments[] = [
                    'kind' => 'coupon',
                    'id' => $coupon->id,
                    'code' => $coupon->code,
                    'name' => $coupon->name ?: $coupon->code,
                    'amount' => Money::format($couponResult['discount']),
                    'paise' => $couponResult['discount'],
                ];
            }
            $message = 'Coupon applied successfully.';
        }

        $discount = min($subtotal, array_sum(array_column($adjustments, 'paise')));
        $discounted = max(0, $subtotal - $discount);
        $gstPercent = (int) config('cebinova.pricing.gst_percent', 18);
        $gst = $gstPercent > 0 ? (int) round($discounted * $gstPercent / 100) : 0;

        $snapshot = [
            'subtotal' => Money::format($subtotal),
            'discount_total' => Money::format($discount),
            'discounted_subtotal' => Money::format($discounted),
            'gst_percent' => $gstPercent,
            'gst_amount' => Money::format($gst),
            'gst_note' => 'GST is extra and is calculated on the discounted pre-tax subtotal.',
            'adjustments' => array_map(fn ($row) => collect($row)->except('paise')->all(), $adjustments),
            'promotion_id' => $primary?->id,
            'promotion_name' => $primary?->name,
            'coupon_id' => $coupon?->id,
            'coupon_code' => $coupon?->code,
            'ends_at' => $primary?->ends_at?->toIso8601String(),
            'captured_at' => $at->toIso8601String(),
            'lines' => array_map(fn ($line) => [
                'type' => $line['type'],
                'id' => $line['id'],
                'label' => $line['label'],
                'amount' => Money::format($line['paise']),
            ], $normalized),
        ];

        return [
            'subtotal' => Money::format($subtotal),
            'discount_total' => Money::format($discount),
            'discounted_subtotal' => Money::format($discounted),
            'gst_percent' => $gstPercent,
            'gst_amount' => Money::format($gst),
            'message' => $discount > 0 ? $message : null,
            'adjustments' => $snapshot['adjustments'],
            'promotion_id' => $primary?->id,
            'promotion_name' => $primary?->name,
            'coupon_id' => $coupon?->id,
            'coupon_code' => $coupon?->code,
            'ends_at' => $primary?->ends_at?->toIso8601String(),
            'server_now' => $at->toIso8601String(),
            'snapshot' => $snapshot,
        ];
    }

    /**
     * @return array<string, mixed>|null
     */
    public function previewLine(string $type, int $id, int|string $amount, ?int $packageId = null, ?string $optionType = null): ?array
    {
        try {
            $quote = $this->quote([[
                'type' => $type,
                'id' => $id,
                'package_id' => $packageId,
                'option_type' => $optionType,
                'amount' => $amount,
            ]]);
        } catch (\Throwable $exception) {
            report($exception);

            return null;
        }

        if ((int) Money::paise($quote['discount_total']) <= 0) {
            return null;
        }

        return [
            'name' => $quote['promotion_name'],
            'original' => $quote['subtotal'],
            'final' => $quote['discounted_subtotal'],
            'savings' => $quote['discount_total'],
            'ends_at' => $quote['ends_at'],
        ];
    }

    /**
     * @param  array<string, mixed>  $quote
     */
    public function redeem(Lead $lead, array $quote, ?string $customerKey = null): ?PromotionRedemption
    {
        $snapshot = $quote['snapshot'] ?? null;
        if (! is_array($snapshot) || (int) Money::paise($snapshot['discount_total'] ?? 0) <= 0) {
            return null;
        }

        return DB::transaction(function () use ($lead, $snapshot, $customerKey) {
            $coupon = null;
            if (! empty($snapshot['coupon_id'])) {
                $coupon = PromotionCoupon::query()->whereKey($snapshot['coupon_id'])->lockForUpdate()->first();
                if (! $coupon) {
                    throw ValidationException::withMessages(['coupon_code' => 'Invalid coupon code.']);
                }
                $this->assertCouponCapacity($coupon, $customerKey);
            }

            $redemption = PromotionRedemption::query()->create([
                'promotion_id' => $snapshot['promotion_id'] ?? null,
                'coupon_id' => $coupon?->id,
                'lead_id' => $lead->id,
                'visitor_uuid' => request()->cookie(VisitorCapture::COOKIE),
                'customer_key' => $customerKey,
                'original_amount' => $snapshot['subtotal'],
                'discount_amount' => $snapshot['discount_total'],
                'final_amount' => $snapshot['discounted_subtotal'],
                'snapshot' => $snapshot,
                'redeemed_at' => now(),
            ]);

            $this->record('promotion_conversion', $snapshot['promotion_id'] ?? null, $coupon?->id, $lead->id);

            return $redemption;
        });
    }

    /**
     * @return array<string, mixed>
     */
    public function surface(?Carbon $at = null): array
    {
        $at ??= now();
        $cached = Cache::get(self::CACHE_KEY);
        if (is_array($cached) && isset($cached['valid_until']) && Carbon::parse($cached['valid_until'])->gt($at) && $this->cacheMatchesClock($cached['payload'] ?? [], $at)) {
            return $cached['payload'];
        }

        $payload = $this->buildSurface($at);
        $boundary = $this->nextBoundary($at);
        $ttl = max(1, min(60, $at->diffInSeconds($boundary)));
        Cache::put(self::CACHE_KEY, [
            'valid_until' => $at->copy()->addSeconds($ttl)->toIso8601String(),
            'payload' => $payload,
        ], $ttl);

        return $payload;
    }

    public function forgetCache(): void
    {
        Cache::forget(self::CACHE_KEY);
    }

    public function record(string $event, ?int $promotionId = null, ?int $couponId = null, ?int $leadId = null, array $meta = []): void
    {
        if (! in_array($event, PromotionEvent::EVENTS, true)) {
            return;
        }

        $visitor = request()->cookie(VisitorCapture::COOKIE);
        if ($visitor) {
            $known = AnalyticsVisitor::query()->where('uuid', $visitor)->exists();
            $visitor = $known ? $visitor : null;
        }

        PromotionEvent::query()->create([
            'event' => $event,
            'promotion_id' => $promotionId,
            'coupon_id' => $couponId,
            'lead_id' => $leadId,
            'visitor_uuid' => $visitor,
            'path' => mb_substr((string) (request()->headers->get('referer') ?: request()->path()), 0, 2048),
            'meta' => $meta ?: null,
        ]);
    }

    /**
     * @param  list<array<string, mixed>>  $lines
     * @return list<array{type: string, id: int|null, package_id: int|null, option_type: string|null, label: string, paise: int}>
     */
    private function normalizeLines(array $lines): array
    {
        $normalized = [];
        foreach ($lines as $line) {
            $paise = Money::paise($line['amount'] ?? 0);
            if ($paise < 0) {
                $paise = 0;
            }
            $normalized[] = [
                'type' => (string) ($line['type'] ?? ''),
                'id' => isset($line['id']) ? (int) $line['id'] : null,
                'package_id' => isset($line['package_id']) ? (int) $line['package_id'] : null,
                'option_type' => isset($line['option_type']) ? (string) $line['option_type'] : null,
                'label' => (string) ($line['label'] ?? 'Item'),
                'paise' => $paise,
            ];
        }

        return $normalized;
    }

    /**
     * @return Collection<int, PromotionCampaign>
     */
    private function liveCampaigns(Carbon $at): Collection
    {
        return PromotionCampaign::query()
            ->with('targets')
            ->where('publication_status', PromotionPublication::Published->value)
            ->where('starts_at', '<=', $at)
            ->where('ends_at', '>=', $at)
            ->orderBy('id')
            ->get();
    }

    /**
     * @param  Collection<int, PromotionCampaign>  $campaigns
     * @param  list<array{type: string, id: int|null, package_id: int|null, option_type: string|null, label: string, paise: int}>  $lines
     * @return list<array{campaign: PromotionCampaign, discount: int}>
     */
    private function rank(Collection $campaigns, array $lines, Carbon $at): array
    {
        $ranked = [];
        foreach ($campaigns as $campaign) {
            $discount = $this->campaignDiscount($campaign, $lines, $at);
            if ($discount <= 0) {
                continue;
            }
            $ranked[] = ['campaign' => $campaign, 'discount' => $discount];
        }

        usort($ranked, function (array $left, array $right) {
            $savings = $right['discount'] <=> $left['discount'];
            if ($savings !== 0) {
                return $savings;
            }

            $ending = $left['campaign']->ends_at <=> $right['campaign']->ends_at;

            return $ending !== 0 ? $ending : $left['campaign']->id <=> $right['campaign']->id;
        });

        return $ranked;
    }

    /**
     * @param  list<array{type: string, id: int|null, package_id: int|null, option_type: string|null, label: string, paise: int}>  $lines
     */
    private function campaignDiscount(PromotionCampaign $campaign, array $lines, Carbon $at, ?int $alreadyAllocated = null): int
    {
        if (! $campaign->isLive($at)) {
            return 0;
        }

        $eligible = $this->eligibleLines($campaign, $lines);
        if ($eligible === []) {
            return 0;
        }

        $base = array_sum(array_column($eligible, 'paise'));
        if ($alreadyAllocated !== null) {
            $base = max(0, $base - $this->overlap($campaign, $lines, $alreadyAllocated));
        }

        return $this->discountAmount(
            $campaign->discount_type,
            (string) $campaign->discount_value,
            $base,
            null,
        );
    }

    /**
     * @param  list<array{type: string, id: int|null, package_id: int|null, option_type: string|null, label: string, paise: int}>  $lines
     * @return list<array{type: string, id: int|null, package_id: int|null, option_type: string|null, label: string, paise: int}>
     */
    private function eligibleLines(PromotionCampaign $campaign, array $lines): array
    {
        $includes = $campaign->targets->where('mode', 'include')->values();
        if ($includes->isEmpty()) {
            return [];
        }

        $matched = [];
        foreach ($lines as $line) {
            if ($includes->contains(fn (PromotionTarget $target) => $this->matches($target, $line))) {
                $matched[] = $line;
            }
        }

        return $matched;
    }

    /**
     * @param  array{type: string, id: int|null, package_id: int|null, option_type: string|null, label: string, paise: int}  $line
     */
    private function matches(PromotionTarget $target, array $line): bool
    {
        $type = $target->target_type;

        return match ($type) {
            PromotionTargetType::AllWebsitePackages => $line['type'] === 'website_package',
            PromotionTargetType::AllMarketingPackages => $line['type'] === 'marketing_plan',
        };
    }

    private function discountAmount(PromotionDiscountType $type, string $value, int $base, ?int $cap): int
    {
        if ($base <= 0) {
            return 0;
        }

        $discount = $type === PromotionDiscountType::Percentage
            ? Money::percent($base, $value)
            : min($base, Money::paise($value));

        if ($cap !== null) {
            $discount = min($discount, $cap);
        }

        return min($base, max(0, $discount));
    }

    /**
     * @param  list<array{type: string, id: int|null, package_id: int|null, option_type: string|null, label: string, paise: int}>  $lines
     */
    private function overlap(PromotionCampaign $campaign, array $lines, int $primaryDiscount): int
    {
        $eligible = $this->eligibleLines($campaign, $lines);
        $eligibleTotal = array_sum(array_column($eligible, 'paise'));
        $all = array_sum(array_column($lines, 'paise'));
        if ($eligibleTotal <= 0 || $all <= 0) {
            return 0;
        }

        return (int) round($primaryDiscount * ($eligibleTotal / $all));
    }

    private function resolveCoupon(string $code, Carbon $at): PromotionCoupon
    {
        if (! preg_match('/^[A-Z0-9-]{2,40}$/', $code)) {
            throw ValidationException::withMessages(['coupon_code' => 'Invalid coupon code.']);
        }

        $coupon = PromotionCoupon::query()->with('campaign.targets')->where('code', $code)->first();
        if (! $coupon || ! $coupon->is_active) {
            throw ValidationException::withMessages(['coupon_code' => 'Invalid coupon code.']);
        }
        if ($coupon->starts_at && $at->lt($coupon->starts_at)) {
            throw ValidationException::withMessages(['coupon_code' => 'Invalid coupon code.']);
        }
        if ($coupon->ends_at && $at->gt($coupon->ends_at)) {
            throw ValidationException::withMessages(['coupon_code' => 'Coupon has expired.']);
        }
        if ($coupon->campaign && ! $coupon->campaign->isLive($at)) {
            $state = $coupon->campaign->state($at);
            $message = $state === 'expired' ? 'Coupon has expired.' : 'Invalid coupon code.';
            throw ValidationException::withMessages(['coupon_code' => $message]);
        }

        return $coupon;
    }

    /**
     * @param  list<array{type: string, id: int|null, package_id: int|null, option_type: string|null, label: string, paise: int}>  $lines
     * @return array{discount: int, campaign: ?PromotionCampaign, campaign_discount: int, replaces_primary: bool}
     */
    private function couponDiscount(PromotionCoupon $coupon, array $lines, ?PromotionCampaign $primary, int $primaryDiscount, ?string $customerKey, Carbon $at): array
    {
        $this->assertCouponCapacity($coupon, $customerKey);
        $campaign = $coupon->campaign;
        $hasOwnDiscount = $coupon->discount_type && $coupon->discount_value !== null;

        if ($campaign && ! $hasOwnDiscount) {
            if ($campaign->targets->where('mode', 'include')->isEmpty() || $this->eligibleLines($campaign, $lines) === []) {
                throw ValidationException::withMessages(['coupon_code' => 'Coupon is not valid for this package.']);
            }
            $discount = $this->campaignDiscount($campaign, $lines, $at);
            if ($discount <= 0) {
                throw ValidationException::withMessages(['coupon_code' => 'Coupon is not valid for this package.']);
            }
            if ($primary && $primaryDiscount > 0 && $primary->id !== $campaign->id) {
                throw ValidationException::withMessages(['coupon_code' => 'This coupon cannot be combined with the current offer.']);
            }

            return [
                'discount' => 0,
                'campaign' => $campaign,
                'campaign_discount' => $discount,
                'replace' => true,
            ];
        }

        $baseLines = $campaign ? $this->eligibleLines($campaign, $lines) : $lines;
        if ($campaign && $baseLines === []) {
            throw ValidationException::withMessages(['coupon_code' => 'Coupon is not valid for this package.']);
        }

        $base = array_sum(array_column($baseLines, 'paise'));
        $minimum = $coupon->minimum_amount;
        if ($minimum !== null && $base < Money::paise($minimum)) {
            throw ValidationException::withMessages(['coupon_code' => 'Minimum order amount is '.Money::rupees(Money::paise($minimum)).'.']);
        }

        if ($primary && $primaryDiscount > 0) {
            throw ValidationException::withMessages(['coupon_code' => 'This coupon cannot be combined with the current offer.']);
        }

        $type = $coupon->discount_type ?? PromotionDiscountType::Flat;
        $value = (string) ($coupon->discount_value ?? 0);
        $cap = $coupon->maximum_discount_amount !== null ? Money::paise($coupon->maximum_discount_amount) : null;
        $discount = $this->discountAmount($type, $value, $base, $cap);

        return [
            'discount' => $discount,
            'campaign' => null,
            'campaign_discount' => 0,
            'replace' => false,
        ];
    }

    /**
     * @return array{kind: string, id: int, name: string, label: string, amount: string, paise: int}
     */
    private function campaignAdjustment(PromotionCampaign $campaign, int $discount): array
    {
        return [
            'kind' => 'campaign',
            'id' => $campaign->id,
            'name' => $campaign->name,
            'label' => $campaign->name.' '.$campaign->discountLabel(),
            'amount' => Money::format($discount),
            'paise' => $discount,
        ];
    }

    private function assertCouponCapacity(PromotionCoupon $coupon, ?string $customerKey): void
    {
        if ($coupon->usage_limit !== null) {
            $used = PromotionRedemption::query()->where('coupon_id', $coupon->id)->count();
            if ($used >= $coupon->usage_limit) {
                throw ValidationException::withMessages(['coupon_code' => 'Coupon usage limit reached.']);
            }
        }

        if ($coupon->usage_limit_per_customer !== null && $customerKey) {
            $used = PromotionRedemption::query()
                ->where('coupon_id', $coupon->id)
                ->where('customer_key', $customerKey)
                ->count();
            if ($used >= $coupon->usage_limit_per_customer) {
                throw ValidationException::withMessages(['coupon_code' => 'Coupon usage limit reached.']);
            }
        }
    }

    /**
     * @return array<string, mixed>
     */
    private function buildSurface(Carbon $at): array
    {
        $live = PromotionCampaign::query()
            ->with('coupons')
            ->where('publication_status', PromotionPublication::Published->value)
            ->where('starts_at', '<=', $at)
            ->where('ends_at', '>=', $at)
            ->orderBy('ends_at')
            ->orderByDesc('discount_value')
            ->orderBy('id')
            ->get()
            ->filter(fn (PromotionCampaign $campaign) => $campaign->show_popup || $campaign->show_announcement_bar || $campaign->show_pricing_badge)
            ->values();
        $active = $live->first();

        $upcoming = $this->upcomingCampaign($at);
        $featured = $active ?: $upcoming;

        if (! $featured) {
            return [
                'mode' => 'none',
                'server_now' => $at->toIso8601String(),
                'campaign' => null,
                'offers' => [],
            ];
        }

        return [
            'mode' => $featured->isLive($at) ? 'active' : 'upcoming',
            'server_now' => $at->toIso8601String(),
            'campaign' => $this->present($featured, ! $featured->isLive($at)),
            'active_offer' => $active ? $this->present($active, false) : null,
            'live_offers' => $live
                ->filter(fn (PromotionCampaign $campaign) => $campaign->show_popup || $campaign->campaign_type === PromotionCampaignType::Festival)
                ->map(fn (PromotionCampaign $campaign) => $this->present($campaign, false))
                ->values()
                ->all(),
            'offers' => $this->visibleOffers($at),
        ];
    }

    /**
     * @return list<array<string, mixed>>
     */
    private function visibleOffers(Carbon $at): array
    {
        return PromotionCampaign::query()
            ->with('coupons')
            ->where('publication_status', PromotionPublication::Published->value)
            ->orderBy('starts_at')
            ->get()
            ->filter(function (PromotionCampaign $campaign) use ($at) {
                if ($campaign->isLive($at)) {
                    return true;
                }

                return $this->inPreviewWindow($campaign, $at);
            })
            ->map(fn (PromotionCampaign $campaign) => $this->present($campaign, ! $campaign->isLive($at)))
            ->values()
            ->all();
    }

    /**
     * @return array<string, mixed>
     */
    public function present(PromotionCampaign $campaign, bool $upcoming): array
    {
        $coupon = $campaign->relationLoaded('coupons')
            ? $campaign->coupons->firstWhere('is_active', true)
            : $campaign->coupons()->where('is_active', true)->first();

        return [
            'id' => $campaign->id,
            'name' => $campaign->name,
            'slug' => $campaign->slug,
            'type' => $campaign->campaign_type?->value,
            'popup_design' => $campaign->popup_design?->value ?: 'split',
            'type_label' => $campaign->campaign_type?->label(),
            'festival_name' => $campaign->festival_name,
            'theme' => $this->theme($campaign),
            'headline' => $campaign->headline ?: $campaign->name,
            'subheadline' => $campaign->subheadline,
            'description' => $campaign->description,
            'discount_label' => $campaign->discountLabel(),
            'badge' => $campaign->badge_text,
            'terms' => $campaign->terms,
            'starts_at' => $campaign->starts_at?->toIso8601String(),
            'ends_at' => $campaign->ends_at?->toIso8601String(),
            'upcoming' => $upcoming,
            'show_popup' => $upcoming
                ? ($campaign->show_popup || $campaign->campaign_type === PromotionCampaignType::Festival)
                : (bool) $campaign->show_popup,
            'show_announcement_bar' => (bool) $campaign->show_announcement_bar,
            'show_pricing_badge' => (bool) $campaign->show_pricing_badge,
            'show_countdown' => (bool) $campaign->show_countdown,
            'announcement_text' => $campaign->announcement_text,
            'announcement_dismissible' => (bool) $campaign->announcement_dismissible,
            'popup_delay_seconds' => (int) $campaign->popup_delay_seconds,
            'popup_scroll_percent' => $campaign->popup_scroll_percent,
            'popup_exit_intent' => (bool) $campaign->popup_exit_intent,
            'popup_frequency' => $campaign->popup_frequency ?: 'session',
            'popup_cooldown_hours' => (int) $campaign->popup_cooldown_hours,
            'popup_repeat_minutes' => (int) $campaign->popup_repeat_minutes,
            'cta_text' => $campaign->cta_text ?: 'Explore Offer',
            'cta_url' => $campaign->cta_url ?: route('offers.show', $campaign->slug),
            'coupon_code' => $coupon?->code,
            'coupon_required' => (bool) $campaign->coupon_required,
            'state' => $upcoming ? 'scheduled' : $campaign->state(),
        ];
    }

    private function theme(PromotionCampaign $campaign): string
    {
        $occasion = Str::lower($campaign->festival_name.' '.$campaign->name);

        if (Str::contains($occasion, ['navratri', 'navaratri', 'garba', 'dandiya'])) {
            return 'garba';
        }

        return $campaign->campaign_type === PromotionCampaignType::Festival ? 'crackers' : 'none';
    }

    private function upcomingCampaign(Carbon $at): ?PromotionCampaign
    {
        return PromotionCampaign::query()
            ->with('coupons')
            ->where('publication_status', PromotionPublication::Published->value)
            ->where('starts_at', '>', $at)
            ->orderBy('starts_at')
            ->orderBy('id')
            ->get()
            ->first(fn (PromotionCampaign $campaign) => $this->inPreviewWindow($campaign, $at));
    }

    private function inPreviewWindow(PromotionCampaign $campaign, Carbon $at): bool
    {
        if (! $campaign->starts_at || ! $campaign->starts_at->gt($at)) {
            return false;
        }

        $festival = $campaign->campaign_type === PromotionCampaignType::Festival;
        if (! $campaign->show_before_start && ! ($festival && $campaign->show_popup)) {
            return false;
        }

        $days = $festival ? max(1, (int) $campaign->preview_days) : (int) $campaign->preview_days;

        return $at->gte($campaign->starts_at->copy()->subDays($days));
    }

    private function cacheMatchesClock(array $payload, Carbon $at): bool
    {
        $campaign = $payload['campaign'] ?? null;
        $mode = $payload['mode'] ?? 'none';
        if ($mode === 'active' && is_array($campaign) && ! empty($campaign['ends_at']) && Carbon::parse($campaign['ends_at'])->lt($at)) {
            return false;
        }
        if ($mode === 'upcoming' && is_array($campaign) && ! empty($campaign['starts_at']) && Carbon::parse($campaign['starts_at'])->lte($at)) {
            return false;
        }

        return true;
    }

    private function nextBoundary(Carbon $at): Carbon
    {
        $stamps = PromotionCampaign::query()
            ->where('publication_status', PromotionPublication::Published->value)
            ->get(['starts_at', 'ends_at'])
            ->flatMap(fn (PromotionCampaign $campaign) => [$campaign->starts_at, $campaign->ends_at])
            ->filter(fn ($stamp) => $stamp && $stamp->gt($at))
            ->sort()
            ->values();

        return $stamps->first() ?? $at->copy()->addMinute();
    }
}
