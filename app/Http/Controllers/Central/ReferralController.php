<?php

namespace App\Http\Controllers\Central;

use App\Http\Controllers\Controller;
use App\Models\Customers\Referral;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class ReferralController extends Controller
{
    public function __invoke(Request $request, string $code): RedirectResponse
    {
        $referral = Referral::query()->where('referral_code', $code)->first();

        if ($referral) {
            session([
                'referral_code' => $code,
            ]);
            cookie()->queue('referral_code', $code, 60 * 24 * 30); // 30 days
        }

        return to_route('register');
    }
}
