<?php

namespace App\Http\Controllers\Storefront;

use App\Actions\Customers\SubmitContactMessage;
use App\Http\Controllers\Controller;
use App\Http\Requests\Storefront\StoreContactMessageRequest;
use App\Services\Settings\TenantSettings;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;

class ContactController extends Controller
{
    public function store(StoreContactMessageRequest $request, SubmitContactMessage $submitMessage): RedirectResponse
    {
        $submitMessage($request->validated());

        $message = settingsPageContent('contact')['flash_success']
            ?? "Thank you for your message! We'll get back to you soon.";

        return back()->with('success', $message);
    }

    public function show(TenantSettings $settings): View
    {
        return view('storefront.contact', [
            'settings' => $settings,
            'content' => settingsPageContent('contact'),
        ]);
    }
}
