@extends('layouts.storefront')

@section('content')
@php
    $storeAddress = \App\Models\Setting::get('store_address');
    $storePhone = \App\Models\Setting::get('store_phone');
    $storeEmail = \App\Models\Setting::get('store_email');
    $operatingHours = json_decode(\App\Models\Setting::get('operating_hours', '{}'), true);
    $faqItems = json_decode(\App\Models\Setting::get('faq_items', '[]'), true);
    $leadTimeHours = \App\Models\Setting::get('order_lead_time_hours', '24');
    $maxDeliveryMiles = \App\Models\Setting::get('max_delivery_distance_miles', '15');
    $allergyDisclaimer = \App\Models\Setting::get('allergy_disclaimer');
@endphp

<div class="max-w-4xl mx-auto px-4 py-12">
    <!-- Header -->
    <div class="text-center mb-12">
        <p class="font-script text-xl mb-2" style="color: var(--warm-500);">We'd love to hear from you</p>
        <h1 class="font-display text-4xl font-bold mb-4" style="color: var(--warm-900);">
            Get In Touch
        </h1>
        <p class="text-lg max-w-2xl mx-auto" style="color: var(--warm-700);">
            Send us a message and we'll respond as soon as possible.
        </p>
    </div>

    <div class="grid md:grid-cols-2 gap-8">
        <!-- Contact Form -->
        <div class="card p-8">
            <h2 class="font-display text-2xl font-semibold mb-6" style="color: var(--warm-900);">Send us a Message</h2>
            
            @if(session('success'))
            <div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded mb-6">
                {{ session('success') }}
            </div>
            @endif
            
            <form action="{{ route('contact.store') }}" method="POST" class="space-y-6">
                @csrf
                
                <div>
                    <label for="name" class="block text-sm font-medium mb-2" style="color: var(--warm-900);">
                        Full Name *
                    </label>
                    <input type="text" 
                           id="name" 
                           name="name" 
                           value="{{ old('name') }}"
                           required
                           class="input-field @error('name') border-red-500 @enderror">
                    @error('name')
                    <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                    @enderror
                </div>
                
                <div>
                    <label for="email" class="block text-sm font-medium mb-2" style="color: var(--warm-900);">
                        Email Address *
                    </label>
                    <input type="email" 
                           id="email" 
                           name="email" 
                           value="{{ old('email') }}"
                           required
                           class="input-field @error('email') border-red-500 @enderror">
                    @error('email')
                    <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                    @enderror
                </div>
                
                <div>
                    <label for="subject" class="block text-sm font-medium mb-2" style="color: var(--warm-900);">
                        Subject *
                    </label>
                    <input type="text" 
                           id="subject" 
                           name="subject" 
                           value="{{ old('subject') }}"
                           required
                           class="input-field @error('subject') border-red-500 @enderror"
                           placeholder="What's this about?">
                    @error('subject')
                    <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                    @enderror
                </div>
                
                <div>
                    <label for="message" class="block text-sm font-medium mb-2" style="color: var(--warm-900);">
                        Message *
                    </label>
                    <textarea id="message" 
                             name="message" 
                             rows="6"
                             required
                             class="input-field @error('message') border-red-500 @enderror"
                             placeholder="Tell us how we can help you...">{{ old('message') }}</textarea>
                    @error('message')
                    <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                    @enderror
                </div>
                
                <button type="submit" class="w-full btn-primary py-3">
                    Send Message
                </button>
            </form>
        </div>

        <!-- Contact Information -->
        <div class="space-y-8">
            <!-- Contact Details -->
            @if($storeAddress || $storePhone || $storeEmail)
            <div class="card p-8">
                <h2 class="font-display text-2xl font-semibold mb-6" style="color: var(--warm-900);">
                    Contact Information
                </h2>
                
                <div class="space-y-6">
                    @if($storeAddress)
                    <div class="flex items-start">
                        <div class="flex-shrink-0 w-10 h-10 rounded-lg flex items-center justify-center mr-4" style="background: var(--warm-200);">
                            <svg class="w-5 h-5" style="color: var(--warm-600);" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"></path>
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"></path>
                            </svg>
                        </div>
                        <div>
                            <h3 class="font-semibold mb-1" style="color: var(--warm-900);">Address</h3>
                            <p style="color: var(--warm-700);">{{ $storeAddress }}</p>
                        </div>
                    </div>
                    @endif
                    
                    @if($storePhone)
                    <div class="flex items-start">
                        <div class="flex-shrink-0 w-10 h-10 rounded-lg flex items-center justify-center mr-4" style="background: var(--warm-200);">
                            <svg class="w-5 h-5" style="color: var(--warm-600);" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 5a2 2 0 012-2h3.28a1 1 0 01.948.684l1.498 4.493a1 1 0 01-.502 1.21l-2.257 1.13a11.042 11.042 0 005.516 5.516l1.13-2.257a1 1 0 011.21-.502l4.493 1.498a1 1 0 01.684.949V19a2 2 0 01-2 2h-1C9.716 21 3 14.284 3 6V5z"></path>
                            </svg>
                        </div>
                        <div>
                            <h3 class="font-semibold mb-1" style="color: var(--warm-900);">Phone</h3>
                            <p style="color: var(--warm-700);">
                                <a href="tel:{{ $storePhone }}" class="hover:underline">{{ $storePhone }}</a>
                            </p>
                        </div>
                    </div>
                    @endif
                    
                    @if($storeEmail)
                    <div class="flex items-start">
                        <div class="flex-shrink-0 w-10 h-10 rounded-lg flex items-center justify-center mr-4" style="background: var(--warm-200);">
                            <svg class="w-5 h-5" style="color: var(--warm-600);" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"></path>
                            </svg>
                        </div>
                        <div>
                            <h3 class="font-semibold mb-1" style="color: var(--warm-900);">Email</h3>
                            <p style="color: var(--warm-700);">
                                <a href="mailto:{{ $storeEmail }}" class="hover:underline">{{ $storeEmail }}</a>
                            </p>
                        </div>
                    </div>
                    @endif
                </div>
            </div>
            @endif

            <!-- Business Hours -->
            <div class="card p-8">
                <h2 class="font-display text-2xl font-semibold mb-6" style="color: var(--warm-900);">
                    Business Hours
                </h2>
                
                @if(!empty($operatingHours))
                <div class="space-y-2" style="color: var(--warm-700);">
                    @foreach($operatingHours as $day => $hours)
                    <div class="flex justify-between">
                        <span class="font-medium">{{ ucfirst($day) }}</span>
                        <span>
                            @if(isset($hours['open'], $hours['close']))
                                {{ \Carbon\Carbon::createFromFormat('H:i', $hours['open'])->format('g:i A') }} - {{ \Carbon\Carbon::createFromFormat('H:i', $hours['close'])->format('g:i A') }}
                            @else
                                Closed
                            @endif
                        </span>
                    </div>
                    @endforeach
                </div>
                @else
                <p style="color: var(--warm-700);">Contact us for hours</p>
                @endif
                
                <div class="mt-6 p-4 rounded-lg" style="background: var(--warm-100);">
                    <p class="text-sm" style="color: var(--warm-700);">
                        <strong>Note:</strong> Orders require {{ $leadTimeHours }} hours advance notice. 
                        Same-day orders may be available by calling us directly.
                    </p>
                </div>
            </div>

            @if($allergyDisclaimer)
            <!-- Allergy Disclaimer -->
            <div class="card p-8">
                <h2 class="font-display text-2xl font-semibold mb-4" style="color: var(--warm-900);">
                    Allergy Information
                </h2>
                <p class="text-sm leading-relaxed" style="color: var(--warm-700);">
                    {{ $allergyDisclaimer }}
                </p>
            </div>
            @endif

            <!-- FAQ -->
            @if(!empty($faqItems))
            <div class="card p-8">
                <h2 class="font-display text-2xl font-semibold mb-6" style="color: var(--warm-900);">
                    Quick Answers
                </h2>
                
                <div class="space-y-4">
                    @foreach($faqItems as $faq)
                    <div>
                        <h3 class="font-semibold mb-2" style="color: var(--warm-900);">{{ $faq['question'] }}</h3>
                        <p class="text-sm" style="color: var(--warm-700);">
                            {{ $faq['answer'] }}
                        </p>
                    </div>
                    @endforeach
                </div>
            </div>
            @endif
        </div>
    </div>

    <!-- Call to Action -->
    <div class="text-center mt-16">
        <div class="rounded-2xl p-12" style="background: var(--warm-200);">
            <h2 class="font-display text-3xl font-semibold mb-4" style="color: var(--warm-900);">
                Ready to Place an Order?
            </h2>
            <p class="text-lg mb-8 max-w-2xl mx-auto" style="color: var(--warm-700);">
                Browse our menu and place your order online. 
                Experience the difference that artisan quality makes.
            </p>
            <a href="{{ route('order.create') }}" class="btn-primary text-lg px-8 py-4 inline-block">
                View Menu & Order
            </a>
        </div>
    </div>
</div>
@endsection