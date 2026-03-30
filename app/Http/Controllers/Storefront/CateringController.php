<?php

namespace App\Http\Controllers\Storefront;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreCateringInquiryRequest;
use App\Models\CateringInquiry;
use App\Models\CustomerPhoto;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Storage;

class CateringController extends Controller
{
    public function show(): View
    {
        $cateringPhotos = CustomerPhoto::query()->whereLike('caption', '%catering%')
            ->where('is_approved', true)
            ->latest()
            ->take(8)
            ->get();

        $storeName = settings('store_name', 'Our Bakery');
        $minimumGuests = settings('catering_minimum_guests', '10');
        $leadTimeDays = settings('catering_lead_time_days', '14');
        $heroImage = settings('catering_hero_image');
        $heroImageUrl = $heroImage ? Storage::url($heroImage) : 'https://images.unsplash.com/photo-1555244162-803834f70033?w=1920&q=80';
        $content = settingsPageContent('catering');

        $occasions = $content['occasions'] ?? [
            ['title' => 'Weddings', 'description' => 'Custom wedding cakes, dessert tables, pastry towers, and sweet treats to make your big day even sweeter.'],
            ['title' => 'Corporate Events', 'description' => 'Professional catering for meetings, launches, office parties, and team celebrations.'],
            ['title' => 'Parties & Celebrations', 'description' => 'Birthday parties, holiday gatherings, baby showers — we bring the sweetness to any celebration.'],
        ];
        $occasionSvgs = [
            'M12 21C12 21 4 14.36 4 8.5C4 5.42 6.42 3 9.5 3C11.24 3 12.91 3.81 14 5.09C15.09 3.81 16.76 3 18.5 3C21.58 3 24 5.42 24 8.5C24 14.36 16 21 16 21',
            'M3 21V5a2 2 0 012-2h14a2 2 0 012 2v16l-4-3H5a2 2 0 01-2-2z',
            'M12 2l3.09 6.26L22 9.27l-5 4.87L18.18 22 12 18.56 5.82 22 7 14.14l-5-4.87 6.91-1.01L12 2z',
        ];
        $processSteps = $content['process_steps'] ?? [
            [
                'title' => 'Tell Us About Your Event',
                'description' => 'Fill out the inquiry form with your event details.',
            ],
            [
                'title' => 'Get a Custom Quote',
                'description' => "We'll craft a personalized quote based on your needs.",
            ],
            [
                'title' => 'Confirm Your Order',
                'description' => 'Review and confirm — we handle the rest.',
            ],
            ['title' => 'Enjoy!', 'description' => 'Fresh, beautiful baked goods delivered for your event.'],
        ];

        return view('catering', [
            'cateringPhotos' => $cateringPhotos,
            'storeName' => $storeName,
            'minimumGuests' => $minimumGuests,
            'leadTimeDays' => $leadTimeDays,
            'heroImageUrl' => $heroImageUrl,
            'content' => $content,
            'occasions' => $occasions,
            'occasionSvgs' => $occasionSvgs,
            'processSteps' => $processSteps,
        ]);
    }

    public function store(StoreCateringInquiryRequest $request): RedirectResponse
    {

        $validated = $request->validated();

        CateringInquiry::query()->create($validated);

        return to_route('storefront.catering')
            ->with('success', 'Thank you for your inquiry! We\'ll review your request and get back to you with a custom quote soon.');
    }
}
