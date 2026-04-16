<x-layouts.storefront>

<link rel="stylesheet" href="{{ asset('css/catering.css') }}">

{{-- Photo-Forward Hero --}}
<x-storefront.hero-section :image="$settings->cateringHeroImageUrl()" image-alt="Catering spread" image-class="catering-hero-img">
    <div class="relative z-10 flex flex-col justify-end min-h-[55vh] max-w-4xl mx-auto text-center px-4 pb-20">
        <x-storefront.eyebrow line-opacity="0.4" class="catering-fade-1 mb-6">{{ $content['hero_eyebrow'] ?? 'Premium Catering' }}</x-storefront.eyebrow>
        <h1 class="catering-fade-2 font-display text-4xl md:text-6xl font-bold mb-6 leading-tight text-white">
            {{ $content['hero_title'] ?? 'Events & Catering' }}
        </h1>
        <p class="catering-fade-2 font-script text-2xl md:text-3xl mb-10 text-warm-400">
            {{ $content['hero_subtitle'] ?? 'Let us make your celebration unforgettable' }}
        </p>
        <div class="catering-fade-3">
            <a href="#inquiry-form" class="inline-block px-10 py-4 rounded-full font-semibold text-lg transition-all duration-300 hover:scale-105 hover:shadow-lg bg-warm-500 text-warm-900">
                {{ $content['hero_button'] ?? 'Request a Quote' }}
            </a>
        </div>
    </div>
</x-storefront.hero-section>

{{-- What We Offer --}}
<section class="py-20 px-4 bg-warm-50">
    <div class="max-w-5xl mx-auto">
        <div class="text-center mb-14">
            <p class="uppercase tracking-[0.25em] text-xs font-semibold mb-2 text-warm-500">{{ $content['occasions_eyebrow'] ?? 'What We Offer' }}</p>
            <h2 class="font-display text-3xl md:text-4xl font-bold text-warm-900">{{ $content['occasions_heading'] ?? 'Perfect for Every Occasion' }}</h2>
        </div>

        @php
            $occasionSvgs = [
                'M12 21C12 21 4 14.36 4 8.5C4 5.42 6.42 3 9.5 3C11.24 3 12.91 3.81 14 5.09C15.09 3.81 16.76 3 18.5 3C21.58 3 24 5.42 24 8.5C24 14.36 16 21 16 21',
                'M3 21V5a2 2 0 012-2h14a2 2 0 012 2v16l-4-3H5a2 2 0 01-2-2z',
                'M12 2l3.09 6.26L22 9.27l-5 4.87L18.18 22 12 18.56 5.82 22 7 14.14l-5-4.87 6.91-1.01L12 2z',
            ];
        @endphp
        <div class="grid md:grid-cols-3 gap-8">
            @foreach ($occasions as $i => $occasion)
            <div class="rounded-2xl p-8 text-center transition-all duration-300 hover:shadow-xl hover:-translate-y-1 bg-white border border-warm-200">
                <div class="w-16 h-16 rounded-full mx-auto mb-5 flex items-center justify-center bg-warm-100">
                    <svg class="w-7 h-7" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24" class="text-warm-500"><path stroke-linecap="round" stroke-linejoin="round" d="{{ $occasionSvgs[$i] ?? $occasionSvgs[0] }}"/></svg>
                </div>
                <h3 class="font-display text-xl font-bold mb-3 text-warm-900">{{ $occasion['title'] }}</h3>
                <p class="text-warm-600">{{ $occasion['description'] }}</p>
            </div>
            @endforeach
        </div>
    </div>
</section>

{{-- How It Works --}}
<x-storefront.dark-section :show-radial="false" padding="py-20">
    <div class="max-w-4xl mx-auto px-4">
        <div class="text-center mb-14">
            <p class="uppercase tracking-[0.25em] text-xs font-semibold mb-2 text-warm-500">{{ $content['process_eyebrow'] ?? 'Simple Process' }}</p>
            <h2 class="font-display text-3xl md:text-4xl font-bold text-white">{{ $content['process_heading'] ?? 'How It Works' }}</h2>
        </div>

        <div class="grid sm:grid-cols-2 lg:grid-cols-4 gap-8">
            @foreach ($processSteps as $i => $step)
            <div class="text-center">
                <div class="w-14 h-14 rounded-full mx-auto mb-4 flex items-center justify-center font-display text-xl font-bold bg-warm-500 text-warm-900">{{ $i + 1 }}</div>
                <h3 class="font-display text-lg font-bold mb-2 text-warm-200">{{ $step['title'] }}</h3>
                <p class="text-sm text-warm-500">{{ $step['description'] }}</p>
            </div>
            @endforeach
        </div>
    </div>
</x-storefront.dark-section>

@if (isset($cateringPhotos) && $cateringPhotos->count())
{{-- Past Events Gallery --}}
<section class="py-20 px-4 bg-warm-50">
    <div class="max-w-5xl mx-auto">
        <div class="text-center mb-10">
            <p class="uppercase tracking-[0.25em] text-xs font-semibold mb-2 text-warm-500">Our Work</p>
            <h2 class="font-display text-3xl font-bold text-warm-900">Past Events</h2>
        </div>
        <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
            @foreach ($cateringPhotos as $photo)
            <div class="aspect-square rounded-2xl overflow-hidden">
                <img src="{{ Storage::url($photo->photo_path) }}" alt="{{ $photo->caption ?? 'Catering event' }}" class="w-full h-full object-cover hover:scale-110 transition-transform duration-500">
            </div>
            @endforeach
        </div>
    </div>
</section>
@endif

{{-- Testimonial --}}
<section class="py-20 px-4 bg-white">
    <div class="max-w-3xl mx-auto">
        <div class="rounded-2xl p-12 text-center relative overflow-hidden bg-warm-900">
            <x-storefront.grain-texture />
            <div class="relative z-10">

                <p class="font-script text-2xl mb-6 text-warm-500">{{ $content['testimonial_script'] ?? 'What our clients say' }}</p>
                <p class="text-xl italic leading-relaxed mb-6 text-warm-200">
                    "{{ $content['testimonial_quote'] ?? 'The dessert spread at our wedding was absolutely stunning. Every guest raved about the pastries and the cake was a masterpiece. We couldn\'t have asked for a better experience!' }}"
                </p>
                <p class="font-display font-semibold text-warm-500">— {{ $content['testimonial_attribution'] ?? 'A Happy Couple' }}</p>
            </div>
        </div>
    </div>
</section>

{{-- Inquiry Form --}}
<section id="inquiry-form" class="py-20 px-4 bg-warm-50">
    <div class="max-w-3xl mx-auto">
        <div class="text-center mb-10">
            <p class="uppercase tracking-[0.25em] text-xs font-semibold mb-2 text-warm-500">{{ $content['form_eyebrow'] ?? 'Ready to get started?' }}</p>
            <h2 class="font-display text-3xl md:text-4xl font-bold mb-3 text-warm-900">{{ $content['form_heading'] ?? 'Request a Quote' }}</h2>
            <p class="text-warm-600">Minimum {{ $settings->cateringMinimumGuests }} guests · Please allow at least {{ $settings->cateringLeadTimeDays }} days lead time</p>
        </div>

        @session('success')
        <x-storefront.alert variant="light" :dismiss-after="5000"><p class="font-semibold">{{ $value }}</p></x-storefront.alert>
        @endsession

        @if ($errors->any())
        <x-storefront.alert type="error" variant="light">
            <ul class="list-disc list-inside">
                @foreach ($errors->all() as $error)
                <li>{{ $error }}</li>
                @endforeach
            </ul>
        </x-storefront.alert>
        @endif

        <form method="POST" action="{{ route('catering.submit') }}" class="rounded-2xl p-8 md:p-10" style="background: white; border: 1px solid var(--warm-200); box-shadow: 0 25px 50px -12px rgba(0,0,0,0.06);" x-data="{ submitting: false }" @submit="submitting = true">
            @csrf

            <div class="grid md:grid-cols-2 gap-6">
                <div>
                    <label class="block mb-2 font-semibold text-sm text-warm-700">Your Name *</label>
                    <input type="text" name="customer_name" value="{{ old('customer_name') }}" required class="input-field">
                </div>
                <div>
                    <label class="block mb-2 font-semibold text-sm text-warm-700">Email *</label>
                    <input type="email" name="customer_email" value="{{ old('customer_email') }}" required class="input-field">
                </div>
                <div>
                    <label class="block mb-2 font-semibold text-sm text-warm-700">Phone</label>
                    <input type="tel" name="customer_phone" value="{{ old('customer_phone') }}" class="input-field">
                </div>
                <div>
                    <label class="block mb-2 font-semibold text-sm text-warm-700">Event Type *</label>
                    <select name="event_type" required class="input-field">
                        <option value="">Select event type...</option>
                        @foreach ($settings->cateringEventTypes as $eventType)
                            <option value="{{ $eventType }}" @selected(old('event_type') === $eventType)>{{ $eventType }}</option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <label class="block mb-2 font-semibold text-sm text-warm-700">Event Date *</label>
                    <input type="date" name="event_date" value="{{ old('event_date') }}" required min="{{ now()->addDays((int) $settings->cateringLeadTimeDays)->format('Y-m-d') }}" class="input-field">
                </div>
                <div>
                    <label class="block mb-2 font-semibold text-sm text-warm-700">Number of Guests *</label>
                    <input type="number" name="guest_count" value="{{ old('guest_count') }}" required min="{{ $settings->cateringMinimumGuests }}" class="input-field" placeholder="Minimum {{ $settings->cateringMinimumGuests }}">
                </div>
                <div class="md:col-span-2">
                    <label class="block mb-2 font-semibold text-sm text-warm-700">Budget Range</label>
                    <input type="text" name="budget" value="{{ old('budget') }}" class="input-field" placeholder="e.g. $500 - $1000 (optional)">
                </div>
                <div class="md:col-span-2">
                    <label class="block mb-2 font-semibold text-sm text-warm-700">Tell Us What You'd Like *</label>
                    <textarea name="details" required rows="4" class="input-field" placeholder="Describe what baked goods you'd like, any themes, special requests...">{{ old('details') }}</textarea>
                </div>
                <div class="md:col-span-2">
                    <label class="block mb-2 font-semibold text-sm text-warm-700">Dietary Requirements</label>
                    <textarea name="dietary_requirements" rows="2" class="input-field" placeholder="Allergies, gluten-free, vegan, etc.">{{ old('dietary_requirements') }}</textarea>
                </div>
                <div class="md:col-span-2">
                    <label class="block mb-2 font-semibold text-sm text-warm-700">Venue Address</label>
                    <textarea name="venue_address" rows="2" class="input-field" placeholder="Where should we deliver?">{{ old('venue_address') }}</textarea>
                </div>
            </div>

            <div class="mt-8 text-center">
                <button type="submit" class="px-12 py-4 rounded-full font-bold text-lg transition-all duration-300 hover:scale-105 hover:shadow-lg bg-warm-500 text-warm-900 disabled:opacity-50 disabled:cursor-not-allowed inline-flex items-center gap-2" :disabled="submitting">
                    <span class="spinner" x-show="submitting" x-cloak></span>
                    <span x-text="submitting ? 'Submitting...' : {{ Js::from($content['submit_button'] ?? 'Submit Inquiry') }}"></span>
                </button>
            </div>
        </form>
    </div>
</section>
</x-layouts.storefront>
