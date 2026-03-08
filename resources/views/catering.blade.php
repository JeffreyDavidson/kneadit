@extends('layouts.storefront')

@section('content')
@php
    $storeName = \App\Models\Setting::get('store_name', 'Our Bakery');
    $minimumGuests = \App\Models\Setting::get('catering_minimum_guests', '10');
    $leadTimeDays = \App\Models\Setting::get('catering_lead_time_days', '14');
@endphp

<div class="max-w-7xl mx-auto px-4 py-12">
    <!-- Hero -->
    <div class="text-center mb-20">
        <p class="font-script text-2xl mb-4" style="color: var(--warm-600);">Let us make your event unforgettable</p>
        <h1 class="font-display text-5xl md:text-6xl font-bold mb-6" style="color: var(--warm-900);">
            Catering & Events
        </h1>
        <p class="text-lg max-w-2xl mx-auto" style="color: var(--warm-700);">
            From intimate gatherings to grand celebrations, {{ $storeName }} brings artisan-quality baked goods to your special occasions.
        </p>
    </div>

    <!-- What We Offer -->
    <div class="mb-20">
        <div class="text-center mb-10">
            <p class="font-script text-xl mb-2" style="color: var(--warm-500);">What we offer</p>
            <h2 class="font-display text-3xl font-semibold" style="color: var(--warm-900);">Perfect for Every Occasion</h2>
        </div>

        <div class="grid md:grid-cols-3 gap-8 max-w-5xl mx-auto">
            <div class="card p-8 text-center">
                <div class="text-4xl mb-4">💒</div>
                <h3 class="font-display text-xl font-semibold mb-3" style="color: var(--warm-900);">Weddings</h3>
                <p style="color: var(--warm-700);">Custom wedding cakes, dessert tables, pastry towers, and sweet treats to make your big day even sweeter.</p>
            </div>

            <div class="card p-8 text-center">
                <div class="text-4xl mb-4">🏢</div>
                <h3 class="font-display text-xl font-semibold mb-3" style="color: var(--warm-900);">Corporate Events</h3>
                <p style="color: var(--warm-700);">Professional catering for meetings, launches, office parties, and team celebrations. Impress your clients and team.</p>
            </div>

            <div class="card p-8 text-center">
                <div class="text-4xl mb-4">🎉</div>
                <h3 class="font-display text-xl font-semibold mb-3" style="color: var(--warm-900);">Parties & Celebrations</h3>
                <p style="color: var(--warm-700);">Birthday parties, holiday gatherings, baby showers, and more. We'll bring the sweetness to any celebration.</p>
            </div>
        </div>
    </div>

    <!-- How It Works -->
    <div class="mb-20">
        <div class="text-center mb-10">
            <p class="font-script text-xl mb-2" style="color: var(--warm-500);">Simple as</p>
            <h2 class="font-display text-3xl font-semibold" style="color: var(--warm-900);">How It Works</h2>
        </div>

        <div class="grid md:grid-cols-4 gap-6 max-w-4xl mx-auto">
            @foreach([
                ['1', 'Tell Us About Your Event', 'Fill out the inquiry form below with your event details.'],
                ['2', 'Get a Custom Quote', "We'll craft a personalized quote based on your needs."],
                ['3', 'Confirm Your Order', 'Review and confirm — we handle the rest.'],
                ['4', 'Enjoy!', 'Fresh, beautiful baked goods delivered for your event.'],
            ] as [$num, $title, $desc])
            <div class="text-center">
                <div class="w-12 h-12 rounded-full mx-auto mb-4 flex items-center justify-center font-display text-lg font-bold" style="background: var(--warm-500); color: white;">{{ $num }}</div>
                <h3 class="font-display font-semibold mb-2" style="color: var(--warm-900);">{{ $title }}</h3>
                <p class="text-sm" style="color: var(--warm-700);">{{ $desc }}</p>
            </div>
            @endforeach
        </div>
    </div>

    @if(isset($cateringPhotos) && $cateringPhotos->count())
    <!-- Past Catering Photos -->
    <div class="mb-20">
        <div class="text-center mb-10">
            <p class="font-script text-xl mb-2" style="color: var(--warm-500);">Our work</p>
            <h2 class="font-display text-3xl font-semibold" style="color: var(--warm-900);">Past Events</h2>
        </div>
        <div class="grid grid-cols-2 md:grid-cols-4 gap-4 max-w-5xl mx-auto">
            @foreach($cateringPhotos as $photo)
            <div class="aspect-square rounded-xl overflow-hidden">
                <img src="{{ Storage::url($photo->photo) }}" alt="{{ $photo->caption ?? 'Catering event' }}" class="w-full h-full object-cover hover:scale-105 transition-transform duration-300">
            </div>
            @endforeach
        </div>
    </div>
    @endif

    <!-- Testimonial -->
    <div class="mb-20 max-w-3xl mx-auto">
        <div class="card p-10 text-center" style="background: var(--warm-100);">
            <p class="font-script text-xl mb-4" style="color: var(--warm-500);">What our clients say</p>
            <p class="text-xl italic mb-6" style="color: var(--warm-800);">
                "The dessert spread at our wedding was absolutely stunning. Every guest raved about the pastries and the cake was a masterpiece. We couldn't have asked for a better experience!"
            </p>
            <p class="font-display font-semibold" style="color: var(--warm-600);">— A Happy Couple</p>
        </div>
    </div>

    <!-- Inquiry Form -->
    <div id="inquiry-form" class="max-w-3xl mx-auto mb-20">
        <div class="text-center mb-10">
            <p class="font-script text-xl mb-2" style="color: var(--warm-500);">Ready to get started?</p>
            <h2 class="font-display text-3xl font-semibold" style="color: var(--warm-900);">Request a Quote</h2>
            <p class="mt-2" style="color: var(--warm-700);">Minimum {{ $minimumGuests }} guests · Please allow at least {{ $leadTimeDays }} days lead time</p>
        </div>

        @if(session('success'))
        <div class="card p-6 mb-8 text-center" style="background: #d4edda; border-color: #28a745;">
            <p style="color: #155724; font-weight: 600;">🎉 {{ session('success') }}</p>
        </div>
        @endif

        @if($errors->any())
        <div class="card p-6 mb-8" style="background: #f8d7da; border-color: #dc3545;">
            <ul class="list-disc list-inside" style="color: #721c24;">
                @foreach($errors->all() as $error)
                <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
        @endif

        <form method="POST" action="{{ route('catering.submit') }}" class="card p-8">
            @csrf

            <div class="grid md:grid-cols-2 gap-6">
                <div>
                    <label class="block mb-2 font-display font-semibold text-sm" style="color: var(--warm-900);">Your Name *</label>
                    <input type="text" name="customer_name" value="{{ old('customer_name') }}" required class="input-field">
                </div>

                <div>
                    <label class="block mb-2 font-display font-semibold text-sm" style="color: var(--warm-900);">Email *</label>
                    <input type="email" name="customer_email" value="{{ old('customer_email') }}" required class="input-field">
                </div>

                <div>
                    <label class="block mb-2 font-display font-semibold text-sm" style="color: var(--warm-900);">Phone</label>
                    <input type="tel" name="customer_phone" value="{{ old('customer_phone') }}" class="input-field">
                </div>

                <div>
                    <label class="block mb-2 font-display font-semibold text-sm" style="color: var(--warm-900);">Event Type *</label>
                    <select name="event_type" required class="input-field">
                        <option value="">Select event type...</option>
                        <option value="wedding" {{ old('event_type') === 'wedding' ? 'selected' : '' }}>💒 Wedding</option>
                        <option value="corporate" {{ old('event_type') === 'corporate' ? 'selected' : '' }}>🏢 Corporate Event</option>
                        <option value="birthday" {{ old('event_type') === 'birthday' ? 'selected' : '' }}>🎂 Birthday Party</option>
                        <option value="holiday" {{ old('event_type') === 'holiday' ? 'selected' : '' }}>🎄 Holiday Gathering</option>
                        <option value="other" {{ old('event_type') === 'other' ? 'selected' : '' }}>🎉 Other</option>
                    </select>
                </div>

                <div>
                    <label class="block mb-2 font-display font-semibold text-sm" style="color: var(--warm-900);">Event Date *</label>
                    <input type="date" name="event_date" value="{{ old('event_date') }}" required min="{{ now()->addDays((int) $leadTimeDays)->format('Y-m-d') }}" class="input-field">
                </div>

                <div>
                    <label class="block mb-2 font-display font-semibold text-sm" style="color: var(--warm-900);">Number of Guests *</label>
                    <input type="number" name="guest_count" value="{{ old('guest_count') }}" required min="{{ $minimumGuests }}" class="input-field" placeholder="Minimum {{ $minimumGuests }}">
                </div>

                <div class="md:col-span-2">
                    <label class="block mb-2 font-display font-semibold text-sm" style="color: var(--warm-900);">Budget Range</label>
                    <input type="text" name="budget" value="{{ old('budget') }}" class="input-field" placeholder="e.g. $500 - $1000 (optional)">
                </div>

                <div class="md:col-span-2">
                    <label class="block mb-2 font-display font-semibold text-sm" style="color: var(--warm-900);">Tell Us What You'd Like *</label>
                    <textarea name="details" required rows="4" class="input-field" placeholder="Describe what baked goods you'd like, any themes, special requests...">{{ old('details') }}</textarea>
                </div>

                <div class="md:col-span-2">
                    <label class="block mb-2 font-display font-semibold text-sm" style="color: var(--warm-900);">Dietary Requirements</label>
                    <textarea name="dietary_requirements" rows="2" class="input-field" placeholder="Allergies, gluten-free, vegan, etc.">{{ old('dietary_requirements') }}</textarea>
                </div>

                <div class="md:col-span-2">
                    <label class="block mb-2 font-display font-semibold text-sm" style="color: var(--warm-900);">Venue Address</label>
                    <textarea name="venue_address" rows="2" class="input-field" placeholder="Where should we deliver?">{{ old('venue_address') }}</textarea>
                </div>
            </div>

            <div class="mt-8 text-center">
                <button type="submit" class="btn-primary" style="background: var(--warm-500); color: white; padding: 14px 40px; border-radius: 100px; font-weight: 600; font-size: 16px; border: none; cursor: pointer; transition: all 0.3s ease;">
                    Submit Inquiry ✨
                </button>
            </div>
        </form>
    </div>
</div>
@endsection
