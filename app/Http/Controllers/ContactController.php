<?php

namespace App\Http\Controllers;

use App\Actions\Customers\SubmitContactMessage;
use App\Http\Requests\StoreContactMessageRequest;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Storage;

class ContactController extends Controller
{
    public function show(): View
    {
        $storeName = settings('store_name', 'Our Bakery');
        $heroImage = settings('hero_image');
        $heroImageUrl = $heroImage ? Storage::url($heroImage) : 'https://images.unsplash.com/photo-1509440159596-0249088772ff?w=1920&q=80';
        $storeAddress = settings('store_address');
        $storePhone = settings('store_phone');
        $storeEmail = settings('store_email');
        $operatingHours = json_decode(settings('operating_hours', '{}'), true);
        $faqItems = json_decode(settings('faq_items', '[]'), true);
        $leadTimeHours = settings('order_lead_time_hours', '24');
        $allergyDisclaimer = settings('allergy_disclaimer');
        $content = settingsPageContent('contact');

        return view('contact', compact(
            'storeName', 'heroImageUrl', 'storeAddress', 'storePhone', 'storeEmail',
            'operatingHours', 'faqItems', 'leadTimeHours', 'allergyDisclaimer', 'content',
        ));
    }

    public function store(StoreContactMessageRequest $request, SubmitContactMessage $submitMessage): RedirectResponse
    {
        $submitMessage($request->validated());

        return back()->with('success', 'Thank you for your message! We\'ll get back to you soon.');
    }
}
