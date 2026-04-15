<?php

namespace App\Http\Requests\Auth;

use App\Models\Staff\User;
use Illuminate\Foundation\Http\FormRequest;

class RegisterRequest extends FormRequest
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
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'email', 'max:255', 'unique:users'],
            'password' => ['required', 'string', 'confirmed', User::passwordRule()],
            'bakery_name' => ['required', 'string', 'max:255'],
            'terms' => ['accepted'],
        ];
    }
}
