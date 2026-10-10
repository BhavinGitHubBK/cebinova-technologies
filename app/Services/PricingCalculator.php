<?php

namespace App\Services;

use App\Models\Package;
use App\Models\PricingOption;
use App\Services\Promotions\PromotionEngine;
use App\Support\Promotions\Money;
use Illuminate\Validation\ValidationException;

class PricingCalculator
{
    public function calculate(int $packageId, array $optionIds = [], ?string $coupon = null, ?string $customerKey = null): array
    {
        $package = Package::query()->website()->active()->with(['plans' => fn ($query) => $query->active()])->find($packageId);
        if (! $package || ! $package->plans->first()) {
            throw ValidationException::withMessages(['pricing_package_id' => 'The selected pricing package is unavailable.']);
        }

        $ids = collect($optionIds)->map(fn ($id) => (int) $id)->filter()->unique()->values();
        $options = PricingOption::query()->active()->whereIn('id', $ids)->ordered()->get();
        if ($options->count() !== $ids->count()) {
            throw ValidationException::withMessages(['pricing_option_ids' => 'One or more pricing options are unavailable.']);
        }

        $duplicateTypes = $options->whereIn('type', ['domain', 'hosting'])->groupBy('type')->filter(fn ($group) => $group->count() > 1);
        if ($duplicateTypes->isNotEmpty()) {
            throw ValidationException::withMessages(['pricing_option_ids' => 'Choose only one domain and one hosting option.']);
        }

        $plan = $package->plans->first();
        $lines = [[
            'type' => 'website_package',
            'id' => $package->id,
            'label' => $package->name,
            'amount' => $plan->price,
        ]];
        foreach ($options as $option) {
            $lines[] = [
                'type' => 'pricing_option',
                'id' => $option->id,
                'option_type' => $option->type,
                'label' => $option->name,
                'amount' => $option->price,
            ];
        }

        $base = array_sum(array_map(fn ($line) => Money::paise($line['amount']), $lines));
        $promotion = $this->promotion($lines, $coupon, $customerKey);
        $total = $promotion['discounted_subtotal'] ?? Money::format($base);

        return [
            'package_id' => $package->id,
            'option_ids' => $options->pluck('id')->all(),
            'total' => (float) $total,
            'promotion' => $promotion,
            'snapshot' => [
                'package' => ['id' => $package->id, 'name' => $package->name, 'price' => (float) $plan->price],
                'options' => $options->map(fn ($option) => ['id' => $option->id, 'type' => $option->type, 'name' => $option->name, 'price' => (float) $option->price])->all(),
                'promotion' => $promotion['snapshot'] ?? null,
                'calculated_at' => now()->toIso8601String(),
            ],
        ];
    }

    /**
     * @param  list<array<string, mixed>>  $lines
     * @return array<string, mixed>|null
     */
    private function promotion(array $lines, ?string $coupon, ?string $customerKey): ?array
    {
        try {
            return app(PromotionEngine::class)->quote($lines, $coupon, $customerKey);
        } catch (ValidationException $exception) {
            throw $exception;
        } catch (\Throwable $exception) {
            report($exception);

            return null;
        }
    }
}
