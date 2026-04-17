<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class SendVerificationNotificationController extends Controller
{
    public function __invoke(Request $request): RedirectResponse
    {
        $user = $request->user();
        abort_if($user === null, 401);

        $user->sendEmailVerificationNotification();

        return back()->with('message', 'Verification link sent!');
    }
}
