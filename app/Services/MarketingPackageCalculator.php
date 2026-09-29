<?php

namespace App\Services;

use App\Models\Package;
use App\Models\PackagePlan;
use Illuminate\Validation\ValidationException;

class MarketingPackageCalculator
{
    public function calculate(int $packageId, int $planId): array
    {
        $package = Package::query()->marketing()->active()->find($packageId);
        $plan = PackagePlan::query()->active()->where('package_id', $packageId)->find($planId);

        if (! $package || ! $plan) {
            throw ValidationException::withMessages([
                'marketing_plan_id' => 'The selected marketing package or duration is unavailable.',
            ]);
        }

        return [
            'package_id' => $package->id,
            'plan_id' => $plan->id,
            'category' => $package->service_label ?: $package->name,
            'duration' => $plan->label,
            'price' => (int) $plan->price,
            'snapshot' => [
                'package_id' => $package->id,
                'package_name' => $package->name,
                'plan_id' => $plan->id,
                'plan_label' => $plan->label,
                'duration_text' => $plan->duration,
                'price' => (int) $plan->price,
                'captured_at' => now()->toIso8601String(),
            ],
        ];
    }
}
