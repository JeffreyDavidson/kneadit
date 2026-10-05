<?php

namespace App\Http\Requests\Auth;

use App\Models\Staff\StaffInvitation;
use App\Models\Staff\User;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rules\Password;

class AcceptInvitationRequest extends FormRequest
{
    public function authorize(): bool
    {
        if (! Auth::check()) {
            return true;
        }

        $invitation = StaffInvitation::query()
            ->pending()
            ->where('token', $this->route('token'))
            ->first();

        $user = Auth::user();

        return ! $invitation || ($user !== null && $user->email === $invitation->email);
    }

    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        // A new user has no account to fall back on, so both fields are mandatory.
        // An existing user only gains the role and may omit them.
        $presence = $this->needsNewAccount() ? ['required'] : ['sometimes', 'required'];

        return [
            'name' => [...$presence, 'string', 'max:255'],
            'password' => [...$presence, 'string', 'confirmed', Password::min(8)->letters()->numbers()],
        ];
    }

    private function needsNewAccount(): bool
    {
        $invitation = $this->attributes->get('invitation');

        return $invitation instanceof StaffInvitation
            && ! User::query()->where('email', $invitation->email)->exists();
    }
}
