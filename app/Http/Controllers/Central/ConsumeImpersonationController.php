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
        Auth::login($consumeToken($token, $request->ip()));

        // Explicitly target the tenant admin panel — calling
        // Dashboard::getUrl() here would resolve against Filament's "current"
        // panel which is unset in this controller, so it'd fall back to the
        // first-registered panel (Central) and send the baker to a URL their
        // role can't access.
        return redirect(Filament::getPanel('admin')->getUrl());
    }
}
