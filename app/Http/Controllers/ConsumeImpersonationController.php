<?php

namespace App\Http\Controllers;

use App\Actions\Platform\ConsumeImpersonationToken;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Auth;

class ConsumeImpersonationController extends Controller
{
    public function __invoke(string $token, ConsumeImpersonationToken $consumeToken): RedirectResponse
    {
        Auth::login($consumeToken($token));

        return redirect()->to('/admin');
    }
}
