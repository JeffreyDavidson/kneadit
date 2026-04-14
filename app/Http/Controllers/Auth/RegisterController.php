<?php

namespace App\Http\Controllers\Auth;

use App\Actions\Staff\CreateUser;
use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\RegisterRequest;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Auth;

class RegisterController extends Controller
{
    public function store(RegisterRequest $request, CreateUser $createUser): RedirectResponse
    {
        $user = $createUser($request->validated());

        session(['bakery_name' => $request->validated('bakery_name')]);

        Auth::login($user);

        return to_route('billing.plans');
    }

    public function show(): View
    {
        return view('auth.register');
    }
}
