<x-layouts.storefront>
<link rel="stylesheet" href="{{ asset('css/contact.css') }}">

@php
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
@endphp

{{-- Photo-Forward Hero --}}
<section class="relative overflow-hidden" style="min-height: 55vh;">
    <div class="absolute inset-0">
        <img src="{{ $heroImageUrl }}" alt="{{ $storeName }} contact" class="w-full h-full object-cover contact-hero-img">
    </div>
    <div class="absolute inset-0" style="background: linear-gradient(to bottom, rgba(28,20,16,0.4) 0%, rgba(28,20,16,0.65) 50%, rgba(28,20,16,0.95) 100%);"></div>
    <div class="absolute inset-0 opacity-[0.03]" style="background-image: url('data:image/svg+xml,%3Csvg viewBox=%220 0 256 256%22 xmlns=%22http://www.w3.org/2000/svg%22%3E%3Cfilter id=%22noise%22%3E%3CfeTurbulence type=%22fractalNoise%22 baseFrequency=%220.9%22 numOctaves=%224%22 stitchTiles=%22stitch%22/%3E%3C/filter%3E%3Crect width=%22100%25%22 height=%22100%25%22 filter=%22url(%23noise)%22/%3E%3C/svg%3E');"></div>

    <div class="relative z-10 flex flex-col items-center justify-end text-center px-4 pb-20" style="min-height: 55vh;">
        <div class="contact-fade-1 flex items-center gap-3 mb-6">
            <span class="block w-8 h-px" style="background: var(--warm-500);"></span>
            <span class="uppercase tracking-[0.25em] text-xs font-semibold" style="color: var(--warm-500);">{{ $content['hero_eyebrow'] ?? 'Get in Touch' }}</span>
            <span class="block w-8 h-px" style="background: var(--warm-500);"></span>
        </div>
        <h1 class="contact-fade-1 font-display text-5xl md:text-7xl lg:text-8xl font-bold leading-none mb-4" style="color: var(--warm-100);">
            {!! nl2br(e($content['hero_title'] ?? "We'd Love to\nHear From You")) !!}
        </h1>
        <p class="contact-fade-2 font-script text-2xl md:text-3xl" style="color: var(--warm-400);">
            Let's start a conversation
        </p>
        <p class="contact-fade-3 text-lg md:text-xl max-w-xl mx-auto mt-4" style="color: var(--warm-400);">
            {{ $content['hero_subtitle'] ?? 'Questions, special requests, or just want to say hello — we\'re all ears.' }}
        </p>
    </div>
</section>

{{-- Contact Info Cards --}}
@if($storeAddress || $storePhone || $storeEmail)
<section style="background: var(--warm-800);">
    <div class="max-w-5xl mx-auto px-4 py-10">
        <div class="grid sm:grid-cols-3 gap-4">
            @if($storeAddress)
            <div class="info-card p-6 rounded-2xl text-center" style="background: rgba(139,104,68,0.1); border: 1px solid rgba(139,104,68,0.15);">
                <div class="w-10 h-10 rounded-full mx-auto mb-3 flex items-center justify-center" style="background: rgba(212,146,12,0.15);">
                    <svg class="w-5 h-5" style="color: var(--warm-500);" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"/></svg>
                </div>
                <p class="text-xs uppercase tracking-[0.2em] mb-2" style="color: var(--warm-500);">Address</p>
                <p class="text-sm" style="color: var(--warm-300);">{{ $storeAddress }}</p>
            </div>
            @endif
            @if($storePhone)
            <div class="info-card p-6 rounded-2xl text-center" style="background: rgba(139,104,68,0.1); border: 1px solid rgba(139,104,68,0.15);">
                <div class="w-10 h-10 rounded-full mx-auto mb-3 flex items-center justify-center" style="background: rgba(212,146,12,0.15);">
                    <svg class="w-5 h-5" style="color: var(--warm-500);" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M3 5a2 2 0 012-2h3.28a1 1 0 01.948.684l1.498 4.493a1 1 0 01-.502 1.21l-2.257 1.13a11.042 11.042 0 005.516 5.516l1.13-2.257a1 1 0 011.21-.502l4.493 1.498a1 1 0 01.684.949V19a2 2 0 01-2 2h-1C9.716 21 3 14.284 3 6V5z"/></svg>
                </div>
                <p class="text-xs uppercase tracking-[0.2em] mb-2" style="color: var(--warm-500);">Phone</p>
                <p class="text-sm"><a href="tel:{{ $storePhone }}" style="color: var(--warm-300);">{{ $storePhone }}</a></p>
            </div>
            @endif
            @if($storeEmail)
            <div class="info-card p-6 rounded-2xl text-center" style="background: rgba(139,104,68,0.1); border: 1px solid rgba(139,104,68,0.15);">
                <div class="w-10 h-10 rounded-full mx-auto mb-3 flex items-center justify-center" style="background: rgba(212,146,12,0.15);">
                    <svg class="w-5 h-5" style="color: var(--warm-500);" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"/></svg>
                </div>
                <p class="text-xs uppercase tracking-[0.2em] mb-2" style="color: var(--warm-500);">Email</p>
                <p class="text-sm"><a href="mailto:{{ $storeEmail }}" style="color: var(--warm-300);">{{ $storeEmail }}</a></p>
            </div>
            @endif
        </div>
    </div>
</section>
@endif

{{-- Form + Sidebar --}}
<section style="background: var(--warm-100);">
    <div class="max-w-6xl mx-auto px-4 py-16 md:py-24">
        <div class="grid md:grid-cols-5 gap-12 md:gap-16">
            {{-- Form --}}
            <div class="md:col-span-3">
                <div class="flex items-center gap-3 mb-8">
                    <span class="block w-8 h-px" style="background: var(--warm-500); opacity: 0.5;"></span>
                    <span class="uppercase tracking-[0.25em] text-xs font-semibold" style="color: var(--warm-500);">{{ $content['form_eyebrow'] ?? 'Send a Message' }}</span>
                </div>

                @if(session('success'))
                <div class="mb-8 p-5 rounded-2xl" style="background: #f0fdf4; border: 1px solid #bbf7d0;">
                    <p class="text-green-800 font-medium">{{ session('success') }}</p>
                </div>
                @endif

                <div class="p-8 md:p-10 rounded-2xl" style="background: white; box-shadow: 0 8px 40px rgba(28,20,16,0.08); border: 1px solid var(--warm-200);">
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

                        <button type="submit" class="inline-block px-10 py-4 rounded-full font-semibold text-lg transition-all duration-300 hover:scale-105 hover:shadow-lg" style="background: var(--warm-500); color: var(--warm-900);">
                            Send Message
                        </button>
                    </form>
                </div>
            </div>

            {{-- Sidebar --}}
            <div class="md:col-span-2 space-y-8">
                @if(!empty($operatingHours))
                <div class="p-8 rounded-2xl" style="background: white; border: 1px solid var(--warm-200);">
                    <div class="flex items-center gap-3 mb-6">
                        <span class="block w-6 h-px" style="background: var(--warm-500); opacity: 0.5;"></span>
                        <span class="uppercase tracking-[0.25em] text-xs font-semibold" style="color: var(--warm-500);">{{ $content['hours_eyebrow'] ?? 'Hours' }}</span>
                    </div>
                    <div class="space-y-3">
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
                    <div class="mt-5 p-3 rounded-xl text-sm" style="background: var(--warm-100); color: var(--warm-600);">
                        📋 Orders need {{ $leadTimeHours }}h advance notice.
                    </div>
                </div>
                @endif

                {{-- Map Placeholder --}}
                @if($storeAddress)
                <div class="rounded-2xl overflow-hidden" style="aspect-ratio: 16/10; background: var(--warm-200);">
                    <div class="w-full h-full flex items-center justify-center">
                        <div class="text-center px-6">
                            <svg class="w-8 h-8 mx-auto mb-2" style="color: var(--warm-400);" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"/></svg>
                            <p class="text-sm font-medium" style="color: var(--warm-500);">{{ $storeAddress }}</p>
                        </div>
                    </div>
                </div>
                @endif

                @if($allergyDisclaimer)
                <div class="p-6 rounded-2xl" style="background: var(--warm-200); border-left: 3px solid var(--warm-500);">
                    <p class="text-xs uppercase tracking-[0.2em] font-semibold mb-2" style="color: var(--warm-500);">Allergy Info</p>
                    <p class="text-sm leading-relaxed" style="color: var(--warm-600);">{{ $allergyDisclaimer }}</p>
                </div>
                @endif
            </div>
        </div>
    </div>
</section>

{{-- FAQ --}}
@if(!empty($faqItems))
<section class="relative py-20 overflow-hidden" style="background: var(--warm-900);">
    <div class="absolute inset-0 opacity-[0.03]" style="background-image: url('data:image/svg+xml,%3Csvg viewBox=%220 0 256 256%22 xmlns=%22http://www.w3.org/2000/svg%22%3E%3Cfilter id=%22noise%22%3E%3CfeTurbulence type=%22fractalNoise%22 baseFrequency=%220.9%22 numOctaves=%224%22 stitchTiles=%22stitch%22/%3E%3C/filter%3E%3Crect width=%22100%25%22 height=%22100%25%22 filter=%22url(%23noise)%22/%3E%3C/svg%3E');"></div>
    <div class="relative z-10 max-w-5xl mx-auto px-4">
        <div class="text-center mb-12">
            <div class="flex items-center justify-center gap-3 mb-4">
                <span class="block w-8 h-px" style="background: var(--warm-500); opacity: 0.5;"></span>
                <span class="uppercase tracking-[0.25em] text-xs font-semibold" style="color: var(--warm-500);">{{ $content['faq_eyebrow'] ?? 'FAQ' }}</span>
                <span class="block w-8 h-px" style="background: var(--warm-500); opacity: 0.5;"></span>
            </div>
            <h2 class="font-display text-3xl md:text-5xl font-bold" style="color: var(--warm-100);">{{ $content['faq_heading'] ?? 'Common Questions' }}</h2>
        </div>
        <div class="grid md:grid-cols-2 gap-x-12 gap-y-8">
            @foreach($faqItems as $faq)
            <div class="p-6 rounded-2xl" style="background: var(--warm-800); border: 1px solid rgba(139,104,68,0.15);">
                <h3 class="font-display text-lg font-semibold mb-3" style="color: var(--warm-200);">{{ $faq['question'] }}</h3>
                <p class="leading-relaxed text-sm" style="color: var(--warm-400);">{{ $faq['answer'] }}</p>
            </div>
            @endforeach
        </div>
    </div>
</section>
@endif
</x-layouts.storefront>
