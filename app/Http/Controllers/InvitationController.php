<?php

namespace App\Http\Controllers;

use App\Http\Requests\AcceptInvitationRequest;
use App\Models\Setting;
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

        $storeName = Setting::get('store_name', 'Our Bakery');
        $existingUser = User::query()->where('email', $invitation->email)->first();

        return view('invitations.show', [
            'invitation' => $invitation,
            'storeName' => $storeName,
            'existingUser' => $existingUser,
        ]);
    }

    public function store(AcceptInvitationRequest $request, string $token): View|RedirectResponse
    {
        $invitation = StaffInvitation::query()->where('token', $token)
            ->whereNull('accepted_at')
            ->firstOrFail();

        if ($invitation->isExpired()) {
            return view('invitations.expired');
        }

        // If an authenticated user is accepting, verify their email matches the invitation
        abort_if(Auth::check() && Auth::user()->email !== $invitation->email, 403, 'This invitation was sent to a different email address.');

        $existingUser = User::query()->where('email', $invitation->email)->first();

        if ($existingUser) {
            $existingUser->update(['role' => $invitation->role]);
            $user = $existingUser;
        } else {
            $validated = $request->validated();

            $user = User::query()->create([
                'name' => $validated['name'],
                'email' => $invitation->email,
                'password' => $validated['password'],
                'role' => $invitation->role,
                'email_verified_at' => now(),
            ]);
        }

        $invitation->update(['accepted_at' => now()]);

        Auth::login($user);

        return redirect('/admin');
    }
}
