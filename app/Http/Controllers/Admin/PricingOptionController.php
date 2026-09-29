<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\PricingOption;
use App\Services\Admin\ActivityLogger;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class PricingOptionController extends Controller
{
    public function index(Request $request): View
    {
        return view('admin.pricing-options.index', [
            'options' => PricingOption::query()
                ->when($request->filled('type'), fn ($query) => $query->where('type', $request->string('type')))
                ->ordered()->paginate(30)->withQueryString(),
            'types' => PricingOption::TYPES,
        ]);
    }

    public function create(): View
    {
        return view('admin.pricing-options.form', ['option' => new PricingOption, 'types' => PricingOption::TYPES]);
    }

    public function store(Request $request): RedirectResponse
    {
        $option = PricingOption::query()->create($this->validated($request));
        ActivityLogger::log('create', 'pricing_options', $option, 'Pricing option created');

        return redirect()->route('admin.pricing-options.index')->with('success', 'Pricing option created.');
    }

    public function edit(PricingOption $pricingOption): View
    {
        return view('admin.pricing-options.form', ['option' => $pricingOption, 'types' => PricingOption::TYPES]);
    }

    public function update(Request $request, PricingOption $pricingOption): RedirectResponse
    {
        $pricingOption->update($this->validated($request, $pricingOption));
        ActivityLogger::log('update', 'pricing_options', $pricingOption, 'Pricing option updated');

        return redirect()->route('admin.pricing-options.index')->with('success', 'Pricing option updated.');
    }

    public function destroy(PricingOption $pricingOption): RedirectResponse
    {
        $pricingOption->delete();
        ActivityLogger::log('delete', 'pricing_options', $pricingOption, 'Pricing option archived');

        return back()->with('success', 'Pricing option archived.');
    }

    private function validated(Request $request, ?PricingOption $option = null): array
    {
        $data = $request->validate([
            'type' => ['required', Rule::in(array_keys(PricingOption::TYPES))],
            'name' => ['required', 'string', 'max:190'],
            'slug' => ['nullable', 'string', 'max:190', Rule::unique('pricing_options')->ignore($option)],
            'description' => ['nullable', 'string', 'max:2000'],
            'price' => ['required', 'numeric', 'min:0', 'max:9999999999.99'],
            'billing_period' => ['required', Rule::in(array_keys(PricingOption::BILLING_PERIODS))],
            'sort_order' => ['nullable', 'integer', 'min:0'],
            'is_recommended' => ['sometimes', 'boolean'],
            'is_active' => ['sometimes', 'boolean'],
        ]);
        $data['slug'] = ($data['slug'] ?? null) ?: Str::slug($data['name']);
        $data['is_recommended'] = $request->boolean('is_recommended');
        $data['is_active'] = $request->boolean('is_active');
        $data['sort_order'] = (int) ($data['sort_order'] ?? 0);

        return $data;
    }
}
