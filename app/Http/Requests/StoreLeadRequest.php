<?php

namespace App\Http\Requests;

use App\Support\MarketingPackages;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Validator;

class StoreLeadRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        $packageEnquiry = $this->isPackageEnquiry();

        return [
            'name' => ['required', 'string', 'max:120'],
            'business_name' => ['nullable', 'string', 'max:160'],
            'phone' => ['required', 'string', 'max:20', 'regex:/^[0-9+\s\-()]{8,20}$/'],
            'whatsapp' => ['nullable', 'string', 'max:20', 'regex:/^[0-9+\s\-()]{8,20}$/'],
            'email' => ['nullable', 'email:rfc', 'max:160'],
            'business_type' => ['nullable', 'string', Rule::in(config('sarvix.form.business_types'))],
            'service' => ['required', 'string', Rule::in(config('sarvix.form.services'))],
            'package_category' => ['nullable', 'string', Rule::requiredIf($packageEnquiry), Rule::in(MarketingPackages::categories())],
            'plan_duration' => ['nullable', 'string', Rule::requiredIf($packageEnquiry), Rule::in(MarketingPackages::durations())],
            'plan_price' => ['nullable', 'string', 'max:40'],
            'city' => ['nullable', 'string', 'max:80'],
            'budget' => ['nullable', 'string', Rule::in(config('sarvix.form.budgets'))],
            'message' => ['nullable', 'string', 'max:2000'],
            'consultation' => ['sometimes', 'boolean'],
            'source' => ['nullable', 'string', Rule::in(config('sarvix.leads.sources'))],
        ];
    }

    public function withValidator(Validator $validator): void
    {
        $validator->after(function (Validator $validator) {
            if ($validator->errors()->isNotEmpty()) {
                return;
            }

            $category = $this->input('package_category');
            $duration = $this->input('plan_duration');

            if (! $this->isPackageEnquiry()) {
                return;
            }

            if (! MarketingPackages::isValid((string) $category, (string) $duration)) {
                $validator->errors()->add('plan_duration', 'The selected package and duration are not valid.');
            }
        });
    }

    protected function prepareForValidation(): void
    {
        $this->merge([
            'consultation' => $this->boolean('consultation'),
            'name' => $this->clean('name'),
            'business_name' => $this->clean('business_name'),
            'phone' => $this->clean('phone'),
            'whatsapp' => $this->clean('whatsapp'),
            'email' => $this->clean('email'),
            'business_type' => $this->clean('business_type'),
            'service' => $this->clean('service'),
            'package_category' => $this->clean('package_category'),
            'plan_duration' => $this->clean('plan_duration'),
            'city' => $this->clean('city'),
            'budget' => $this->clean('budget'),
            'message' => $this->clean('message'),
            'source' => $this->clean('source'),
        ]);
    }

    public function isHoneypotTriggered(): bool
    {
        return filled($this->input('website'));
    }

    public function isPackageEnquiry(): bool
    {
        return in_array($this->input('service'), config('sarvix.form.package_services'), true)
            || filled($this->input('package_category'));
    }

    private function clean(string $key): ?string
    {
        $value = $this->input($key);

        if (! is_string($value)) {
            return null;
        }

        $value = preg_replace('/<script\b[^>]*>.*?<\/script>/is', '', $value) ?? $value;
        $value = trim(strip_tags($value));

        return $value === '' ? null : $value;
    }
}
