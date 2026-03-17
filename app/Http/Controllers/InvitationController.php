<?php

namespace App\Http\Controllers;

use App\Models\Setting;
use App\Models\StaffInvitation;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;

class InvitationController extends Controller
{
    public function show(string $token)
    {
        $invitation = StaffInvitation::where('token', $token)
            ->whereNull('accepted_at')
            ->firstOrFail();

        if ($invitation->isExpired()) {
            return view('invitations.expired');
        }

        $storeName = Setting::get('store_name', 'Our Bakery');
        $existingUser = User::where('email', $invitation->email)->first();

        return view('invitations.show', [
            'invitation' => $invitation,
            'storeName' => $storeName,
            'existingUser' => $existingUser,
        ]);
    }

    public function store(Request $request, string $token)
    {
        $invitation = StaffInvitation::where('token', $token)
            ->whereNull('accepted_at')
            ->firstOrFail();

        if ($invitation->isExpired()) {
            return view('invitations.expired');
        }

        // If an authenticated user is accepting, verify their email matches the invitation
        abort_if(Auth::check() && Auth::user()->email !== $invitation->email, 403, 'This invitation was sent to a different email address.');

        $existingUser = User::where('email', $invitation->email)->first();

        if ($existingUser) {
            $existingUser->update(['role' => $invitation->role]);
            $user = $existingUser;
        } else {
            $request->validate([
                'name' => ['required', 'string', 'max:255'],
                'password' => ['required', 'string', 'min:8', 'confirmed'],
            ]);

            $user = User::create([
                'name' => $request->name,
                'email' => $invitation->email,
                'password' => Hash::make($request->password),
                'role' => $invitation->role,
                'email_verified_at' => now(),
            ]);
        }

        $invitation->update(['accepted_at' => now()]);

        Auth::login($user);

        return redirect('/admin');
    }
}
