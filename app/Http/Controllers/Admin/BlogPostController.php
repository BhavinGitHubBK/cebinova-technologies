<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\BlogCategory;
use App\Models\BlogPost;
use App\Services\Admin\ActivityLogger;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;
use Illuminate\View\View;

class BlogPostController extends Controller
{
    public function index(Request $request): View
    {
        $posts = BlogPost::query()
            ->with('category')
            ->when($request->filled('q'), fn ($q) => $q->where('title', 'like', '%'.$request->q.'%'))
            ->when($request->filled('status'), fn ($q) => $q->where('status', $request->status))
            ->latest('updated_at')
            ->paginate(20)
            ->withQueryString();

        return view('admin.blog-posts.index', compact('posts'));
    }

    public function create(): View
    {
        return view('admin.blog-posts.form', [
            'post' => new BlogPost(['status' => 'draft', 'author_id' => Auth::id()]),
            'categories' => BlogCategory::query()->orderBy('sort_order')->orderBy('name')->get(),
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $data = $this->validated($request);
        $post = BlogPost::query()->create($data);
        ActivityLogger::log('create', 'blog-posts', $post, 'Blog post created');

        return redirect()->route('admin.blog-posts.index')->with('success', 'Post created.');
    }

    public function show(BlogPost $blogPost): View
    {
        $blogPost->load(['category', 'author']);

        return view('admin.blog-posts.show', ['post' => $blogPost]);
    }

    public function edit(BlogPost $blogPost): View
    {
        return view('admin.blog-posts.form', [
            'post' => $blogPost,
            'categories' => BlogCategory::query()->orderBy('sort_order')->orderBy('name')->get(),
        ]);
    }

    public function update(Request $request, BlogPost $blogPost): RedirectResponse
    {
        $blogPost->update($this->validated($request, $blogPost));
        ActivityLogger::log('update', 'blog-posts', $blogPost, 'Blog post updated');

        return redirect()->route('admin.blog-posts.index')->with('success', 'Post updated.');
    }

    public function destroy(BlogPost $blogPost): RedirectResponse
    {
        $blogPost->delete();
        ActivityLogger::log('delete', 'blog-posts', $blogPost, 'Blog post deleted');

        return redirect()->route('admin.blog-posts.index')->with('success', 'Post deleted.');
    }

    private function validated(Request $request, ?BlogPost $post = null): array
    {
        $data = $request->validate([
            'blog_category_id' => ['nullable', 'exists:blog_categories,id'],
            'author_id' => ['nullable', 'exists:users,id'],
            'title' => ['required', 'string', 'max:190'],
            'slug' => ['nullable', 'string', 'max:190', 'unique:blog_posts,slug,'.($post?->id ?? 'NULL')],
            'excerpt' => ['nullable', 'string', 'max:500'],
            'content' => ['nullable', 'string'],
            'featured_image' => ['nullable', 'string', 'max:255'],
            'status' => ['required', 'in:draft,published,scheduled'],
            'published_at' => ['nullable', 'date'],
            'seo_title' => ['nullable', 'string', 'max:190'],
            'seo_description' => ['nullable', 'string', 'max:500'],
            'canonical_url' => ['nullable', 'string', 'max:255'],
            'og_image' => ['nullable', 'string', 'max:255'],
            'is_featured' => ['sometimes', 'boolean'],
        ]);

        $data['slug'] = $data['slug'] ?: Str::slug($data['title']);
        $data['author_id'] = $data['author_id'] ?: Auth::id();
        $data['is_featured'] = $request->boolean('is_featured');
        if ($data['status'] === 'published' && empty($data['published_at'])) {
            $data['published_at'] = now();
        }

        return $data;
    }
}
