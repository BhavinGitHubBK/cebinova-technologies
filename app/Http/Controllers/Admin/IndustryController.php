<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Industry;
use App\Models\Solution;
use App\Services\Admin\ActivityLogger;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class IndustryController extends Controller
{
    public function index(Request $request): View { $industries = Industry::query()->withCount('solutions')->when($request->filled('q'), fn ($q) => $q->where('name', 'like', '%'.$request->q.'%'))->when($request->filled('status'), fn ($q) => $q->where('is_active', $request->status === 'active'))->when($request->filled('featured'), fn ($q) => $q->where('is_featured', $request->featured === 'yes'))->orderBy('sort_order')->orderBy('id')->paginate(20)->withQueryString(); return view('admin.industries.index', compact('industries')); }
    public function create(): View { return $this->form(new Industry); }
    public function edit(Industry $industry): View { $industry->load('solutions'); return $this->form($industry); }
    public function show(Industry $industry): View { $industry->load('solutions'); return view('admin.industries.show', compact('industry')); }
    public function store(Request $request): RedirectResponse { $industry = Industry::query()->create($this->validated($request)); $this->sync($industry, $request); ActivityLogger::log('create', 'industries', $industry, 'Industry created'); return redirect()->route('admin.industries.index')->with('success', 'Industry created.'); }
    public function update(Request $request, Industry $industry): RedirectResponse { $oldSlug = $industry->slug; $industry->update($this->validated($request, $industry)); $this->sync($industry, $request); ActivityLogger::log('update', 'industries', $industry, $oldSlug === $industry->slug ? 'Industry updated' : "Industry slug changed from {$oldSlug} to {$industry->slug}"); return redirect()->route('admin.industries.index')->with('success', 'Industry updated.'); }
    public function destroy(Industry $industry): RedirectResponse { $industry->delete(); ActivityLogger::log('delete', 'industries', $industry, 'Industry archived'); return redirect()->route('admin.industries.index')->with('success', 'Industry archived.'); }
    private function form(Industry $industry): View { return view('admin.industries.form', ['industry' => $industry, 'solutions' => Solution::query()->orderBy('sort_order')->get()]); }
    private function validated(Request $request, ?Industry $industry = null): array { $data = $request->validate(['name' => ['required', 'string', 'max:190'], 'slug' => ['nullable', 'regex:/^[a-z0-9]+(?:-[a-z0-9]+)*$/', Rule::unique('industries')->ignore($industry)], 'group' => ['nullable', 'string', 'max:80'], 'icon' => ['nullable', 'string', 'max:60'], 'short_description' => ['nullable', 'string', 'max:500'], 'description' => ['nullable', 'string'], 'hero_title' => ['nullable', 'string', 'max:190'], 'hero_subtitle' => ['nullable', 'string'], 'image' => ['nullable', 'string', 'max:255'], 'sort_order' => ['nullable', 'integer', 'min:0'], 'is_featured' => ['sometimes', 'boolean'], 'is_active' => ['sometimes', 'boolean'], 'seo_title' => ['nullable', 'string', 'max:190'], 'seo_description' => ['nullable', 'string', 'max:500']]); $data['slug'] = $data['slug'] ?: ($industry?->slug ?: Str::slug($data['name'])); $data['is_featured'] = $request->boolean('is_featured'); $data['is_active'] = $request->boolean('is_active', true); $data['sort_order'] = (int) ($data['sort_order'] ?? 0); return $data; }
    private function sync(Industry $industry, Request $request): void { $request->validate(['solution_ids' => ['nullable', 'array'], 'solution_ids.*' => ['integer', Rule::exists('solutions', 'id')]]); $industry->solutions()->sync(collect($request->input('solution_ids', []))->values()->mapWithKeys(fn ($id, $index) => [$id => ['sort_order' => $index]])->all()); }
}
