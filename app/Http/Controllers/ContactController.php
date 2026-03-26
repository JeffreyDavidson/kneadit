<?php

namespace App\Http\Controllers;

use App\Actions\Customers\SubmitContactMessage;
use App\Http\Requests\StoreContactMessageRequest;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;

class ContactController extends Controller
{
    public function show(): View
    {
        return view('contact');
    }

    public function store(StoreContactMessageRequest $request, SubmitContactMessage $submitMessage): RedirectResponse
    {
        $submitMessage($request->validated());

        return back()->with('success', 'Thank you for your message! We\'ll get back to you soon.');
    }
}
