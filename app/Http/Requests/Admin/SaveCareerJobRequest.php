<?php

namespace App\Http\Requests\Admin;

use App\Models\CareerJob;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class SaveCareerJobRequest extends FormRequest
{
    public function authorize(): bool { return $this->user()?->can('admin.recruitment.manage') ?? false; }

    protected function prepareForValidation(): void
    {
        $this->merge(['slug' => $this->input('slug') ?: str($this->input('title'))->slug()->toString()]);
    }

    public function rules(): array
    {
        $job = $this->route('job');
        return [
            'career_department_id' => ['required', 'exists:career_departments,id'], 'title' => ['required', 'string', 'max:190'],
            'slug' => ['required', 'regex:/^[a-z0-9]+(?:-[a-z0-9]+)*$/', 'max:190', Rule::unique('career_jobs')->ignore($job?->id)],
            'location' => ['required', 'string', 'max:190'], 'work_mode' => ['required', Rule::in(array_keys(CareerJob::WORK_MODES))],
            'employment_type' => ['required', Rule::in(array_keys(CareerJob::EMPLOYMENT_TYPES))],
            'experience_min' => ['nullable', 'integer', 'min:0', 'max:60'], 'experience_max' => ['nullable', 'integer', 'gte:experience_min', 'max:60'],
            'compensation_type' => ['nullable', Rule::in(['salary', 'hourly', 'fixed-project'])],
            'compensation_min' => ['nullable', 'integer', 'min:0'], 'compensation_max' => ['nullable', 'integer', 'gte:compensation_min'],
            'compensation_period' => ['nullable', Rule::in(['hour', 'month', 'year', 'project'])], 'show_compensation' => ['sometimes', 'boolean'],
            'summary' => ['required', 'string', 'max:2000'], 'overview' => ['nullable', 'string', 'max:50000'],
            'responsibilities' => ['nullable', 'string', 'max:50000'], 'required_skills' => ['nullable', 'string', 'max:50000'],
            'qualifications' => ['nullable', 'string', 'max:50000'], 'application_deadline' => ['nullable', 'date'],
            'sort_order' => ['nullable', 'integer', 'min:0', 'max:1000000'], 'is_featured' => ['sometimes', 'boolean'],
            'seo_title' => ['nullable', 'string', 'max:190'], 'seo_description' => ['nullable', 'string', 'max:500'],
        ];
    }
}
