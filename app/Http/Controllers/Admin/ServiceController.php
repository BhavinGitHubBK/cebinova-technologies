<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Service;
use App\Services\Admin\ActivityLogger;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;
use Illuminate\Support\Str;
use Illuminate\View\View;

class ServiceController extends Controller
{
    public function index(Request $request): View
    {
        $services = Service::query()
            ->when($request->filled('q'), fn ($q) => $q->where('name', 'like', '%'.$request->q.'%'))
            ->when($request->filled('status'), fn ($q) => $q->where('is_active', $request->status === 'active'))
            ->when($request->filled('featured'), fn ($q) => $q->where('is_featured', $request->featured === 'yes'))
            ->orderBy('sort_order')
            ->orderBy('name')
            ->paginate(20)
            ->withQueryString();

        return view('admin.services.index', compact('services'));
    }

    public function create(): View
    {
        return view('admin.services.form', ['service' => new Service]);
    }

    public function store(Request $request): RedirectResponse
    {
        $data = $this->validated($request);
        $service = DB::transaction(function () use ($request, $data) {
            $service = Service::query()->create($data);
            $this->syncContent($service, $request);

            return $service;
        });
        ActivityLogger::log('create', 'services', $service, 'Service created');

        return redirect()->route('admin.services.index')->with('success', 'Service created.');
    }

    public function show(Service $service): View
    {
        $service->load('sections.items', 'faqs');

        return view('admin.services.show', compact('service'));
    }

    public function edit(Service $service): View
    {
        $service->load('sections.items', 'faqs');

        return view('admin.services.form', compact('service'));
    }

    public function update(Request $request, Service $service): RedirectResponse
    {
        $originalSlug = $service->slug;
        DB::transaction(function () use ($request, $service) {
            $service->update($this->validated($request, $service));
            $this->syncContent($service, $request);
        });
        ActivityLogger::log('update', 'services', $service, 'Service updated');
        if ($originalSlug !== $service->slug) {
            ActivityLogger::log('update', 'services', $service, 'Service slug changed from '.$originalSlug.' to '.$service->slug);
        }

        return redirect()->route('admin.services.index')->with('success', 'Service updated.');
    }

    public function destroy(Service $service): RedirectResponse
    {
        $service->delete();
        ActivityLogger::log('delete', 'services', $service, 'Service deleted');

        return redirect()->route('admin.services.index')->with('success', 'Service deleted.');
    }

    private function validated(Request $request, ?Service $service = null): array
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:190'],
            'slug' => ['nullable', 'string', 'max:190', 'regex:/^[a-z0-9]+(?:-[a-z0-9]+)*$/', Rule::unique('services', 'slug')->ignore($service)],
            'category' => ['nullable', 'string', 'max:120'],
            'icon' => ['nullable', 'string', 'max:60'],
            'short_description' => ['nullable', 'string', 'max:500'],
            'full_description' => ['nullable', 'string'],
            'featured_image' => ['nullable', 'string', 'max:255'],
            'hero_title' => ['nullable', 'string', 'max:190'],
            'hero_subtitle' => ['nullable', 'string'],
            'badge' => ['nullable', 'string', 'max:80'],
            'audience' => ['nullable', 'string'],
            'outcome' => ['nullable', 'string'],
            'next_label' => ['nullable', 'string', 'max:120'],
            'next_route' => ['nullable', 'string', 'max:120'],
            'next_param' => ['nullable', 'string', 'max:190'],
            'features_text' => ['nullable', 'string'],
            'cta_label' => ['nullable', 'string', 'max:120'],
            'cta_url' => ['nullable', 'string', 'max:255'],
            'sort_order' => ['nullable', 'integer', 'min:0'],
            'is_featured' => ['sometimes', 'boolean'],
            'is_active' => ['sometimes', 'boolean'],
            'seo_title' => ['nullable', 'string', 'max:190'],
            'seo_description' => ['nullable', 'string', 'max:500'],
        ]);

        $data['slug'] = $data['slug'] ?: ($service?->slug ?: Str::slug($data['name']));
        $data['features'] = collect(preg_split('/\r\n|\r|\n/', (string) ($data['features_text'] ?? '')))
            ->map(fn ($line) => trim($line))
            ->filter()
            ->values()
            ->all();
        unset($data['features_text']);
        $data['is_featured'] = $request->boolean('is_featured');
        $data['is_active'] = $request->boolean('is_active', true);
        $data['sort_order'] = (int) ($data['sort_order'] ?? 0);

        return $data;
    }

    private function syncContent(Service $service, Request $request): void
    {
        $request->validate([
            'features_text' => ['nullable', 'string', 'max:20000'],
            'benefits_text' => ['nullable', 'string', 'max:20000'],
            'use_cases_text' => ['nullable', 'string', 'max:20000'],
            'technologies_text' => ['nullable', 'string', 'max:20000'],
            'process_title' => ['nullable', 'string', 'max:190'],
            'process_subtitle' => ['nullable', 'string', 'max:500'],
            'process_text' => ['nullable', 'string', 'max:20000'],
            'faqs_text' => ['nullable', 'string', 'max:30000'],
        ]);
        $types = [
            'features' => ['title' => 'What this includes', 'input' => 'features_text', 'descriptions' => false],
            'benefits' => ['title' => 'Benefits', 'input' => 'benefits_text', 'descriptions' => true],
            'use_cases' => ['title' => 'Business use cases', 'input' => 'use_cases_text', 'descriptions' => true],
            'technologies' => ['title' => 'Technologies', 'input' => 'technologies_text', 'descriptions' => true],
            'process' => ['title' => $request->input('process_title', 'A simple path from first chat to launch.'), 'input' => 'process_text', 'descriptions' => true],
        ];

        foreach ($types as $order => $definition) {
            $items = $this->lines((string) $request->input($definition['input']), $definition['descriptions']);
            $section = $service->sections()->firstOrNew(['type' => $order]);
            $section->fill([
                'title' => $definition['title'],
                'subtitle' => $order === 'process' ? $request->input('process_subtitle') : null,
                'is_active' => $items !== [],
                'sort_order' => array_search($order, array_keys($types), true) * 10 + 10,
            ])->save();
            $section->items()->delete();
            foreach ($items as $index => $item) {
                $section->items()->create(array_merge($item, ['is_active' => true, 'sort_order' => $index]));
            }
        }

        $service->faqs()->delete();
        foreach ($this->lines((string) $request->input('faqs_text'), true) as $index => $faq) {
            if (! filled($faq['description'])) {
                continue;
            }
            $service->faqs()->create([
                'page' => 'service',
                'question' => $faq['title'],
                'answer' => $faq['description'],
                'is_active' => true,
                'sort_order' => $index,
            ]);
        }
    }

    private function lines(string $value, bool $withDescriptions): array
    {
        return collect(preg_split('/\r\n|\r|\n/', $value))
            ->map(fn ($line) => trim($line))
            ->filter()
            ->map(function ($line) use ($withDescriptions) {
                [$title, $description] = array_pad(array_map('trim', explode('|', $line, 2)), 2, null);

                return ['title' => $title, 'description' => $withDescriptions ? $description : null];
            })->values()->all();
    }
}
