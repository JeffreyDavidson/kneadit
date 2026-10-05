<?php

declare(strict_types=1);

namespace App\Http\Requests\Auth;

use App\Models\Staff\User;
use App\Support\EmailAddress;
use Closure;
use Illuminate\Database\Query\Expression;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rules\Password;

class RegisterRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    protected function prepareForValidation(): void
    {
        if (! is_string($this->input('email'))) {
            return;
        }

        $this->merge(['email' => EmailAddress::normalize($this->input('email'))]);
    }

    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:255'],
            'email' => [
                'bail',
                'required',
                'string',
                'email',
                'max:255',
                // Compared lowercased so an account stored before emails were normalized still counts as taken.
                function (string $attribute, mixed $value, Closure $fail): void {
                    if (User::query()->where(new Expression('lower(email)'), $value)->exists()) {
                        $fail(__('validation.unique', ['attribute' => $attribute]));
                    }
                },
            ],
            'password' => ['required', 'string', 'confirmed', Password::min(8)->letters()->numbers()],
            'bakery_name' => ['required', 'string', 'max:255'],
            'terms' => ['accepted'],
        ];
    }
}
