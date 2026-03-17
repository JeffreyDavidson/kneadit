<?php

namespace App\Http\Controllers\Storefront;

use App\Http\Controllers\Controller;
use App\Models\CateringInquiry;
use App\Models\CustomerPhoto;
use App\Models\Setting;
use Illuminate\Http\Request;

class CateringController extends Controller
{
    public function catering()
    {
        $cateringPhotos = CustomerPhoto::where('caption', 'like', '%catering%')
            ->where('is_approved', true)
            ->latest()
            ->take(8)
            ->get();

        return view('catering', compact('cateringPhotos'));
    }

    public function submitCateringInquiry(Request $request)
    {
        $minimumGuests = (int) Setting::get('catering_minimum_guests', '10');
        $leadTimeDays = (int) Setting::get('catering_lead_time_days', '14');

        $validated = $request->validate([
            'customer_name' => 'required|string|max:255',
            'customer_email' => 'required|email|max:255',
            'customer_phone' => 'nullable|string|max:255',
            'event_type' => 'required|in:wedding,corporate,birthday,holiday,other',
            'event_date' => 'required|date|after_or_equal:'.now()->addDays($leadTimeDays)->format('Y-m-d'),
            'guest_count' => 'required|integer|min:'.$minimumGuests,
            'budget' => 'nullable|string|max:255',
            'details' => 'required|string',
            'dietary_requirements' => 'nullable|string',
            'venue_address' => 'nullable|string',
        ]);

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

        CateringInquiry::create($validated);

        return redirect()->route('storefront.catering')
            ->with('success', 'Thank you for your inquiry! We\'ll review your request and get back to you with a custom quote soon.');
    }
}
