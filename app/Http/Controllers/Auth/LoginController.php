<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Http\Requests\LoginRequest;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Auth;

class LoginController extends Controller
{
    public function show(): View
    {
        return view('auth.login');
    }

    public function store(LoginRequest $request): RedirectResponse
    {
        $validated = $request->validated();
        $remember = $request->boolean('remember');

        if (! Auth::attempt($validated, $remember)) {
            return back()->withErrors([
                'email' => 'These credentials do not match our records.',
            ])->onlyInput('email');
        }

        $request->session()->regenerate();

        $user = Auth::user();

        if (! $user?->subscribed('default')) {
            return to_route('billing.plans');
        }

        if ($user->tenants()->count() === 0) {
            return to_route('onboarding');
        }

        $tenant = $user->tenants()->first();

        return redirect("https://{$tenant?->id}." . config('app.central_domain', 'getkneadit.app') . '/admin');
    }
}
