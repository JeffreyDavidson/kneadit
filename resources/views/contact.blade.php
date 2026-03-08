@extends('layouts.storefront')

@section('content')
<style>
    .contact-input {
        width: 100%;
        padding: 0.875rem 1rem;
        border-radius: 0.75rem;
        border: 1.5px solid var(--warm-300);
        background: var(--warm-50);
        font-family: var(--font-body);
        font-size: 1rem;
        color: var(--warm-900);
        transition: border-color 0.2s, box-shadow 0.2s;
        outline: none;
    }
    .contact-input:focus {
        border-color: var(--warm-500);
        box-shadow: 0 0 0 3px color-mix(in srgb, var(--warm-500) 15%, transparent);
    }
    .contact-input::placeholder {
        color: var(--warm-400);
    }
</style>

@php
    $storeAddress = \App\Models\Setting::get('store_address');
    $storePhone = \App\Models\Setting::get('store_phone');
    $storeEmail = \App\Models\Setting::get('store_email');
    $operatingHours = json_decode(\App\Models\Setting::get('operating_hours', '{}'), true);
    $faqItems = json_decode(\App\Models\Setting::get('faq_items', '[]'), true);
    $leadTimeHours = \App\Models\Setting::get('order_lead_time_hours', '24');
    $allergyDisclaimer = \App\Models\Setting::get('allergy_disclaimer');
@endphp

{{-- Hero --}}
<div class="py-20 md:py-28 text-center" style="background: var(--warm-100);">
    <div class="max-w-4xl mx-auto px-4">
        <h1 class="font-display text-5xl md:text-7xl font-bold tracking-tight mb-6" style="color: var(--warm-900);">
            We'd Love to<br>Hear From You
        </h1>
        <p class="text-lg md:text-xl max-w-xl mx-auto" style="color: var(--warm-600);">
            Questions, special requests, or just want to say hello — we're all ears.
        </p>
    </div>
</div>

<div class="max-w-6xl mx-auto px-4 py-16 md:py-24">
    <div class="grid md:grid-cols-5 gap-12 md:gap-16">
        {{-- Form --}}
        <div class="md:col-span-3">
            @if(session('success'))
            <div class="mb-8 p-5 rounded-xl" style="background: #f0fdf4; border: 1px solid #bbf7d0;">
                <p class="text-green-800 font-medium">{{ session('success') }}</p>
            </div>
            @endif

            <form action="{{ route('contact.store') }}" method="POST" class="space-y-6">
                @csrf

                <div class="grid sm:grid-cols-2 gap-6">
                    <div>
                        <label for="name" class="block text-sm font-medium mb-2" style="color: var(--warm-800);">Full Name</label>
                        <input type="text" id="name" name="name" value="{{ old('name') }}" required class="contact-input @error('name') border-red-500 @enderror" placeholder="Jane Smith">
                        @error('name')<p class="text-red-500 text-sm mt-1">{{ $message }}</p>@enderror
                    </div>
                    <div>
                        <label for="email" class="block text-sm font-medium mb-2" style="color: var(--warm-800);">Email</label>
                        <input type="email" id="email" name="email" value="{{ old('email') }}" required class="contact-input @error('email') border-red-500 @enderror" placeholder="jane@example.com">
                        @error('email')<p class="text-red-500 text-sm mt-1">{{ $message }}</p>@enderror
                    </div>
                </div>

                <div>
                    <label for="subject" class="block text-sm font-medium mb-2" style="color: var(--warm-800);">Subject</label>
                    <input type="text" id="subject" name="subject" value="{{ old('subject') }}" required class="contact-input @error('subject') border-red-500 @enderror" placeholder="What's this about?">
                    @error('subject')<p class="text-red-500 text-sm mt-1">{{ $message }}</p>@enderror
                </div>

                <div>
                    <label for="message" class="block text-sm font-medium mb-2" style="color: var(--warm-800);">Message</label>
                    <textarea id="message" name="message" rows="6" required class="contact-input @error('message') border-red-500 @enderror" placeholder="Tell us how we can help...">{{ old('message') }}</textarea>
                    @error('message')<p class="text-red-500 text-sm mt-1">{{ $message }}</p>@enderror
                </div>

                <button type="submit" class="btn-primary py-3.5 px-8 text-lg">
                    Send Message
                </button>
            </form>
        </div>

        {{-- Info side --}}
        <div class="md:col-span-2 space-y-10">
            @if($storeAddress || $storePhone || $storeEmail)
            <div>
                <p class="text-sm tracking-widest uppercase font-medium mb-6" style="color: var(--warm-500);">Contact Details</p>
                <div class="space-y-5">
                    @if($storeAddress)
                    <div>
                        <p class="text-sm font-medium mb-1" style="color: var(--warm-500);">Address</p>
                        <p class="text-lg" style="color: var(--warm-800);">{{ $storeAddress }}</p>
                    </div>
                    @endif
                    @if($storePhone)
                    <div>
                        <p class="text-sm font-medium mb-1" style="color: var(--warm-500);">Phone</p>
                        <p class="text-lg"><a href="tel:{{ $storePhone }}" class="hover:underline" style="color: var(--warm-800);">{{ $storePhone }}</a></p>
                    </div>
                    @endif
                    @if($storeEmail)
                    <div>
                        <p class="text-sm font-medium mb-1" style="color: var(--warm-500);">Email</p>
                        <p class="text-lg"><a href="mailto:{{ $storeEmail }}" class="hover:underline" style="color: var(--warm-800);">{{ $storeEmail }}</a></p>
                    </div>
                    @endif
                </div>
            </div>
            @endif

            @if(!empty($operatingHours))
            <div>
                <p class="text-sm tracking-widest uppercase font-medium mb-6" style="color: var(--warm-500);">Hours</p>
                <div class="space-y-2">
                    @foreach($operatingHours as $day => $hours)
                    <div class="flex justify-between text-sm">
                        <span class="font-medium" style="color: var(--warm-800);">{{ ucfirst($day) }}</span>
                        <span style="color: var(--warm-600);">
                            @if(isset($hours['open'], $hours['close']))
                                {{ \Carbon\Carbon::createFromFormat('H:i', $hours['open'])->format('g:i A') }} – {{ \Carbon\Carbon::createFromFormat('H:i', $hours['close'])->format('g:i A') }}
                            @else
                                Closed
                            @endif
                        </span>
                    </div>
                    @endforeach
                </div>
                <p class="text-sm mt-4 p-3 rounded-lg" style="background: var(--warm-100); color: var(--warm-600);">
                    Orders need {{ $leadTimeHours }}h advance notice.
                </p>
            </div>
            @endif

            @if($allergyDisclaimer)
            <div>
                <p class="text-sm tracking-widest uppercase font-medium mb-3" style="color: var(--warm-500);">Allergy Info</p>
                <p class="text-sm leading-relaxed" style="color: var(--warm-600);">{{ $allergyDisclaimer }}</p>
            </div>
            @endif
        </div>
    </div>

    {{-- FAQ --}}
    @if(!empty($faqItems))
    <div class="mt-24">
        <div class="flex items-center gap-6 mb-12">
            <div class="flex-1 h-px" style="background: var(--warm-300);"></div>
            <h2 class="font-display text-2xl md:text-3xl font-bold whitespace-nowrap" style="color: var(--warm-900);">Common Questions</h2>
            <div class="flex-1 h-px" style="background: var(--warm-300);"></div>
        </div>
        <div class="grid md:grid-cols-2 gap-x-16 gap-y-10 max-w-5xl mx-auto">
            @foreach($faqItems as $faq)
            <div>
                <h3 class="font-display text-lg font-semibold mb-2" style="color: var(--warm-900);">{{ $faq['question'] }}</h3>
                <p class="leading-relaxed" style="color: var(--warm-600);">{{ $faq['answer'] }}</p>
            </div>
            @endforeach
        </div>
    </div>
    @endif
</div>
@endsection