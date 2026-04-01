<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\Staff\StaffInvitation;
use App\Models\Staff\User;
use App\Services\Settings\TenantSettings;
use Illuminate\Contracts\View\View;
use Illuminate\Http\Request;

class ShowInvitationController extends Controller
{
    public function __invoke(Request $request, TenantSettings $settings): View
    {
        /** @var StaffInvitation $invitation */
        $invitation = $request->attributes->get('invitation');

        $existingUser = User::query()->where('email', $invitation->email)->first();

        return view('invitations.show', [
            'settings' => $settings,
            'invitation' => $invitation,
            'existingUser' => $existingUser,
        ]);
    }
}
