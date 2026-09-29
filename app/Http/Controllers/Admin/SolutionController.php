<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Industry;
use App\Models\Service;
use App\Models\Solution;
use App\Services\Admin\ActivityLogger;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class SolutionController extends Controller
{
    public function index(Request $request): View
    {
        $solutions = Solution::query()->withCount(['services', 'industries'])
            ->when($request->filled('q'), fn ($q) => $q->where('name', 'like', '%'.$request->q.'%'))
            ->when($request->filled('status'), fn ($q) => $q->where('is_active', $request->status === 'active'))
            ->when($request->filled('featured'), fn ($q) => $q->where('is_featured', $request->featured === 'yes'))
            ->orderBy('sort_order')->orderBy('id')->paginate(20)->withQueryString();
        return view('admin.solutions.index', compact('solutions'));
    }

    public function create(): View { return $this->form(new Solution); }
    public function edit(Solution $solution): View { $solution->load('sections.items', 'faqs', 'services', 'industries'); return $this->form($solution); }
    public function show(Solution $solution): View { $solution->load('sections.items', 'faqs', 'services', 'industries'); return view('admin.solutions.show', compact('solution')); }

    public function store(Request $request): RedirectResponse
    {
        $solution = DB::transaction(function () use ($request) { $solution = Solution::query()->create($this->validated($request)); $this->sync($solution, $request); return $solution; });
        ActivityLogger::log('create', 'solutions', $solution, 'Solution created');
        return redirect()->route('admin.solutions.index')->with('success', 'Solution created.');
    }

    public function update(Request $request, Solution $solution): RedirectResponse
    {
        $oldSlug = $solution->slug;
        DB::transaction(function () use ($request, $solution) { $solution->update($this->validated($request, $solution)); $this->sync($solution, $request); });
        ActivityLogger::log('update', 'solutions', $solution, $oldSlug === $solution->slug ? 'Solution updated' : "Solution slug changed from {$oldSlug} to {$solution->slug}");
        return redirect()->route('admin.solutions.index')->with('success', 'Solution updated.');
    }

    public function destroy(Solution $solution): RedirectResponse
    {
        $solution->delete(); ActivityLogger::log('delete', 'solutions', $solution, 'Solution archived');
        return redirect()->route('admin.solutions.index')->with('success', 'Solution archived.');
    }

    private function form(Solution $solution): View
    {
        return view('admin.solutions.form', ['solution' => $solution, 'services' => Service::query()->orderBy('sort_order')->get(), 'industries' => Industry::query()->orderBy('sort_order')->get()]);
    }

    private function validated(Request $request, ?Solution $solution = null): array
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:190'], 'slug' => ['nullable', 'regex:/^[a-z0-9]+(?:-[a-z0-9]+)*$/', Rule::unique('solutions')->ignore($solution)],
            'category' => ['nullable', 'string', 'max:120'], 'icon' => ['nullable', 'string', 'max:60'], 'badge' => ['nullable', 'string', 'max:80'],
            'short_description' => ['nullable', 'string', 'max:500'], 'description' => ['nullable', 'string'], 'hero_title' => ['nullable', 'string', 'max:190'], 'hero_subtitle' => ['nullable', 'string'],
            'audience' => ['nullable', 'string'], 'outcome' => ['nullable', 'string'], 'image' => ['nullable', 'string', 'max:255'], 'demo_slug' => ['nullable', 'string', 'max:120'],
            'business_type' => ['nullable', 'string', 'max:120'], 'cta_label' => ['nullable', 'string', 'max:120'], 'next_route' => ['nullable', 'string', 'max:120'], 'next_param' => ['nullable', 'string', 'max:190'],
            'sort_order' => ['nullable', 'integer', 'min:0'], 'is_featured' => ['sometimes', 'boolean'], 'is_active' => ['sometimes', 'boolean'], 'seo_title' => ['nullable', 'string', 'max:190'], 'seo_description' => ['nullable', 'string', 'max:500'],
        ]);
        $data['slug'] = $data['slug'] ?: ($solution?->slug ?: Str::slug($data['name']));
        $data['is_featured'] = $request->boolean('is_featured'); $data['is_active'] = $request->boolean('is_active', true); $data['sort_order'] = (int) ($data['sort_order'] ?? 0);
        return $data;
    }

    private function sync(Solution $solution, Request $request): void
    {
        $request->validate(['features_text' => ['nullable', 'string', 'max:20000'], 'modules_text' => ['nullable', 'string', 'max:20000'], 'benefits_text' => ['nullable', 'string', 'max:20000'], 'process_text' => ['nullable', 'string', 'max:20000'], 'business_problem' => ['nullable', 'string', 'max:5000'], 'overview' => ['nullable', 'string', 'max:5000'], 'faqs_text' => ['nullable', 'string', 'max:30000'], 'service_ids' => ['nullable', 'array'], 'service_ids.*' => ['integer', Rule::exists('services', 'id')], 'industry_ids' => ['nullable', 'array'], 'industry_ids.*' => ['integer', Rule::exists('industries', 'id')]]);
        foreach (['features', 'modules', 'benefits', 'process'] as $order => $type) {
            $items = $this->lines((string) $request->input($type.'_text'));
            $section = $solution->sections()->firstOrNew(['type' => $type]);
            $section->fill(['title' => ucfirst($type), 'is_active' => $items !== [], 'sort_order' => ($order + 1) * 10])->save(); $section->items()->delete();
            foreach ($items as $index => $item) $section->items()->create(array_merge($item, ['is_active' => true, 'sort_order' => $index]));
        }
        foreach (['business_problem' => 'Business problem', 'overview' => 'Solution overview'] as $type => $title) $solution->sections()->updateOrCreate(['type' => $type], ['title' => $title, 'content' => $request->input($type), 'is_active' => $request->filled($type), 'sort_order' => $type === 'business_problem' ? 1 : 2]);
        $solution->faqs()->delete(); foreach ($this->lines((string) $request->input('faqs_text')) as $index => $faq) if ($faq['description']) $solution->faqs()->create(['page' => 'solution', 'question' => $faq['title'], 'answer' => $faq['description'], 'is_active' => true, 'sort_order' => $index]);
        $solution->services()->sync(collect($request->input('service_ids', []))->values()->mapWithKeys(fn ($id, $index) => [$id => ['sort_order' => $index]])->all());
        $solution->industries()->sync(collect($request->input('industry_ids', []))->values()->mapWithKeys(fn ($id, $index) => [$id => ['sort_order' => $index]])->all());
    }

    private function lines(string $value): array { return collect(preg_split('/\r\n|\r|\n/', $value))->map(fn ($line) => trim($line))->filter()->map(function ($line) { [$title, $description] = array_pad(array_map('trim', explode('|', $line, 2)), 2, null); return compact('title', 'description'); })->values()->all(); }
}
