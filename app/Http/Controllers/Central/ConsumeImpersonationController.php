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

        // Redirect to the tenant Admin panel ON THE CURRENT HOST. We can't use
        // Filament::getPanel('admin')->getUrl() — that helper falls back to
        // config('app.url') (central domain) when there's no panel context,
        // which sends the baker off-domain to a panel they can't access.
        // Use a path-only redirect so the browser keeps the tenant subdomain,
        // then prefix the configured panel path explicitly.
        $path = '/' . ltrim(Filament::getPanel('admin')->getPath(), '/');

        return redirect()->to($path);
    }
}
