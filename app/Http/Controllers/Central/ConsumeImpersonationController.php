<?php

namespace App\Http\Controllers\Central;

use App\Actions\Platform\ConsumeImpersonationToken;
use App\Http\Controllers\Controller;
use Filament\Facades\Filament;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class ConsumeImpersonationController extends Controller
{
    public function __invoke(string $token, Request $request, ConsumeImpersonationToken $consumeToken): RedirectResponse
    {
        $user = $consumeToken($token, $request->ip());

        // Flush any prior session data (e.g. the platform admin's password_hash_web
        // carried in via the shared SESSION_DOMAIN cookie). Without this,
        // AuthenticateSession on the next request compares the impersonated
        // tenant user's password hash to the platform admin's stale hash,
        // logs the user out, and bounces them to /admin/login.
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        Auth::login($user);

        return redirect()->to('/' . ltrim(Filament::getPanel('admin')->getPath(), '/'));
    }
}
