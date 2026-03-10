<?php

namespace App\Http\Controllers;

use App\Models\Tenant;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class ImpersonateController extends Controller
{
    public function login(Request $request, Tenant $tenant)
    {
        $tenant->initialize();

        $user = $tenant->users()->first();

        if (! $user) {
            abort(404, 'No users found for this tenant.');
        }

        Auth::login($user);

        return redirect()->to('https://' . $tenant->id . '.getkneadit.app/admin');
    }
}
