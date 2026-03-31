<?php

namespace App\Http\Controllers;

use Illuminate\Contracts\View\View;

class ShowOnboardingController extends Controller
{
    public function __invoke(): View
    {
        return view('onboarding', [
            'bakeryName' => session('bakery_name', ''),
        ]);
    }
}
