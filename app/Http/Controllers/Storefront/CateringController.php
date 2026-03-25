<?php

namespace App\Http\Controllers\Storefront;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreCateringInquiryRequest;
use App\Models\CateringInquiry;
use App\Models\CustomerPhoto;
use App\Models\Setting;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;

class CateringController extends Controller
{
    public function show(): View
    {
        $cateringPhotos = CustomerPhoto::query()->whereLike('caption', '%catering%')
            ->where('is_approved', true)
            ->latest()
            ->take(8)
            ->get();

        return view('catering', compact('cateringPhotos'));
    }

    public function store(StoreCateringInquiryRequest $request): RedirectResponse
    {
        $minimumGuests = (int) Setting::get('catering_minimum_guests', '10');
        $leadTimeDays = (int) Setting::get('catering_lead_time_days', '14');

        $validated = $request->validated();

        $validated['details'] = strip_tags($validated['details']);
        if (isset($validated['budget'])) {
            $validated['budget'] = strip_tags($validated['budget']);
        }
        if (isset($validated['dietary_requirements'])) {
            $validated['dietary_requirements'] = strip_tags($validated['dietary_requirements']);
        }
        if (isset($validated['venue_address'])) {
            $validated['venue_address'] = strip_tags($validated['venue_address']);
        }

        CateringInquiry::query()->create($validated);

        return to_route('storefront.catering')
            ->with('success', 'Thank you for your inquiry! We\'ll review your request and get back to you with a custom quote soon.');
    }
}
