<?php

namespace App\Http\Requests\Storefront;

use App\Models\Platform\Tenant;
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
            'subdomain' => self::subdomainRules(),
            'storefront_choice' => ['required', 'in:kneadit,own'],
            'external_website' => ['required_if:storefront_choice,own', 'nullable', 'url', 'max:255'],
        ];
    }

    /**
     * The rules a bakery subdomain must pass, shared with ChangeTenantSubdomain. A bakery
     * that is changing its subdomain does not collide with its own id, subdomain or
     * domain rows, so those are excluded for `$changing`.
     *
     * @return array<int, mixed>
     */
    public static function subdomainRules(?Tenant $changing = null): array
    {
        $domainRule = Rule::unique('domains', 'domain');

        if ($changing instanceof Tenant) {
            $domainRule->whereNot('tenant_id', $changing->id);
        }

        return [
            'required',
            'string',
            'regex:/^[a-z0-9](?:[a-z0-9-]{0,61}[a-z0-9])?$/',
            Rule::notIn(config()->array('kneadit.reserved_subdomains')),
            $domainRule,
            Rule::unique('tenants', 'id')->ignore($changing?->id, 'id'),
            Rule::unique('tenants', 'subdomain')->ignore($changing?->id, 'id'),
        ];
    }

    /**
     * @return array<string, string>
     */
    public static function subdomainMessages(): array
    {
        return [
            'subdomain.regex' => 'Use lowercase letters, numbers and hyphens, starting and ending with a letter or number.',
        ];
    }

    /**
     * @return array<string, string>
     */
    public function messages(): array
    {
        return self::subdomainMessages();
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
