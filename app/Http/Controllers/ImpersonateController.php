<?php

namespace App\Http\Controllers;

use App\Actions\Platform\CreateImpersonationToken;
use App\Enums\UserRole;
use App\Models\Tenant;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class ImpersonateController extends Controller
{
    public function __invoke(Request $request, Tenant $tenant, CreateImpersonationToken $createToken): RedirectResponse
    {
        abort_unless(
            $request->user()?->role === UserRole::PlatformAdmin,
            403,
            'Unauthorized.',
        );

        return redirect()->to($createToken($tenant));
    }
}
