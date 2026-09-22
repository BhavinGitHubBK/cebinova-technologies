<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Package;
use App\Models\PackagePlan;
use App\Services\Admin\ActivityLogger;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\View\View;

class PackageController extends Controller
{
    public function index(Request $request): View
    {
        $packages = Package::query()
            ->withCount('plans')
            ->when($request->filled('category'), fn ($q) => $q->where('category', $request->category))
            ->when($request->filled('q'), fn ($q) => $q->where('name', 'like', '%'.$request->q.'%'))
            ->orderBy('sort_order')
            ->paginate(20)
            ->withQueryString();

        return view('admin.packages.index', [
            'packages' => $packages,
            'categories' => Package::CATEGORIES,
        ]);
    }

    public function create(): View
    {
        return view('admin.packages.form', [
            'package' => new Package,
            'categories' => Package::CATEGORIES,
            'plans' => [],
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $data = $this->validatedPackage($request);
        $plans = $this->validatedPlans($request);

        $package = DB::transaction(function () use ($data, $plans) {
            $package = Package::query()->create($data);
            foreach ($plans as $i => $plan) {
                $package->plans()->create(array_merge($plan, ['sort_order' => $i]));
            }

            return $package;
        });

        ActivityLogger::log('create', 'packages', $package, 'Package created');

        return redirect()->route('admin.packages.index')->with('success', 'Package created.');
    }

    public function show(Package $package): View
    {
        $package->load('plans');

        return view('admin.packages.show', compact('package'));
    }

    public function edit(Package $package): View
    {
        $package->load('plans');

        return view('admin.packages.form', [
            'package' => $package,
            'categories' => Package::CATEGORIES,
            'plans' => $package->plans,
        ]);
    }

    public function update(Request $request, Package $package): RedirectResponse
    {
        $data = $this->validatedPackage($request, $package);
        $plans = $this->validatedPlans($request);

        DB::transaction(function () use ($package, $data, $plans) {
            $package->update($data);
            $package->plans()->delete();
            foreach ($plans as $i => $plan) {
                $package->plans()->create(array_merge($plan, ['sort_order' => $i]));
            }
        });

        ActivityLogger::log('update', 'packages', $package, 'Package updated');

        return redirect()->route('admin.packages.index')->with('success', 'Package updated.');
    }

    public function destroy(Package $package): RedirectResponse
    {
        $package->delete();
        ActivityLogger::log('delete', 'packages', $package, 'Package deleted');

        return redirect()->route('admin.packages.index')->with('success', 'Package deleted.');
    }

    private function validatedPackage(Request $request, ?Package $package = null): array
    {
        $data = $request->validate([
            'category' => ['required', 'in:'.implode(',', array_keys(Package::CATEGORIES))],
            'name' => ['required', 'string', 'max:190'],
            'slug' => ['nullable', 'string', 'max:190', 'unique:packages,slug,'.($package?->id ?? 'NULL')],
            'key' => ['nullable', 'string', 'max:80'],
            'heading' => ['nullable', 'string', 'max:190'],
            'short_description' => ['nullable', 'string'],
            'subheading' => ['nullable', 'string'],
            'teaser' => ['nullable', 'string', 'max:255'],
            'best_if' => ['nullable', 'string', 'max:255'],
            'service_label' => ['nullable', 'string', 'max:120'],
            'badge' => ['nullable', 'string', 'max:80'],
            'cta_label' => ['nullable', 'string', 'max:120'],
            'secondary_cta' => ['nullable', 'string', 'max:120'],
            'why' => ['nullable', 'string'],
            'includes_text' => ['nullable', 'string'],
            'is_highlighted' => ['sometimes', 'boolean'],
            'is_active' => ['sometimes', 'boolean'],
            'sort_order' => ['nullable', 'integer', 'min:0'],
            'active_from' => ['nullable', 'date'],
            'active_until' => ['nullable', 'date', 'after_or_equal:active_from'],
            'seo_title' => ['nullable', 'string', 'max:190'],
            'seo_description' => ['nullable', 'string', 'max:500'],
        ]);

        $data['slug'] = $data['slug'] ?: Str::slug($data['name']);
        $data['includes'] = collect(preg_split('/\r\n|\r|\n/', (string) ($data['includes_text'] ?? '')))
            ->map(fn ($l) => trim($l))->filter()->values()->all();
        unset($data['includes_text']);
        $data['is_highlighted'] = $request->boolean('is_highlighted');
        $data['is_active'] = $request->boolean('is_active', true);
        $data['sort_order'] = (int) ($data['sort_order'] ?? 0);

        return $data;
    }

    /**
     * @return list<array<string, mixed>>
     */
    private function validatedPlans(Request $request): array
    {
        $request->validate([
            'plans' => ['nullable', 'array'],
            'plans.*.label' => ['nullable', 'string', 'max:80'],
            'plans.*.price' => ['nullable', 'integer', 'min:0'],
            'plans.*.original_price' => ['nullable', 'integer', 'min:0'],
            'plans.*.key' => ['nullable', 'string', 'max:80'],
            'plans.*.duration' => ['nullable', 'string', 'max:80'],
            'plans.*.billing_duration' => ['nullable', 'string', 'max:80'],
            'plans.*.period' => ['nullable', 'string', 'max:80'],
            'plans.*.badge' => ['nullable', 'string', 'max:80'],
            'plans.*.cta' => ['nullable', 'string', 'max:120'],
            'plans.*.note' => ['nullable', 'string', 'max:500'],
            'plans.*.features_text' => ['nullable', 'string'],
            'plans.*.is_active' => ['sometimes', 'boolean'],
        ]);

        $plans = [];
        foreach ($request->input('plans', []) as $index => $plan) {
            if (! filled($plan['label'] ?? null)) {
                continue;
            }

            if (! is_numeric($plan['price'] ?? null)) {
                throw \Illuminate\Validation\ValidationException::withMessages([
                    "plans.{$index}.price" => 'Each plan with a label needs a price.',
                ]);
            }

            $features = collect(preg_split('/\r\n|\r|\n/', (string) ($plan['features_text'] ?? '')))
                ->map(fn ($l) => trim($l))->filter()->values()->all();
            $plans[] = [
                'key' => $plan['key'] ?? Str::slug($plan['label']),
                'label' => $plan['label'],
                'duration' => $plan['duration'] ?? null,
                'billing_duration' => $plan['billing_duration'] ?? null,
                'original_price' => $plan['original_price'] ?? null,
                'price' => (int) $plan['price'],
                'discount_percent' => null,
                'period' => $plan['period'] ?? null,
                'badge' => $plan['badge'] ?? null,
                'cta' => $plan['cta'] ?? null,
                'note' => $plan['note'] ?? null,
                'features' => $features,
                'is_active' => (bool) ($plan['is_active'] ?? true),
            ];
        }

        return $plans;
    }
}
