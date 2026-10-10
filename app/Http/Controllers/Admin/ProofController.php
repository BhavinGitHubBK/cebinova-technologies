<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Demo;
use App\Models\Industry;
use App\Models\Service;
use App\Models\Solution;
use App\Services\Admin\ActivityLogger;
use App\Support\ProofMedia;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;

class ProofController extends Controller
{
    private function context(): array
    {
        return [
            'kind' => 'demos',
            'services' => Service::orderBy('name')->get(),
            'solutions' => Solution::orderBy('name')->get(),
            'industries' => Industry::orderBy('name')->get(),
        ];
    }

    public function index(Request $request)
    {
        $fallback = $request->query('sort') === 'updated_at' && ! $request->filled('dir') ? 'desc' : 'asc';
        [$sort, $dir] = admin_sort($request, ['name', 'industry', 'sort_order', 'is_active', 'updated_at'], 'sort_order', $fallback);
        $matched = Demo::query()
            ->when($request->filled('q'), fn ($query) => $query->where('name', 'like', '%'.$request->string('q').'%'))
            ->when($request->filled('industry'), fn ($query) => $query->whereHas('industries', fn ($related) => $related->whereKey($request->input('industry'))))
            ->when($request->filled('solution'), fn ($query) => $query->whereHas('solutions', fn ($related) => $related->whereKey($request->input('solution'))));
        $counts = [
            'all' => (clone $matched)->count(),
            'active' => (clone $matched)->where('is_active', true)->count(),
            'inactive' => (clone $matched)->where('is_active', false)->count(),
        ];
        $records = $matched->with(['industries', 'solutions'])
            ->when($request->filled('status'), fn ($query) => $query->where('is_active', $request->input('status') === 'active'))
            ->when($sort === 'industry', function ($query) use ($dir) {
                $query->orderBy(
                    Industry::query()->select('industries.name')
                        ->join('demo_industry', 'industries.id', '=', 'demo_industry.industry_id')
                        ->whereColumn('demo_industry.demo_id', 'demos.id')
                        ->orderBy('industries.name')
                        ->limit(1),
                    $dir
                );
            }, fn ($query) => $query->orderBy($sort, $dir))
            ->orderBy('id')->paginate(20)->withQueryString();

        return view('admin.proof.index', $this->context() + compact('records', 'counts', 'sort', 'dir') + [
            'status' => $request->string('status')->toString(),
        ]);
    }

    public function create()
    {
        return view('admin.proof.form', $this->context() + ['record' => new Demo]);
    }

    public function edit(Request $request)
    {
        return view('admin.proof.form', $this->context() + [
            'record' => Demo::query()->with(['services', 'solutions', 'industries'])->findOrFail($request->route('record')),
        ]);
    }

    public function store(Request $request)
    {
        return $this->save($request, new Demo);
    }

    public function update(Request $request)
    {
        return $this->save($request, Demo::query()->findOrFail($request->route('record')));
    }

    public function destroy(Request $request)
    {
        $record = Demo::query()->findOrFail($request->route('record'));
        $record->delete();
        ActivityLogger::log('delete', 'demos', $record, 'Record archived');

        return redirect()->route('admin.demos.index')->with('success', 'Record archived; references retained.');
    }

    private function save(Request $request, Demo $record)
    {
        $request->merge(['slug' => $request->input('slug') ?: ($record->slug ?: Str::slug($request->input('name', '')))]);
        $data = $request->validate([
            'name' => ['required', 'string', 'max:190'],
            'slug' => ['required', 'string', 'max:190', 'regex:/^[a-z0-9]+(?:-[a-z0-9]+)*$/', Rule::unique('demos', 'slug')->ignore($record->id)],
            'short_description' => ['required', 'string', 'max:1000'],
            'description' => ['nullable', 'string', 'max:30000'],
            'thumbnail_upload' => ProofMedia::rules(),
            'sort_order' => ['nullable', 'integer', 'min:0', 'max:1000000'],
            'is_active' => ['sometimes', 'boolean'],
            'is_featured' => ['sometimes', 'boolean'],
            'live_url' => ['nullable', 'url:http,https', 'max:2048'],
            'badge' => ['required', Rule::in(['LIVE DEMO', 'SOLUTION DEMO', 'CONCEPT'])],
            'features' => ['nullable', 'array', 'max:50'],
            'features.*.name' => ['nullable', 'string', 'max:190'],
            'features.*.description' => ['nullable', 'string', 'max:1000'],
            'features.*.is_active' => ['required', 'boolean'],
            'features.*.sort_order' => ['nullable', 'integer', 'min:0'],
            'technologies_text' => ['nullable', 'string', 'max:2000'],
            'screenshots_upload' => ['nullable', 'array', 'max:12'],
            'screenshots_upload.*' => ProofMedia::rules(),
            'screenshots' => ['nullable', 'array', 'max:50'],
            'screenshots.*.alt_text' => ['nullable', 'string', 'max:255'],
            'screenshots.*.caption' => ['nullable', 'string', 'max:500'],
            'screenshots.*.sort_order' => ['nullable', 'integer', 'min:0'],
            'screenshots.*.remove' => ['sometimes', 'boolean'],
            'service_ids' => ['nullable', 'array'],
            'service_ids.*' => ['integer', 'distinct', Rule::exists('services', 'id')->whereNull('deleted_at')],
            'solution_ids' => ['nullable', 'array'],
            'solution_ids.*' => ['integer', 'distinct', Rule::exists('solutions', 'id')->whereNull('deleted_at')],
            'industry_ids' => ['nullable', 'array'],
            'industry_ids.*' => ['integer', 'distinct', Rule::exists('industries', 'id')->whereNull('deleted_at')],
        ]);
        foreach (['is_active', 'is_featured'] as $flag) {
            $data[$flag] = $request->boolean($flag);
        }
        $data['sort_order'] = $data['sort_order'] ?? 0;
        $data['features'] = collect($data['features'] ?? [])->filter(fn ($f) => filled($f['name'] ?? null))->sortBy('sort_order')->values()->all();
        $data['technologies'] = array_values(array_filter(array_map('trim', preg_split('/\r\n|\r|\n/', $data['technologies_text'] ?? ''))));
        unset($data['technologies_text']);
        $shots = [];
        foreach ($record->screenshots ?? [] as $index => $shot) {
            $edit = $data['screenshots'][$index] ?? [];
            if (! empty($edit['remove'])) {
                continue;
            }
            $shots[] = array_merge($shot, array_intersect_key($edit, array_flip(['alt_text', 'caption', 'sort_order'])));
        }
        foreach ($request->file('screenshots_upload', []) as $file) {
            $shots[] = ['image' => ProofMedia::store($file), 'alt_text' => $data['name'], 'caption' => null, 'sort_order' => count($shots)];
        }
        $data['screenshots'] = collect($shots)->sortBy('sort_order')->values()->all();
        if ($request->hasFile('thumbnail_upload')) {
            $data['thumbnail'] = ProofMedia::store($request->file('thumbnail_upload'));
        }
        unset($data['thumbnail_upload'], $data['screenshots_upload']);
        $relations = [
            'services' => $data['service_ids'] ?? [],
            'solutions' => $data['solution_ids'] ?? [],
            'industries' => $data['industry_ids'] ?? [],
        ];
        unset($data['service_ids'], $data['solution_ids'], $data['industry_ids']);
        DB::transaction(function () use ($record, $data, $relations) {
            $record->fill($data)->save();
            foreach ($relations as $relation => $ids) {
                $record->$relation()->sync($ids);
            }
        });
        ActivityLogger::log('update', 'demos', $record, 'Proof content saved');

        return redirect()->route('admin.demos.edit', $record)->with('success', 'Saved.');
    }
}
