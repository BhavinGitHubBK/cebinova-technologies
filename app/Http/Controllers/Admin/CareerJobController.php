<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\SaveCareerJobRequest;
use App\Models\CareerDepartment;
use App\Models\CareerJob;
use App\Services\Admin\ActivityLogger;
use App\Support\SafeHtml;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class CareerJobController extends Controller
{
    public function index(Request $request): View
    {
        $query = CareerJob::query()->with('department')->withCount('applications')
            ->when($request->filled('q'), fn ($q) => $q->where(fn ($inner) => $inner->where('title', 'like', '%'.$request->string('q').'%')->orWhere('location', 'like', '%'.$request->string('q').'%')))
            ->when($request->filled('status'), fn ($q) => $q->where('status', $request->string('status')))
            ->when($request->filled('department'), fn ($q) => $q->where('career_department_id', $request->integer('department')));
        return view('admin.recruitment.jobs.index', [
            'jobs' => $query->orderBy('sort_order')->orderByDesc('created_at')->paginate(20)->withQueryString(),
            'departments' => CareerDepartment::orderBy('name')->get(),
            'counts' => CareerJob::query()->selectRaw('status, count(*) total')->groupBy('status')->pluck('total', 'status'),
        ]);
    }

    public function create(): View { return $this->form(new CareerJob); }
    public function edit(CareerJob $job): View { return $this->form($job); }
    public function preview(CareerJob $job): View { $job->load('department'); return view('careers.show', compact('job'))->with('preview', true); }

    private function form(CareerJob $job): View
    {
        return view('admin.recruitment.jobs.form', ['job' => $job, 'departments' => CareerDepartment::where('is_active', true)->orderBy('sort_order')->orderBy('name')->get()]);
    }

    public function store(SaveCareerJobRequest $request): RedirectResponse { return $this->save($request, new CareerJob); }
    public function update(SaveCareerJobRequest $request, CareerJob $job): RedirectResponse { return $this->save($request, $job); }

    private function save(SaveCareerJobRequest $request, CareerJob $job): RedirectResponse
    {
        $data = $request->validated();
        foreach (['overview', 'responsibilities', 'required_skills', 'qualifications'] as $field) $data[$field] = SafeHtml::clean($data[$field] ?? null);
        $data['show_compensation'] = $request->boolean('show_compensation');
        $data['is_featured'] = $request->boolean('is_featured');
        $data['sort_order'] ??= 0;
        $job->fill($data)->save();
        ActivityLogger::log($job->wasRecentlyCreated ? 'create' : 'update', 'recruitment', $job, 'Career job saved');
        return redirect()->route('admin.recruitment.jobs.edit', $job)->with('success', 'Job saved.');
    }

    public function status(Request $request, CareerJob $job): RedirectResponse
    {
        $data = $request->validate(['status' => ['required', 'in:draft,published,closed,archived']]);
        $job->status = $data['status'];
        if ($data['status'] === 'published' && ! $job->published_at) $job->published_at = now();
        $job->save();
        ActivityLogger::log('update', 'recruitment', $job, 'Job status changed to '.$data['status']);
        return back()->with('success', 'Job status updated.');
    }

    public function destroy(CareerJob $job): RedirectResponse
    {
        abort_if($job->applications()->exists(), 422, 'Archive jobs with applications instead of deleting them.');
        $job->delete();
        ActivityLogger::log('delete', 'recruitment', $job, 'Job deleted');
        return redirect()->route('admin.recruitment.jobs.index')->with('success', 'Job deleted.');
    }
}
