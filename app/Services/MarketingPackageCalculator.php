<?php

namespace App\Services;

use App\Models\Package;
use App\Models\PackagePlan;
use App\Services\Promotions\PromotionEngine;
use App\Support\Promotions\Money;
use Illuminate\Validation\ValidationException;

class MarketingPackageCalculator
{
    public function calculate(int $packageId, int $planId, ?string $coupon = null, ?string $customerKey = null): array
    {
        $package = Package::query()->marketing()->active()->find($packageId);
        $plan = PackagePlan::query()->active()->where('package_id', $packageId)->find($planId);

        if (! $package || ! $plan) {
            throw ValidationException::withMessages([
                'marketing_plan_id' => 'The selected marketing package or duration is unavailable.',
            ]);
        }

        $lines = [[
            'type' => 'marketing_plan',
            'id' => $plan->id,
            'package_id' => $package->id,
            'label' => $package->name.' · '.$plan->label,
            'amount' => $plan->price,
        ]];
        $promotion = null;
        try {
            $promotion = app(PromotionEngine::class)->quote($lines, $coupon, $customerKey);
        } catch (ValidationException $exception) {
            throw $exception;
        } catch (\Throwable $exception) {
            report($exception);
        }

        $price = $promotion ? Money::paise($promotion['discounted_subtotal']) : Money::paise($plan->price);

        return [
            'package_id' => $package->id,
            'plan_id' => $plan->id,
            'category' => $package->service_label ?: $package->name,
            'duration' => $plan->label,
            'price' => intdiv($price + 50, 100),
            'promotion' => $promotion,
            'snapshot' => [
                'package_id' => $package->id,
                'package_name' => $package->name,
                'plan_id' => $plan->id,
                'plan_label' => $plan->label,
                'duration_text' => $plan->duration,
                'price' => (int) $plan->price,
                'promotion' => $promotion['snapshot'] ?? null,
                'captured_at' => now()->toIso8601String(),
            ],
        ];
    }
}
