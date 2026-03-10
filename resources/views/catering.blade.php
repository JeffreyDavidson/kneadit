@extends('layouts.storefront')

@section('content')
@php
    $storeName = \App\Models\Setting::get('store_name', 'Our Bakery');
    $minimumGuests = \App\Models\Setting::get('catering_minimum_guests', '10');
    $leadTimeDays = \App\Models\Setting::get('catering_lead_time_days', '14');
    $heroImage = \App\Models\Setting::get('catering_hero_image');
    $heroImageUrl = $heroImage ? Storage::url($heroImage) : 'https://images.unsplash.com/photo-1555244162-803834f70033?w=1920&q=80';
@endphp

<style>
    @keyframes cateringKenBurns { 0% { transform: scale(1); } 100% { transform: scale(1.06); } }
    @keyframes cateringFadeUp { from { opacity: 0; transform: translateY(30px); } to { opacity: 1; transform: translateY(0); } }
    .catering-hero-img { animation: cateringKenBurns 25s ease-in-out infinite alternate; }
    .catering-fade-1 { animation: cateringFadeUp 0.8s ease-out 0.3s both; }
    .catering-fade-2 { animation: cateringFadeUp 0.8s ease-out 0.5s both; }
    .catering-fade-3 { animation: cateringFadeUp 0.8s ease-out 0.7s both; }
</style>

{{-- Photo-Forward Hero --}}
<section class="relative overflow-hidden" style="min-height: 55vh;">
    <div class="absolute inset-0">
        <img src="{{ $heroImageUrl }}" alt="Catering spread" class="w-full h-full object-cover catering-hero-img">
    </div>
    <div class="absolute inset-0" style="background: linear-gradient(to bottom, rgba(28,20,16,0.4) 0%, rgba(28,20,16,0.65) 50%, rgba(28,20,16,0.95) 100%);"></div>
    <div class="absolute inset-0 opacity-[0.03]" style="background-image: url('data:image/svg+xml,%3Csvg viewBox=%220 0 256 256%22 xmlns=%22http://www.w3.org/2000/svg%22%3E%3Cfilter id=%22noise%22%3E%3CfeTurbulence type=%22fractalNoise%22 baseFrequency=%220.9%22 numOctaves=%224%22 stitchTiles=%22stitch%22/%3E%3C/filter%3E%3Crect width=%22100%25%22 height=%22100%25%22 filter=%22url(%23noise)%22/%3E%3C/svg%3E');"></div>

    <div class="relative z-10 flex flex-col justify-end min-h-[55vh] max-w-4xl mx-auto text-center px-4 pb-20">
        <div class="catering-fade-1 flex items-center justify-center gap-4 mb-6">
            <span class="block w-8 h-px" style="background: var(--warm-500); opacity: 0.4;"></span>
            <span class="uppercase tracking-[0.25em] text-xs font-semibold" style="color: var(--warm-500);">Premium Catering</span>
            <span class="block w-8 h-px" style="background: var(--warm-500); opacity: 0.4;"></span>
        </div>
        <h1 class="catering-fade-2 font-display text-4xl md:text-6xl font-bold mb-6 leading-tight" style="color: white;">
            Events & Catering
        </h1>
        <p class="catering-fade-2 font-script text-2xl md:text-3xl mb-10" style="color: var(--warm-400);">
            Let us make your celebration unforgettable
        </p>
        <div class="catering-fade-3">
            <a href="#inquiry-form" class="inline-block px-10 py-4 rounded-full font-semibold text-lg transition-all duration-300 hover:scale-105 hover:shadow-2xl" style="background: var(--warm-500); color: var(--warm-900);">
                Request a Quote
            </a>
        </div>
    </div>
</section>

{{-- What We Offer --}}
<section class="py-20 px-4" style="background: var(--warm-50);">
    <div class="max-w-5xl mx-auto">
        <div class="text-center mb-14">
            <p class="uppercase tracking-[0.25em] text-xs font-semibold mb-2" style="color: var(--warm-500);">What We Offer</p>
            <h2 class="font-display text-3xl md:text-4xl font-bold" style="color: var(--warm-900);">Perfect for Every Occasion</h2>
        </div>

        <div class="grid md:grid-cols-3 gap-8">
            @foreach([
                ['M12 21C12 21 4 14.36 4 8.5C4 5.42 6.42 3 9.5 3C11.24 3 12.91 3.81 14 5.09C15.09 3.81 16.76 3 18.5 3C21.58 3 24 5.42 24 8.5C24 14.36 16 21 16 21', 'Weddings', 'Custom wedding cakes, dessert tables, pastry towers, and sweet treats to make your big day even sweeter.'],
                ['M3 21V5a2 2 0 012-2h14a2 2 0 012 2v16l-4-3H5a2 2 0 01-2-2z', 'Corporate Events', 'Professional catering for meetings, launches, office parties, and team celebrations.'],
                ['M12 2l3.09 6.26L22 9.27l-5 4.87L18.18 22 12 18.56 5.82 22 7 14.14l-5-4.87 6.91-1.01L12 2z', 'Parties & Celebrations', 'Birthday parties, holiday gatherings, baby showers — we bring the sweetness to any celebration.'],
            ] as [$svgPath, $title, $desc])
            <div class="rounded-2xl p-8 text-center transition-all duration-300 hover:shadow-xl hover:-translate-y-1" style="background: white; border: 1px solid var(--warm-200);">
                <div class="w-16 h-16 rounded-full mx-auto mb-5 flex items-center justify-center" style="background: var(--warm-100);">
                    <svg class="w-7 h-7" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24" style="color: var(--warm-500);"><path stroke-linecap="round" stroke-linejoin="round" d="{{ $svgPath }}"/></svg>
                </div>
                <h3 class="font-display text-xl font-bold mb-3" style="color: var(--warm-900);">{{ $title }}</h3>
                <p style="color: var(--warm-600);">{{ $desc }}</p>
            </div>
            @endforeach
        </div>
    </div>
</section>

{{-- How It Works --}}
<section class="relative overflow-hidden py-20 px-4" style="background: var(--warm-900);">
    <div class="absolute inset-0 opacity-[0.03]" style="background-image: url('data:image/svg+xml,%3Csvg viewBox=%220 0 256 256%22 xmlns=%22http://www.w3.org/2000/svg%22%3E%3Cfilter id=%22noise%22%3E%3CfeTurbulence type=%22fractalNoise%22 baseFrequency=%220.9%22 numOctaves=%224%22 stitchTiles=%22stitch%22/%3E%3C/filter%3E%3Crect width=%22100%25%22 height=%22100%25%22 filter=%22url(%23noise)%22/%3E%3C/svg%3E');"></div>
    <div class="relative z-10 max-w-4xl mx-auto">
        <div class="text-center mb-14">
            <p class="uppercase tracking-[0.25em] text-xs font-semibold mb-2" style="color: var(--warm-500);">Simple Process</p>
            <h2 class="font-display text-3xl md:text-4xl font-bold" style="color: white;">How It Works</h2>
        </div>

        <div class="grid sm:grid-cols-2 lg:grid-cols-4 gap-8">
            @foreach([
                ['1', 'Tell Us About Your Event', 'Fill out the inquiry form with your event details.'],
                ['2', 'Get a Custom Quote', "We'll craft a personalized quote based on your needs."],
                ['3', 'Confirm Your Order', 'Review and confirm — we handle the rest.'],
                ['4', 'Enjoy!', 'Fresh, beautiful baked goods delivered for your event.'],
            ] as [$num, $title, $desc])
            <div class="text-center">
                <div class="w-14 h-14 rounded-full mx-auto mb-4 flex items-center justify-center font-display text-xl font-bold" style="background: var(--warm-500); color: var(--warm-900);">{{ $num }}</div>
                <h3 class="font-display text-lg font-bold mb-2" style="color: var(--warm-200);">{{ $title }}</h3>
                <p class="text-sm" style="color: var(--warm-500);">{{ $desc }}</p>
            </div>
            @endforeach
        </div>
    </div>
</section>

@if(isset($cateringPhotos) && $cateringPhotos->count())
{{-- Past Events Gallery --}}
<section class="py-20 px-4" style="background: var(--warm-50);">
    <div class="max-w-5xl mx-auto">
        <div class="text-center mb-10">
            <p class="uppercase tracking-[0.25em] text-xs font-semibold mb-2" style="color: var(--warm-500);">Our Work</p>
            <h2 class="font-display text-3xl font-bold" style="color: var(--warm-900);">Past Events</h2>
        </div>
        <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
            @foreach($cateringPhotos as $photo)
            <div class="aspect-square rounded-2xl overflow-hidden">
                <img src="{{ Storage::url($photo->photo) }}" alt="{{ $photo->caption ?? 'Catering event' }}" class="w-full h-full object-cover hover:scale-110 transition-transform duration-500">
            </div>
            @endforeach
        </div>
    </div>
</section>
@endif

{{-- Testimonial --}}
<section class="py-20 px-4" style="background: white;">
    <div class="max-w-3xl mx-auto">
        <div class="rounded-2xl p-12 text-center relative overflow-hidden" style="background: var(--warm-900);">
            <div class="absolute inset-0 opacity-[0.03]" style="background-image: url('data:image/svg+xml,%3Csvg viewBox=%220 0 256 256%22 xmlns=%22http://www.w3.org/2000/svg%22%3E%3Cfilter id=%22noise%22%3E%3CfeTurbulence type=%22fractalNoise%22 baseFrequency=%220.9%22 numOctaves=%224%22 stitchTiles=%22stitch%22/%3E%3C/filter%3E%3Crect width=%22100%25%22 height=%22100%25%22 filter=%22url(%23noise)%22/%3E%3C/svg%3E');"></div>
            <div class="relative z-10">
                <p class="font-script text-2xl mb-6" style="color: var(--warm-500);">What our clients say</p>
                <p class="text-xl italic leading-relaxed mb-6" style="color: var(--warm-200);">
                    "The dessert spread at our wedding was absolutely stunning. Every guest raved about the pastries and the cake was a masterpiece. We couldn't have asked for a better experience!"
                </p>
                <p class="font-display font-semibold" style="color: var(--warm-500);">— A Happy Couple</p>
            </div>
        </div>
    </div>
</section>

{{-- Inquiry Form --}}
<section id="inquiry-form" class="py-20 px-4" style="background: var(--warm-50);">
    <div class="max-w-3xl mx-auto">
        <div class="text-center mb-10">
            <p class="uppercase tracking-[0.25em] text-xs font-semibold mb-2" style="color: var(--warm-500);">Ready to get started?</p>
            <h2 class="font-display text-3xl md:text-4xl font-bold mb-3" style="color: var(--warm-900);">Request a Quote</h2>
            <p style="color: var(--warm-600);">Minimum {{ $minimumGuests }} guests · Please allow at least {{ $leadTimeDays }} days lead time</p>
        </div>

        @if(session('success'))
        <div class="rounded-2xl p-6 mb-8 text-center" style="background: #d4edda; border: 1px solid #28a745;">
            <p style="color: #155724; font-weight: 600;">{{ session('success') }}</p>
        </div>
        @endif

        @if($errors->any())
        <div class="rounded-2xl p-6 mb-8" style="background: #f8d7da; border: 1px solid #dc3545;">
            <ul class="list-disc list-inside" style="color: #721c24;">
                @foreach($errors->all() as $error)
                <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
        @endif

        <form method="POST" action="{{ route('catering.submit') }}" class="rounded-2xl p-8 md:p-10" style="background: white; border: 1px solid var(--warm-200); box-shadow: 0 25px 50px -12px rgba(0,0,0,0.06);">
            @csrf

            <div class="grid md:grid-cols-2 gap-6">
                <div>
                    <label class="block mb-2 font-semibold text-sm" style="color: var(--warm-700);">Your Name *</label>
                    <input type="text" name="customer_name" value="{{ old('customer_name') }}" required class="input-field">
                </div>
                <div>
                    <label class="block mb-2 font-semibold text-sm" style="color: var(--warm-700);">Email *</label>
                    <input type="email" name="customer_email" value="{{ old('customer_email') }}" required class="input-field">
                </div>
                <div>
                    <label class="block mb-2 font-semibold text-sm" style="color: var(--warm-700);">Phone</label>
                    <input type="tel" name="customer_phone" value="{{ old('customer_phone') }}" class="input-field">
                </div>
                <div>
                    <label class="block mb-2 font-semibold text-sm" style="color: var(--warm-700);">Event Type *</label>
                    <select name="event_type" required class="input-field">
                        <option value="">Select event type...</option>
                        <option value="wedding" {{ old('event_type') === 'wedding' ? 'selected' : '' }}>Wedding</option>
                        <option value="corporate" {{ old('event_type') === 'corporate' ? 'selected' : '' }}>Corporate Event</option>
                        <option value="birthday" {{ old('event_type') === 'birthday' ? 'selected' : '' }}>Birthday Party</option>
                        <option value="holiday" {{ old('event_type') === 'holiday' ? 'selected' : '' }}>Holiday Gathering</option>
                        <option value="other" {{ old('event_type') === 'other' ? 'selected' : '' }}>Other</option>
                    </select>
                </div>
                <div>
                    <label class="block mb-2 font-semibold text-sm" style="color: var(--warm-700);">Event Date *</label>
                    <input type="date" name="event_date" value="{{ old('event_date') }}" required min="{{ now()->addDays((int) $leadTimeDays)->format('Y-m-d') }}" class="input-field">
                </div>
                <div>
                    <label class="block mb-2 font-semibold text-sm" style="color: var(--warm-700);">Number of Guests *</label>
                    <input type="number" name="guest_count" value="{{ old('guest_count') }}" required min="{{ $minimumGuests }}" class="input-field" placeholder="Minimum {{ $minimumGuests }}">
                </div>
                <div class="md:col-span-2">
                    <label class="block mb-2 font-semibold text-sm" style="color: var(--warm-700);">Budget Range</label>
                    <input type="text" name="budget" value="{{ old('budget') }}" class="input-field" placeholder="e.g. $500 - $1000 (optional)">
                </div>
                <div class="md:col-span-2">
                    <label class="block mb-2 font-semibold text-sm" style="color: var(--warm-700);">Tell Us What You'd Like *</label>
                    <textarea name="details" required rows="4" class="input-field" placeholder="Describe what baked goods you'd like, any themes, special requests...">{{ old('details') }}</textarea>
                </div>
                <div class="md:col-span-2">
                    <label class="block mb-2 font-semibold text-sm" style="color: var(--warm-700);">Dietary Requirements</label>
                    <textarea name="dietary_requirements" rows="2" class="input-field" placeholder="Allergies, gluten-free, vegan, etc.">{{ old('dietary_requirements') }}</textarea>
                </div>
                <div class="md:col-span-2">
                    <label class="block mb-2 font-semibold text-sm" style="color: var(--warm-700);">Venue Address</label>
                    <textarea name="venue_address" rows="2" class="input-field" placeholder="Where should we deliver?">{{ old('venue_address') }}</textarea>
                </div>
            </div>

            <div class="mt-8 text-center">
                <button type="submit" class="px-12 py-4 rounded-full font-bold text-lg transition-all duration-300 hover:scale-105 hover:shadow-2xl" style="background: var(--warm-500); color: var(--warm-900);">
                    Submit Inquiry
                </button>
            </div>
        </form>
    </div>
</section>
@endsection
