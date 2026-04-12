<?php

namespace App\Http\Controllers\Central;

use App\Actions\Platform\CreateImpersonationToken;
use App\Http\Controllers\Controller;
use App\Models\Platform\Tenant;
use Illuminate\Http\RedirectResponse;
use Illuminate\Routing\Attributes\Controllers\Authorize;

class ImpersonateController extends Controller
{
    #[Authorize('platform-admin')]
    public function __invoke(Tenant $tenant, CreateImpersonationToken $createToken): RedirectResponse
    {
        return redirect()->to($createToken($tenant));
    }
}
