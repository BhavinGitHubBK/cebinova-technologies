<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Project;
use App\Services\Admin\ActivityLogger;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\View\View;

class ProjectController extends Controller
{
    public function index(Request $request): View
    {
        $projects = Project::query()
            ->when($request->filled('q'), fn ($q) => $q->where('title', 'like', '%'.$request->q.'%'))
            ->orderBy('sort_order')
            ->orderBy('title')
            ->paginate(20)
            ->withQueryString();

        return view('admin.projects.index', compact('projects'));
    }

    public function create(): View
    {
        return view('admin.projects.form', ['project' => new Project]);
    }

    public function store(Request $request): RedirectResponse
    {
        $data = $this->validated($request);
        $project = Project::query()->create($data);
        ActivityLogger::log('create', 'projects', $project, 'Project created');

        return redirect()->route('admin.projects.index')->with('success', 'Project created.');
    }

    public function show(Project $project): View
    {
        return view('admin.projects.show', compact('project'));
    }

    public function edit(Project $project): View
    {
        return view('admin.projects.form', compact('project'));
    }

    public function update(Request $request, Project $project): RedirectResponse
    {
        $project->update($this->validated($request, $project));
        ActivityLogger::log('update', 'projects', $project, 'Project updated');

        return redirect()->route('admin.projects.index')->with('success', 'Project updated.');
    }

    public function destroy(Project $project): RedirectResponse
    {
        $project->delete();
        ActivityLogger::log('delete', 'projects', $project, 'Project deleted');

        return redirect()->route('admin.projects.index')->with('success', 'Project deleted.');
    }

    private function validated(Request $request, ?Project $project = null): array
    {
        $data = $request->validate([
            'title' => ['required', 'string', 'max:190'],
            'slug' => ['nullable', 'string', 'max:190', 'unique:projects,slug,'.($project?->id ?? 'NULL')],
            'client_name' => ['nullable', 'string', 'max:190'],
            'category' => ['nullable', 'string', 'max:120'],
            'preview' => ['nullable', 'string', 'max:60'],
            'type' => ['nullable', 'string', 'max:120'],
            'technologies_text' => ['nullable', 'string'],
            'short_description' => ['nullable', 'string', 'max:500'],
            'full_case_study' => ['nullable', 'string'],
            'thumbnail' => ['nullable', 'string', 'max:255'],
            'gallery_text' => ['nullable', 'string'],
            'project_url' => ['nullable', 'string', 'max:255'],
            'completed_at' => ['nullable', 'date'],
            'sort_order' => ['nullable', 'integer', 'min:0'],
            'status' => ['required', 'in:draft,published'],
            'seo_title' => ['nullable', 'string', 'max:190'],
            'seo_description' => ['nullable', 'string', 'max:500'],
            'is_featured' => ['sometimes', 'boolean'],
        ]);

        $data['slug'] = $data['slug'] ?: Str::slug($data['title']);
        $data['technologies'] = collect(preg_split('/\r\n|\r|\n|,/', (string) ($data['technologies_text'] ?? '')))
            ->map(fn ($line) => trim($line))
            ->filter()
            ->values()
            ->all();
        $data['gallery'] = collect(preg_split('/\r\n|\r|\n/', (string) ($data['gallery_text'] ?? '')))
            ->map(fn ($line) => trim($line))
            ->filter()
            ->values()
            ->all();
        unset($data['technologies_text'], $data['gallery_text']);
        $data['is_featured'] = $request->boolean('is_featured');
        $data['sort_order'] = (int) ($data['sort_order'] ?? 0);

        return $data;
    }
}
