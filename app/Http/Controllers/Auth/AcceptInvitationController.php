<?php

namespace App\Http\Controllers\Auth;

use App\Actions\Staff\AcceptStaffInvitation;
use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\AcceptInvitationRequest;
use App\Models\Staff\StaffInvitation;
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
