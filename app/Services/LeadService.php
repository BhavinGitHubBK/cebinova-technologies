<?php

namespace App\Services;

use App\Enums\LeadStatus;
use App\Http\Requests\StoreLeadRequest;
use App\Models\Demo;
use App\Models\Industry;
use App\Models\Lead;
use App\Models\Service;
use App\Models\Solution;
use App\Services\Promotions\PromotionEngine;
use App\Support\MarketingPackages;
use App\Support\Promotions\Money;
use Illuminate\Validation\ValidationException;

class LeadService
{
    public function storeFromRequest(StoreLeadRequest $request, ?PricingCalculator $pricing = null): ?Lead
    {
        if ($request->isHoneypotTriggered()) {
            return null;
        }

        $category = $request->validated('package_category');
        $duration = $request->validated('plan_duration');

        $serviceRecord = null;
        if ($request->validated('service_id')) {
            $serviceRecord = Service::query()->active()->find($request->validated('service_id'));
            if (! $serviceRecord) {
                throw ValidationException::withMessages([
                    'service_id' => 'The selected service is unavailable.',
                ]);
            }
        }

        $solution = $request->validated('solution_id')
            ? Solution::query()->active()->find($request->validated('solution_id'))
            : null;
        if ($request->validated('solution_id') && ! $solution) {
            throw ValidationException::withMessages([
                'solution_id' => 'The selected solution is unavailable.',
            ]);
        }

        $industry = $request->validated('industry_id')
            ? Industry::query()->active()->find($request->validated('industry_id'))
            : null;
        if ($request->validated('industry_id') && ! $industry) {
            throw ValidationException::withMessages([
                'industry_id' => 'The selected industry is unavailable.',
            ]);
        }

        $coupon = $request->validated('coupon_code');
        $customerKey = strtolower((string) ($request->validated('email') ?: $request->validated('phone')));

        $marketingData = null;
        if ($request->validated('marketing_package_id')) {
            $marketingData = app(MarketingPackageCalculator::class)->calculate(
                (int) $request->validated('marketing_package_id'),
                (int) $request->validated('marketing_plan_id'),
                $coupon,
                $customerKey
            );
            $category = $marketingData['category'];
            $duration = $marketingData['duration'];
        }

        $pricingData = null;
        if ($request->validated('pricing_package_id')) {
            $pricingData = ($pricing ?? app(PricingCalculator::class))->calculate(
                (int) $request->validated('pricing_package_id'),
                $request->validated('pricing_option_ids', []),
                $coupon,
                $customerKey
            );
        }

        $promotion = $pricingData['promotion'] ?? $marketingData['promotion'] ?? null;

        $demo = $request->validated('demo_id') ? Demo::active()->findOrFail($request->validated('demo_id')) : null;
        $source = $demo
            ? 'demo'
            : ($serviceRecord
                ? 'Service'
                : ($solution ? 'Technology Solution' : $this->resolvedSource($request, $category)));

        $duplicateQuery = Lead::query()
            ->where('created_at', '>=', now()->subMinutes(2))
            ->where(function ($query) use ($request) {
                $query->where('phone', $request->validated('phone'));

                if ($request->validated('email')) {
                    $query->orWhere('email', $request->validated('email'));
                }
            });

        foreach ([
            'name' => $request->validated('name'),
            'business_name' => $request->validated('business_name'),
            'phone' => $request->validated('phone'),
            'whatsapp' => $request->validated('whatsapp'),
            'email' => $request->validated('email'),
            'business_type' => $request->validated('business_type'),
            'service' => $serviceRecord?->name ?? $request->validated('service'),
            'service_id' => $serviceRecord?->id,
            'solution_id' => $solution?->id,
            'industry_id' => $industry?->id,
            'package_category' => $category,
            'plan_duration' => $duration,
            'pricing_package_id' => $pricingData['package_id'] ?? null,
            'marketing_package_id' => $marketingData['package_id'] ?? null,
            'marketing_plan_id' => $marketingData['plan_id'] ?? null,
            'demo_id' => $demo?->id,
            'source' => $source,
            'message' => $request->validated('message'),
            'free_consultation' => $request->boolean('consultation'),
        ] as $field => $value) {
            $value === null
                ? $duplicateQuery->whereNull($field)
                : $duplicateQuery->where($field, $value);
        }

        $duplicate = $duplicateQuery->latest()->first();

        if ($duplicate) {
            return $duplicate;
        }

        $lead = Lead::query()->create([
            'demo_id' => $demo?->id,
            'solution_id' => $solution?->id,
            'solution_snapshot' => $solution ? ['solution_id' => $solution->id, 'name' => $solution->name, 'slug' => $solution->slug, 'captured_at' => now()->toIso8601String()] : null,
            'industry_id' => $industry?->id,
            'industry_snapshot' => $industry ? ['industry_id' => $industry->id, 'name' => $industry->name, 'slug' => $industry->slug, 'captured_at' => now()->toIso8601String()] : null,
            'source_page' => $demo ? route('demos').'#'.$demo->slug : ($solution ? route('solutions.show', $solution->slug) : ($industry ? route('industries') : null)),
            'name' => $request->validated('name'),
            'business_name' => $request->validated('business_name'),
            'phone' => $request->validated('phone'),
            'whatsapp' => $request->validated('whatsapp'),
            'email' => $request->validated('email'),
            'business_type' => $request->validated('business_type'),
            'service' => $serviceRecord?->name ?? $request->validated('service'),
            'service_id' => $serviceRecord?->id,
            'service_snapshot' => $serviceRecord ? [
                'service_id' => $serviceRecord->id,
                'name' => $serviceRecord->name,
                'slug' => $serviceRecord->slug,
                'captured_at' => now()->toIso8601String(),
            ] : null,
            'package_category' => $category,
            'plan_duration' => $duration,
            'selected_price' => isset($marketingData['price']) ? cebinova_inr($marketingData['price']) : MarketingPackages::formattedPrice($category, $duration),
            'marketing_package_id' => $marketingData['package_id'] ?? null,
            'marketing_plan_id' => $marketingData['plan_id'] ?? null,
            'marketing_snapshot' => $marketingData['snapshot'] ?? null,
            'pricing_package_id' => $pricingData['package_id'] ?? null,
            'pricing_option_ids' => $pricingData['option_ids'] ?? null,
            'estimated_total' => $pricingData['total'] ?? null,
            'pricing_snapshot' => $pricingData['snapshot'] ?? null,
            'promotion_id' => $promotion['promotion_id'] ?? null,
            'coupon_id' => $promotion['coupon_id'] ?? null,
            'promotion_snapshot' => $promotion['snapshot'] ?? null,
            'city' => $request->validated('city'),
            'budget' => $request->validated('budget'),
            'message' => $request->validated('message'),
            'free_consultation' => $request->boolean('consultation'),
            'source' => $source,
            'page_url' => mb_substr((string) $request->headers->get('referer'), 0, 2048) ?: null,
            'status' => Lead::STATUS_NEW,
        ]);

        if (is_array($promotion) && (int) Money::paise($promotion['discount_total'] ?? 0) > 0) {
            try {
                app(PromotionEngine::class)->redeem($lead, $promotion, $customerKey);
            } catch (\Throwable $exception) {
                $lead->delete();
                throw $exception;
            }
        }

        try {
            app(LeadNotifier::class)->notify($lead);
        } catch (\Throwable $exception) {
            report($exception);
        }

        return $lead;
    }

    public function updateStatus(Lead $lead, LeadStatus $status): void
    {
        $updates = ['status' => $status->value];
        $timestampColumn = $status->timestampColumn();

        if ($timestampColumn && ! $lead->{$timestampColumn}) {
            $updates[$timestampColumn] = now();
        }

        $lead->update($updates);
    }

    private function resolvedSource(StoreLeadRequest $request, ?string $category): string
    {
        if ($category && in_array($category, MarketingPackages::categories(), true)) {
            return $category;
        }

        $allowed = config('cebinova.leads.sources');
        $submitted = $request->validated('source');

        if (is_string($submitted) && in_array($submitted, $allowed, true)) {
            return $submitted;
        }

        return 'Website Contact';
    }
}
