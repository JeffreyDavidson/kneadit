<?php

namespace App\Http\Controllers\Auth;

use App\Actions\Staff\AcceptStaffInvitation;
use App\Filament\Pages\Dashboard\Dashboard;
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
            name: $request->filled('name') ? $request->string('name')->toString() : null,
            password: $request->filled('password') ? $request->string('password')->toString() : null,
        );

        Auth::login($user);

        return redirect(Dashboard::getUrl());
    }
}
