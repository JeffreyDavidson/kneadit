<?php

namespace App\Http\Controllers;

use App\Actions\Staff\AcceptStaffInvitation;
use App\Http\Requests\AcceptInvitationRequest;
use App\Models\StaffInvitation;
use App\Models\User;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Auth;

class InvitationController extends Controller
{
    public function show(string $token): View
    {
        $invitation = StaffInvitation::query()->where('token', $token)
            ->whereNull('accepted_at')
            ->firstOrFail();

        if ($invitation->isExpired()) {
            return view('invitations.expired');
        }

        $storeName = settings('store_name', 'Our Bakery');
        $existingUser = User::query()->where('email', $invitation->email)->first();

        return view('invitations.show', [
            'invitation' => $invitation,
            'storeName' => $storeName,
            'existingUser' => $existingUser,
        ]);
    }

    public function store(AcceptInvitationRequest $request, string $token, AcceptStaffInvitation $acceptInvitation): View|RedirectResponse
    {
        $invitation = StaffInvitation::query()->where('token', $token)
            ->whereNull('accepted_at')
            ->firstOrFail();

        if ($invitation->isExpired()) {
            return view('invitations.expired');
        }

        abort_if(Auth::check() && Auth::user()?->email !== $invitation->email, 403, 'This invitation was sent to a different email address.');

        $validated = $request->validated();

        $user = $acceptInvitation(
            invitation: $invitation,
            name: $validated['name'] ?? null,
            password: $validated['password'] ?? null,
        );

        Auth::login($user);

        return redirect('/admin');
    }
}
