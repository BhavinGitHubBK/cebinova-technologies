<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\PageSection;
use App\Services\Admin\ActivityLogger;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class PageSectionController extends Controller
{
    public function index(Request $request): View
    {
        $matched = PageSection::query()
            ->when($request->filled('section_page'), fn ($query) => $query->where('page', $request->section_page))
            ->when($request->filled('q'), function ($query) use ($request) {
                $term = '%'.$request->string('q').'%';
                $query->where(function ($query) use ($term) {
                    $query->where('key', 'like', $term)->orWhere('heading', 'like', $term);
                });
            });
        $counts = [
            'all' => (clone $matched)->count(),
            'active' => (clone $matched)->where('is_visible', true)->count(),
            'inactive' => (clone $matched)->where('is_visible', false)->count(),
        ];
        [$sort, $dir] = admin_sort($request, ['page', 'key', 'heading', 'is_visible'], 'page');
        $sections = $matched
            ->when($request->filled('status'), fn ($query) => $query->where('is_visible', $request->status === 'active'))
            ->orderBy($sort, $dir)
            ->orderBy('sort_order')
            ->paginate(20)
            ->withQueryString();

        return view('admin.page-sections.index', [
            'sections' => $sections,
            'counts' => $counts,
            'status' => $request->string('status')->toString(),
            'pages' => PageSection::query()->distinct()->orderBy('page')->pluck('page'),
            'sort' => $sort,
            'dir' => $dir,
        ]);
    }

    public function create(): View
    {
        return view('admin.page-sections.form', ['section' => new PageSection(['page' => 'home', 'is_visible' => true])]);
    }

    public function store(Request $request): RedirectResponse
    {
        $data = $this->validated($request);
        $section = PageSection::query()->create($data);
        ActivityLogger::log('create', 'page-sections', $section, 'Page section created');

        return redirect()->route('admin.page-sections.edit', $section)->with('success', 'Section created.');
    }

    public function show(PageSection $pageSection): View
    {
        return view('admin.page-sections.show', ['section' => $pageSection]);
    }

    public function edit(PageSection $pageSection): View
    {
        return view('admin.page-sections.form', ['section' => $pageSection]);
    }

    public function update(Request $request, PageSection $pageSection): RedirectResponse
    {
        $pageSection->update($this->validated($request, $pageSection));
        ActivityLogger::log('update', 'page-sections', $pageSection, 'Page section updated');

        return redirect()->route('admin.page-sections.edit', $pageSection)->with('success', 'Section updated.');
    }

    public function destroy(PageSection $pageSection): RedirectResponse
    {
        $pageSection->delete();
        ActivityLogger::log('delete', 'page-sections', $pageSection, 'Page section deleted');

        return redirect()->route('admin.page-sections.index')->with('success', 'Section deleted.');
    }

    private function validated(Request $request, ?PageSection $section = null): array
    {
        $data = $request->validate([
            'page' => ['required', 'string', 'max:60'],
            'key' => [
                'required', 'string', 'max:60',
                Rule::unique('page_sections', 'key')
                    ->where(fn ($q) => $q->where('page', $request->input('page')))
                    ->ignore($section?->id),
            ],
            'heading' => ['nullable', 'string', 'max:255'],
            'subheading' => ['nullable', 'string', 'max:255'],
            'body' => ['nullable', 'string'],
            'image_path' => ['nullable', 'string', 'max:255'],
            'cta_label' => ['nullable', 'string', 'max:120'],
            'cta_url' => ['nullable', 'required_with:cta_label', 'string', 'max:255', admin_site_link()],
            'secondary_cta_label' => ['nullable', 'string', 'max:120'],
            'secondary_cta_url' => ['nullable', 'required_with:secondary_cta_label', 'string', 'max:255', admin_site_link()],
            'settings' => ['nullable', 'json'],
            'sort_order' => ['nullable', 'integer', 'min:0'],
            'is_visible' => ['sometimes', 'boolean'],
        ]);

        $data['settings'] = $request->filled('settings') ? (json_decode($request->input('settings'), true) ?? []) : [];
        $data['is_visible'] = $request->boolean('is_visible', true);
        $data['sort_order'] = (int) ($data['sort_order'] ?? 0);

        return $data;
    }
}
