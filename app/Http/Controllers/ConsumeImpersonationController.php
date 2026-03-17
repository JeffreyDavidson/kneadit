<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class ConsumeImpersonationController extends Controller
{
    /**
     * Tenant side: consume the token and log in as the first user.
     */
    public function __invoke(Request $request, string $token)
    {
        $hashed = hash('sha256', $token);
        $record = DB::connection('central')->table('impersonation_tokens')
            ->where('token', $hashed)
            ->where('expires_at', '>', now())
            ->first();

        abort_unless($record, 403, 'Invalid or expired impersonation token.');

        // Delete the token (one-time use)
        DB::connection('central')->table('impersonation_tokens')
            ->where('token', $hashed)
            ->delete();

        $user = User::first();

        abort_unless($user, 404, 'No users found for this tenant.');

        Auth::login($user);

        return redirect()->to('/admin');
    }
}
