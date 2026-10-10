<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Service;
use App\Services\Admin\ActivityLogger;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class ServiceController extends Controller
{
    public function index(Request $request): View
    {
        $matched = Service::query()->when($request->filled('q'), function ($query) use ($request) {
            $term = '%'.$request->string('q').'%';
            $query->where(function ($query) use ($term) {
                $query->where('name', 'like', $term)->orWhere('slug', 'like', $term)->orWhere('category', 'like', $term);
            });
        });
        $counts = [
            'all' => (clone $matched)->count(),
            'active' => (clone $matched)->where('is_active', true)->count(),
            'inactive' => (clone $matched)->where('is_active', false)->count(),
        ];
        [$sort, $dir] = admin_sort($request, ['name', 'category', 'sort_order', 'is_active'], 'sort_order');
        $services = $matched
            ->when($request->filled('status'), fn ($query) => $query->where('is_active', $request->status === 'active'))
            ->orderBy($sort, $dir)
            ->orderBy('name')
            ->paginate(20)
            ->withQueryString();

        return view('admin.services.index', [
            'services' => $services,
            'counts' => $counts,
            'status' => $request->string('status')->toString(),
            'sort' => $sort,
            'dir' => $dir,
        ]);
    }

    public function create(): View
    {
        return view('admin.services.form', ['service' => new Service]);
    }

    public function store(Request $request): RedirectResponse
    {
        $data = $this->validated($request);
        $service = Service::query()->create($data);
        ActivityLogger::log('create', 'services', $service, 'Service created');

        return redirect()->route('admin.services.edit', $service)->with('success', 'Service created.');
    }

    public function show(Service $service): View
    {
        return view('admin.services.show', compact('service'));
    }

    public function edit(Service $service): View
    {
        return view('admin.services.form', compact('service'));
    }

    public function update(Request $request, Service $service): RedirectResponse
    {
        $service->update($this->validated($request, $service));
        ActivityLogger::log('update', 'services', $service, 'Service updated');

        return redirect()->route('admin.services.edit', $service)->with('success', 'Service updated.');
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
            'category' => ['required', 'string', 'max:120'],
            'icon' => ['nullable', 'string', 'max:60'],
            'short_description' => ['required', 'string', 'max:500'],
            'full_description' => ['nullable', 'string'],
            'featured_image' => ['nullable', 'string', 'max:255'],
            'features_text' => ['nullable', 'string'],
            'cta_label' => ['nullable', 'string', 'max:120'],
            'cta_url' => ['nullable', 'required_with:cta_label', 'string', 'max:255', admin_site_link()],
            'sort_order' => ['nullable', 'integer', 'min:0'],
            'is_featured' => ['sometimes', 'boolean'],
            'is_active' => ['sometimes', 'boolean'],
            'seo_title' => ['nullable', 'string', 'max:190'],
            'seo_description' => ['nullable', 'string', 'max:500'],
        ]);

        $data['slug'] = $data['slug'] ?: Str::slug($data['name']);
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
}
