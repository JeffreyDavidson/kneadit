<?php

namespace App\Http\Controllers\Storefront\Account;

use App\Http\Controllers\Controller;
use Illuminate\Contracts\View\View;
use Illuminate\Http\Request;

class ShowResetPasswordController extends Controller
{
    public function __invoke(Request $request, string $token): View
    {
        return view('storefront.account.reset-password', [
            'token' => $token,
            'email' => (string) $request->query('email', ''),
        ]);
    }
}
