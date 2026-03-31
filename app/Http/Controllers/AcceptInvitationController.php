<?php

namespace App\Http\Controllers;

use App\Actions\Staff\AcceptStaffInvitation;
use App\Http\Requests\AcceptInvitationRequest;
use App\Models\StaffInvitation;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Auth;

class AcceptInvitationController extends Controller
{
    public function __invoke(AcceptInvitationRequest $request, AcceptStaffInvitation $acceptInvitation): RedirectResponse
    {
        /** @var StaffInvitation $invitation */
        $invitation = $request->attributes->get('invitation');

        $user = $acceptInvitation(
            invitation: $invitation,
            name: $request->validated('name'),
            password: $request->validated('password'),
        );

        Auth::login($user);

        return redirect('/admin');
    }
}
