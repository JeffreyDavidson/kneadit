<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

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
}
