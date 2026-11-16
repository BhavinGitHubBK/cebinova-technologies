<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreCareerApplicationRequest extends FormRequest
{
    public function authorize(): bool { return true; }

    protected function prepareForValidation(): void
    {
        foreach (['portfolio_url', 'github_url', 'linkedin_url'] as $field) {
            if ($this->filled($field) && ! preg_match('/^https?:\/\//i', (string) $this->input($field))) {
                $this->merge([$field => 'https://'.$this->input($field)]);
            }
        }
    }

    public function rules(): array
    {
        $freelance = $this->route('job')?->employment_type === 'freelance';
        return [
            'name' => ['required', 'string', 'max:190'], 'email' => ['required', 'email:rfc', 'max:190'],
            'phone' => ['required', 'string', 'max:40', 'regex:/^[0-9+()\-\s]{7,40}$/'],
            'current_location' => ['required', 'string', 'max:190'], 'experience' => ['required', 'string', 'max:120'],
            'skills' => ['required', 'string', 'max:3000'], 'availability' => ['required', 'string', 'max:190'],
            'message' => ['nullable', 'string', 'max:5000'],
            'portfolio_url' => ['nullable', 'url:http,https', 'max:2048'], 'github_url' => ['nullable', 'url:http,https', 'max:2048'],
            'linkedin_url' => ['nullable', 'url:http,https', 'max:2048'],
            'resume' => ['required', 'file', 'max:5120', 'mimes:pdf,doc,docx', 'mimetypes:application/pdf,application/msword,application/vnd.openxmlformats-officedocument.wordprocessingml.document,application/octet-stream'],
            'freelance_preference' => [Rule::requiredIf($freelance), 'nullable', Rule::in(['hourly', 'fixed-project'])],
            'expected_rate' => [Rule::requiredIf($freelance), 'nullable', 'integer', 'min:1', 'max:100000000'],
            'consent' => ['accepted'], 'website' => ['nullable', 'size:0'],
        ];
    }

    public function messages(): array
    {
        return ['consent.accepted' => 'You must consent to recruitment data storage before applying.', 'website.size' => 'Unable to submit this application.'];
    }
}
