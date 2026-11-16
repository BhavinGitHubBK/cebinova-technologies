<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\CareerDepartment;
use App\Services\Admin\ActivityLogger;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class CareerDepartmentController extends Controller
{
    public function index(): View { return view('admin.recruitment.departments', ['departments' => CareerDepartment::withCount('jobs')->orderBy('sort_order')->orderBy('name')->get()]); }

    public function store(Request $request): RedirectResponse { return $this->save($request, new CareerDepartment); }
    public function update(Request $request, CareerDepartment $department): RedirectResponse { return $this->save($request, $department); }

    private function save(Request $request, CareerDepartment $department): RedirectResponse
    {
        $request->merge(['slug' => $request->input('slug') ?: Str::slug($request->input('name', ''))]);
        $data = $request->validate(['name' => ['required', 'string', 'max:120'], 'slug' => ['required', 'max:140', 'regex:/^[a-z0-9]+(?:-[a-z0-9]+)*$/', Rule::unique('career_departments')->ignore($department)], 'sort_order' => ['nullable', 'integer', 'min:0'], 'is_active' => ['sometimes', 'boolean']]);
        $data['is_active'] = $request->boolean('is_active'); $data['sort_order'] ??= 0;
        $department->fill($data)->save();
        ActivityLogger::log($department->wasRecentlyCreated ? 'create' : 'update', 'recruitment', $department, 'Department saved');
        return back()->with('success', 'Department saved.');
    }

    public function destroy(CareerDepartment $department): RedirectResponse
    {
        abort_if($department->jobs()->exists(), 422, 'This department is used by jobs.');
        $department->delete();
        return back()->with('success', 'Department deleted.');
    }
}
