<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\BlogCategory;
use App\Services\Admin\ActivityLogger;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\View\View;

class BlogCategoryController extends Controller
{
    public function index(Request $request): View
    {
        $categories = BlogCategory::query()
            ->withCount('posts')
            ->when($request->filled('q'), fn ($q) => $q->where('name', 'like', '%'.$request->q.'%'))
            ->orderBy('sort_order')
            ->orderBy('name')
            ->paginate(20)
            ->withQueryString();

        return view('admin.blog-categories.index', compact('categories'));
    }

    public function create(): View
    {
        return view('admin.blog-categories.form', ['category' => new BlogCategory]);
    }

    public function store(Request $request): RedirectResponse
    {
        $data = $this->validated($request);
        $category = BlogCategory::query()->create($data);
        ActivityLogger::log('create', 'blog-categories', $category, 'Blog category created');

        return redirect()->route('admin.blog-categories.index')->with('success', 'Category created.');
    }

    public function show(BlogCategory $blogCategory): View
    {
        $blogCategory->loadCount('posts');

        return view('admin.blog-categories.show', ['category' => $blogCategory]);
    }

    public function edit(BlogCategory $blogCategory): View
    {
        return view('admin.blog-categories.form', ['category' => $blogCategory]);
    }

    public function update(Request $request, BlogCategory $blogCategory): RedirectResponse
    {
        $blogCategory->update($this->validated($request, $blogCategory));
        ActivityLogger::log('update', 'blog-categories', $blogCategory, 'Blog category updated');

        return redirect()->route('admin.blog-categories.index')->with('success', 'Category updated.');
    }

    public function destroy(BlogCategory $blogCategory): RedirectResponse
    {
        $blogCategory->delete();
        ActivityLogger::log('delete', 'blog-categories', $blogCategory, 'Blog category deleted');

        return redirect()->route('admin.blog-categories.index')->with('success', 'Category deleted.');
    }

    private function validated(Request $request, ?BlogCategory $category = null): array
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:190'],
            'slug' => ['nullable', 'string', 'max:190', 'unique:blog_categories,slug,'.($category?->id ?? 'NULL')],
            'sort_order' => ['nullable', 'integer', 'min:0'],
            'is_active' => ['sometimes', 'boolean'],
        ]);

        $data['slug'] = $data['slug'] ?: Str::slug($data['name']);
        $data['is_active'] = $request->boolean('is_active', true);
        $data['sort_order'] = (int) ($data['sort_order'] ?? 0);

        return $data;
    }
}
