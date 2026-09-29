<?php

namespace App\Services;

use App\Models\Package;
use App\Models\PricingOption;
use Illuminate\Validation\ValidationException;

class PricingCalculator
{
    public function calculate(int $packageId, array $optionIds = []): array
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
        $total = (float) $plan->price + $options->sum(fn ($option) => (float) $option->price);

        return [
            'package_id' => $package->id,
            'option_ids' => $options->pluck('id')->all(),
            'total' => $total,
            'snapshot' => [
                'package' => ['id' => $package->id, 'name' => $package->name, 'price' => (float) $plan->price],
                'options' => $options->map(fn ($option) => ['id' => $option->id, 'type' => $option->type, 'name' => $option->name, 'price' => (float) $option->price])->all(),
                'calculated_at' => now()->toIso8601String(),
            ],
        ];
    }
}
