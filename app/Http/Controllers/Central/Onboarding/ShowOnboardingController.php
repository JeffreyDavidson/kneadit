<?php

namespace App\Http\Controllers\Central\Onboarding;

use App\Http\Controllers\Controller;
use Illuminate\Contracts\View\View;

class ShowOnboardingController extends Controller
{
    public function __invoke(): View
    {
        return view('central.platform.onboarding', [
            'bakeryName' => session('bakery_name', ''),
        ]);
    }
}
