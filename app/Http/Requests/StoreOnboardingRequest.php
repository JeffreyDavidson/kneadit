<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Str;

class StoreOnboardingRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            'store_name' => ['required', 'string', 'max:255'],
            'subdomain' => ['required', 'string', 'max:63', 'alpha_dash', 'not_in:www,mail,admin,api,app,blog,cdn,dev,ftp,help,imap,login,mx,ns,pop,smtp,staging,status,support,test,webmail', 'unique:domains,domain'],
            'storefront_choice' => ['required', 'in:kneadit,own'],
            'external_website' => ['required_if:storefront_choice,own', 'nullable', 'url', 'max:255'],
        ];
    }

    public function subdomain(): string
    {
        return Str::lower($this->validated('subdomain'));
    }

    public function usesKneadItStorefront(): bool
    {
        return $this->validated('storefront_choice') === 'kneadit';
    }

    public function referralCode(): ?string
    {
        return $this->session()->get('referral_code') ?? $this->cookie('referral_code');
    }

    public function adminUrl(): string
    {
        $scheme = $this->secure() ? 'https' : 'http';

        return "{$scheme}://{$this->subdomain()}.{$this->getHost()}/admin";
    }
}
