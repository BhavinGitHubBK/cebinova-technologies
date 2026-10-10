<?php

namespace App\Http\Controllers;

use App\Enums\PromotionTargetType;
use App\Models\Package;
use App\Models\PackagePlan;
use App\Models\PromotionCampaign;
use App\Models\PromotionEvent;
use App\Services\MarketingPackageCalculator;
use App\Services\PricingCalculator;
use App\Services\Promotions\PromotionEngine;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;

class PromotionController extends Controller
{
    public function __construct(private PromotionEngine $promotions) {}

    public function surface(): JsonResponse
    {
        return response()->json($this->promotions->surface());
    }

    public function quote(Request $request): JsonResponse
    {
        $data = $request->validate([
            'pricing_package_id' => ['nullable', 'integer'],
            'pricing_option_ids' => ['nullable', 'array'],
            'pricing_option_ids.*' => ['integer'],
            'marketing_package_id' => ['nullable', 'integer'],
            'marketing_plan_id' => ['nullable', 'integer'],
            'coupon_code' => ['nullable', 'string', 'max:40'],
        ]);

        if (empty($data['pricing_package_id']) && empty($data['marketing_package_id'])) {
            return response()->json(['message' => 'Choose a package to price.'], 422);
        }

        $customerKey = null;
        try {
            if ($data['pricing_package_id'] ?? null) {
                $pricing = app(PricingCalculator::class)->calculate(
                    (int) $data['pricing_package_id'],
                    $data['pricing_option_ids'] ?? [],
                    $data['coupon_code'] ?? null,
                    $customerKey
                );
                $quote = $pricing['promotion'];
            } else {
                $marketing = app(MarketingPackageCalculator::class)->calculate(
                    (int) $data['marketing_package_id'],
                    (int) $data['marketing_plan_id'],
                    $data['coupon_code'] ?? null,
                    $customerKey
                );
                $quote = $marketing['promotion'];
            }
        } catch (ValidationException $exception) {
            if (! empty($data['coupon_code'])) {
                $this->promotions->record('coupon_rejected');
            }
            throw $exception;
        }

        if (! empty($data['coupon_code']) && is_array($quote)) {
            $this->promotions->record(
                ((float) ($quote['discount_total'] ?? 0)) > 0 ? 'coupon_applied' : 'coupon_rejected',
                isset($quote['promotion_id']) ? (int) $quote['promotion_id'] : null,
                isset($quote['coupon_id']) ? (int) $quote['coupon_id'] : null
            );
        }

        if (! is_array($quote)) {
            return response()->json([
                'subtotal' => null,
                'discount_total' => '0.00',
                'discounted_subtotal' => null,
                'adjustments' => [],
                'message' => null,
            ]);
        }

        unset($quote['snapshot']);

        return response()->json($quote);
    }

    public function event(Request $request): JsonResponse
    {
        $data = $request->validate([
            'event' => ['required', 'string'],
            'promotion_id' => ['nullable', 'integer'],
            'coupon_id' => ['nullable', 'integer'],
        ]);

        $allowed = array_values(array_filter(
            PromotionEvent::EVENTS,
            fn (string $event) => $event !== 'promotion_conversion'
        ));

        if (! in_array($data['event'], $allowed, true)) {
            return response()->json(['ok' => false], 422);
        }

        $campaignId = isset($data['promotion_id'])
            ? PromotionCampaign::query()->whereKey($data['promotion_id'])->value('id')
            : null;

        $this->promotions->record($data['event'], $campaignId ? (int) $campaignId : null, $data['coupon_id'] ?? null);

        return response()->json(['ok' => true]);
    }

    public function index(): View
    {
        $campaigns = $this->visibleCampaigns()->load('targets');
        $card = fn (PromotionCampaign $campaign) => $this->promotions->present($campaign, ! $campaign->isLive()) + [
            'covers' => $campaign->targets
                ->where('mode', 'include')
                ->map(fn ($target) => $target->target_type->label())
                ->unique()
                ->values()
                ->all(),
        ];

        return view('pages.offers.index', [
            'campaigns' => $campaigns,
            'live' => $campaigns->filter->isLive()->sortBy('ends_at')->map($card)->values(),
            'upcoming' => $campaigns->reject->isLive()->sortBy('starts_at')->map($card)->values(),
        ]);
    }

    public function show(string $slug): View
    {
        $campaign = PromotionCampaign::query()->with(['targets', 'coupons'])->where('slug', $slug)->firstOrFail();
        $state = $campaign->state();
        if (! in_array($state, ['active', 'scheduled', 'expired'], true) || $campaign->publication_status->value !== 'published') {
            abort(404);
        }
        if ($state === 'scheduled' && ! $this->inPreviewWindow($campaign)) {
            abort(404);
        }

        $this->promotions->record('offer_page_view', $campaign->id);

        return view('pages.offers.show', [
            'campaign' => $campaign,
            'state' => $state,
            'presentation' => $this->promotions->present($campaign, $state === 'scheduled'),
            'packages' => $state === 'active' ? $this->pricedTargets($campaign) : [],
        ]);
    }

    /**
     * @return Collection<int, PromotionCampaign>
     */
    private function visibleCampaigns()
    {
        return PromotionCampaign::query()
            ->with('coupons')
            ->where('publication_status', 'published')
            ->orderBy('starts_at')
            ->get()
            ->filter(function (PromotionCampaign $campaign) {
                $state = $campaign->state();

                return $state === 'active' || ($state === 'scheduled' && $this->inPreviewWindow($campaign));
            })
            ->values();
    }

    private function inPreviewWindow(PromotionCampaign $campaign): bool
    {
        if (! $campaign->show_before_start || ! $campaign->starts_at) {
            return false;
        }

        return now()->gte($campaign->starts_at->copy()->subDays((int) $campaign->preview_days))
            && now()->lt($campaign->starts_at);
    }

    /**
     * @return list<array<string, mixed>>
     */
    private function pricedTargets(PromotionCampaign $campaign): array
    {
        $rows = [];
        $allWebsite = $campaign->targets->contains(fn ($target) => $target->target_type === PromotionTargetType::AllWebsitePackages && $target->mode === 'include');
        if ($allWebsite) {
            $packages = Package::query()->website()->active()->with('plans')->get();
            foreach ($packages as $package) {
                $plan = $package->plans->firstWhere('is_active', true) ?? $package->plans->first();
                if (! $plan) {
                    continue;
                }
                $preview = $this->promotions->previewLine('website_package', $package->id, $plan->price);
                if (! $preview) {
                    continue;
                }
                $rows[] = ['name' => $package->name, 'kind' => 'Website package'] + $preview;
            }
        }

        $allMarketing = $campaign->targets->contains(fn ($target) => $target->target_type === PromotionTargetType::AllMarketingPackages && $target->mode === 'include');
        if ($allMarketing) {
            $plans = PackagePlan::query()->active()->with('package')
                ->whereHas('package', fn ($query) => $query->marketing()->active())
                ->get();
            foreach ($plans as $plan) {
                $preview = $this->promotions->previewLine('marketing_plan', $plan->id, $plan->price, $plan->package_id);
                if (! $preview) {
                    continue;
                }
                $rows[] = ['name' => $plan->package->name.' · '.$plan->label, 'kind' => 'Marketing package'] + $preview;
            }
        }

        return $rows;
    }
}
