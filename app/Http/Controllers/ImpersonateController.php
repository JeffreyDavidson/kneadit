<?php

namespace App\Http\Controllers;

use App\Actions\Platform\CreateImpersonationToken;
use App\Models\Tenant;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Gate;

class ImpersonateController extends Controller
{
    public function __invoke(Tenant $tenant, CreateImpersonationToken $createToken): RedirectResponse
    {
        Gate::authorize('platform-admin');

        return redirect()->to($createToken($tenant));
    }
}
