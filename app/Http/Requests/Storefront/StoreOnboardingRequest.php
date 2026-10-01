<?php

namespace App\Http\Requests\Storefront;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;

class StoreOnboardingRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    protected function prepareForValidation(): void
    {
        if (! is_string($this->input('subdomain'))) {
            return;
        }

        $this->merge(['subdomain' => Str::lower(trim($this->input('subdomain')))]);
    }

    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            'store_name' => ['required', 'string', 'max:255'],
            'subdomain' => [
                'required',
                'string',
                'regex:/^[a-z0-9](?:[a-z0-9-]{0,61}[a-z0-9])?$/',
                Rule::notIn(config()->array('kneadit.reserved_subdomains')),
                'unique:domains,domain',
                'unique:tenants,id',
            ],
            'storefront_choice' => ['required', 'in:kneadit,own'],
            'external_website' => ['required_if:storefront_choice,own', 'nullable', 'url', 'max:255'],
        ];
    }

    /**
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'subdomain.regex' => 'Use lowercase letters, numbers and hyphens, starting and ending with a letter or number.',
        ];
    }

    public function subdomain(): string
    {
        return $this->string('subdomain')->lower()->toString();
    }

    public function usesKneadItStorefront(): bool
    {
        return $this->string('storefront_choice')->toString() === 'kneadit';
    }

    public function referralCode(): ?string
    {
        $referralCode = $this->session()->get('referral_code') ?? $this->cookie('referral_code');

        return is_string($referralCode) ? $referralCode : null;
    }

    public function adminUrl(): string
    {
        $scheme = $this->secure() ? 'https' : 'http';

        return "{$scheme}://{$this->subdomain()}.{$this->getHost()}/admin";
    }
}
