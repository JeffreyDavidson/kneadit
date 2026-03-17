<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class LoginController extends Controller
{
    public function showLoginForm()
    {
        return view('auth.login');
    }

    public function login(Request $request)
    {
        $request->validate([
            'email' => 'required|string|email',
            'password' => 'required|string',
        ]);

        $credentials = $request->only('email', 'password');
        $remember = $request->boolean('remember');

        if (! Auth::attempt($credentials, $remember)) {
            return back()->withErrors([
                'email' => 'These credentials do not match our records.',
            ])->onlyInput('email');
        }

        $request->session()->regenerate();

        $user = Auth::user();

        // If no subscription, go to billing
        if (! $user->subscribed('default')) {
            return redirect('/billing/plans');
        }

        // If subscribed but no tenant (not onboarded), go to onboarding
        if ($user->tenants()->count() === 0) {
            return redirect('/onboarding');
        }

        // Fully set up — go to tenant admin
        $tenant = $user->tenants()->first();

        return redirect("https://{$tenant->id}.".config('app.central_domain', 'getkneadit.app').'/admin');
    }

    public function logout(Request $request)
    {
        Auth::logout();

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect('/');
    }
}
