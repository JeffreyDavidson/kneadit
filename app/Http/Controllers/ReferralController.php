<?php

namespace App\Http\Controllers;

use App\Models\Referral;
use Illuminate\Http\Request;

class ReferralController extends Controller
{
    public function track(Request $request, string $code)
    {
        $referral = Referral::where('referral_code', $code)->first();

        if ($referral) {
            session(['referral_code' => $code]);
            cookie()->queue('referral_code', $code, 60 * 24 * 30); // 30 days
        }

        return to_route('register');
    }
}
