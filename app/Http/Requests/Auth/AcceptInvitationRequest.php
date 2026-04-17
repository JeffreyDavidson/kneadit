<?php

namespace App\Http\Requests\Auth;

use App\Models\Staff\StaffInvitation;
use App\Models\Staff\User;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Auth;

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
        return [
            'name' => ['sometimes', 'required', 'string', 'max:255'],
            'password' => ['sometimes', 'required', 'string', 'confirmed', User::passwordRule()],
        ];
    }
}
