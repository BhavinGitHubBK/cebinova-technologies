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
            'phone' => ['nullable', 'string', 'max:40', 'regex:/^[0-9+()\-\s]{7,40}$/'],
            'area_of_interest' => [Rule::requiredIf(! $this->route('job')), 'nullable', 'string', 'max:190'],
            'current_location' => ['required', 'string', 'max:190'], 'experience' => ['required', 'string', 'max:120'],
            'skills' => ['required', 'string', 'max:3000'], 'availability' => ['required', 'string', 'max:190'],
            'message' => ['nullable', 'string', 'max:5000'],
            'portfolio_url' => ['nullable', 'url:http,https', 'max:2048'], 'github_url' => ['nullable', 'url:http,https', 'max:2048'],
            'linkedin_url' => ['nullable', 'url:http,https', 'max:2048'],
            'resume' => ['required', 'file', 'max:5120', 'extensions:pdf,doc,docx', 'mimes:pdf,doc,docx', 'mimetypes:application/pdf,application/msword,application/vnd.openxmlformats-officedocument.wordprocessingml.document,application/octet-stream'],
            'freelance_preference' => [Rule::requiredIf($freelance), 'nullable', Rule::in(['hourly', 'fixed-project'])],
            'expected_rate' => [Rule::requiredIf($freelance), 'nullable', 'integer', 'min:1', 'max:100000000'],
            'consent' => ['accepted'], 'website' => ['nullable', 'size:0'],
        ];
    }

    public function messages(): array
    {
        return [
            'required' => 'Please enter your :attribute.',
            'max.string' => 'Please keep your :attribute within :max characters.',
            'email.email' => 'Please enter a valid email address, such as name@example.com.',
            'phone.regex' => 'Please enter a valid phone number using digits, spaces, +, brackets or hyphens.',
            'url' => 'Please enter a valid website link, such as https://example.com.',
            'resume.required' => 'Please choose your resume before submitting.',
            'resume.uploaded' => 'Your resume could not be uploaded. Please choose a PDF, DOC or DOCX file up to 5 MB and try again.',
            'resume.max' => 'Your resume is too large. Please choose a file no larger than 5 MB.',
            'resume.file' => 'Please choose a valid resume file.',
            'resume.extensions' => 'Please upload your resume as a PDF, DOC or DOCX file.',
            'resume.mimes' => 'Please upload your resume as a PDF, DOC or DOCX file.',
            'resume.mimetypes' => 'Please upload a valid PDF, DOC or DOCX resume.',
            'freelance_preference.required' => 'Please choose hourly or fixed-project work.',
            'freelance_preference.in' => 'Please choose hourly or fixed-project work.',
            'expected_rate.integer' => 'Please enter your expected rate as a whole number in INR.',
            'expected_rate.min' => 'Please enter an expected rate of at least INR 1.',
            'expected_rate.max' => 'Please enter an expected rate no greater than INR 100,000,000.',
            'consent.accepted' => 'Please tick the consent box so we can review and store your application.',
            'website.size' => 'We could not verify this submission. Please refresh the page and try again.',
        ];
    }

    public function attributes(): array
    {
        return ['name' => 'full name', 'email' => 'email address', 'current_location' => 'current city or location', 'area_of_interest' => 'area of interest', 'availability' => 'availability or notice period', 'skills' => 'relevant skills', 'experience' => 'experience, including fresher or internship experience', 'message' => 'short introduction', 'portfolio_url' => 'portfolio link', 'github_url' => 'GitHub link', 'linkedin_url' => 'LinkedIn link', 'expected_rate' => 'expected rate'];
    }
}
