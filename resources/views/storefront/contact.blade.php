<x-layouts.storefront>
<link rel="stylesheet" href="{{ asset('css/contact.css') }}">


{{-- Photo-Forward Hero --}}
<x-storefront.hero-section :image="$settings->heroImageUrl()" :image-alt="$settings->storeName . ' contact'" image-class="contact-hero-img">
    <div class="relative z-10 flex flex-col items-center justify-end text-center px-4 pb-20 min-h-[55vh]">
        <x-storefront.eyebrow class="contact-fade-1 mb-6">{{ $content['hero_eyebrow'] ?? 'Get in Touch' }}</x-storefront.eyebrow>
        <h1 class="contact-fade-1 font-display text-5xl md:text-7xl lg:text-8xl font-bold leading-none mb-4 text-warm-100">
            {!! nl2br(e($content['hero_title'] ?? "We'd Love to\nHear From You")) !!}
        </h1>
        <p class="contact-fade-2 font-script text-2xl md:text-3xl text-warm-400">
            Let's start a conversation
        </p>
        <p class="contact-fade-3 text-lg md:text-xl max-w-xl mx-auto mt-4 text-warm-400">
            {{ $content['hero_subtitle'] ?? 'Questions, special requests, or just want to say hello — we\'re all ears.' }}
        </p>
    </div>
</x-storefront.hero-section>

{{-- Contact Info Cards --}}
@if ($settings->storeAddress || $settings->storePhone || $settings->storeEmail)
<section class="bg-warm-800">
    <div class="max-w-5xl mx-auto px-4 py-10">
        <div class="grid sm:grid-cols-3 gap-4">
            @if ($settings->storeAddress)
            <div class="info-card p-6 rounded-2xl text-center" style="background: rgba(139,104,68,0.1); border: 1px solid rgba(139,104,68,0.15);">
                <div class="w-10 h-10 rounded-full mx-auto mb-3 flex items-center justify-center bg-warm-500/15">
                    <svg class="w-5 h-5 text-warm-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"/></svg>
                </div>
                <p class="text-xs uppercase tracking-[0.2em] mb-2 text-warm-500">Address</p>
                <p class="text-sm text-warm-300">{{ $settings->storeAddress }}</p>
            </div>
            @endif
            @if ($settings->storePhone)
            <div class="info-card p-6 rounded-2xl text-center" style="background: rgba(139,104,68,0.1); border: 1px solid rgba(139,104,68,0.15);">
                <div class="w-10 h-10 rounded-full mx-auto mb-3 flex items-center justify-center bg-warm-500/15">
                    <svg class="w-5 h-5 text-warm-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M3 5a2 2 0 012-2h3.28a1 1 0 01.948.684l1.498 4.493a1 1 0 01-.502 1.21l-2.257 1.13a11.042 11.042 0 005.516 5.516l1.13-2.257a1 1 0 011.21-.502l4.493 1.498a1 1 0 01.684.949V19a2 2 0 01-2 2h-1C9.716 21 3 14.284 3 6V5z"/></svg>
                </div>
                <p class="text-xs uppercase tracking-[0.2em] mb-2 text-warm-500">Phone</p>
                <p class="text-sm"><a href="tel:{{ $settings->storePhone }}" class="text-warm-300">{{ $settings->storePhone }}</a></p>
            </div>
            @endif
            @if ($settings->storeEmail)
            <div class="info-card p-6 rounded-2xl text-center" style="background: rgba(139,104,68,0.1); border: 1px solid rgba(139,104,68,0.15);">
                <div class="w-10 h-10 rounded-full mx-auto mb-3 flex items-center justify-center bg-warm-500/15">
                    <svg class="w-5 h-5 text-warm-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"/></svg>
                </div>
                <p class="text-xs uppercase tracking-[0.2em] mb-2 text-warm-500">Email</p>
                <p class="text-sm"><a href="mailto:{{ $settings->storeEmail }}" class="text-warm-300">{{ $settings->storeEmail }}</a></p>
            </div>
            @endif
        </div>
    </div>
</section>
@endif

{{-- Form + Sidebar --}}
<section class="bg-warm-100">
    <div class="max-w-6xl mx-auto px-4 py-16 md:py-24">
        <div class="grid md:grid-cols-5 gap-12 md:gap-16">
            {{-- Form --}}
            <div class="md:col-span-3">
                <x-storefront.eyebrow align="left" line-opacity="0.5" class="mb-8">{{ $content['form_eyebrow'] ?? 'Send a Message' }}</x-storefront.eyebrow>

                @session('success')
                <x-storefront.alert variant="light">
                    <p class="font-medium">{{ $value }}</p>
                </x-storefront.alert>
                @endsession

                <div class="p-8 md:p-10 rounded-2xl" style="background: white; box-shadow: 0 8px 40px rgba(28,20,16,0.08); border: 1px solid var(--warm-200);">
                    <form action="{{ route('contact.store') }}" method="POST" class="space-y-6">
                        @csrf
                        <div class="grid sm:grid-cols-2 gap-6">
                            <div>
                                <label for="name" class="block text-sm font-medium mb-2 text-warm-800">Full Name</label>
                                <input type="text" id="name" name="name" value="{{ old('name') }}" required class="contact-input @error('name') border-red-500 @enderror" placeholder="Jane Smith">
                                @error('name')<p class="text-red-500 text-sm mt-1">{{ $message }}</p>@enderror
                            </div>
                            <div>
                                <label for="email" class="block text-sm font-medium mb-2 text-warm-800">Email</label>
                                <input type="email" id="email" name="email" value="{{ old('email') }}" required class="contact-input @error('email') border-red-500 @enderror" placeholder="jane@example.com">
                                @error('email')<p class="text-red-500 text-sm mt-1">{{ $message }}</p>@enderror
                            </div>
                        </div>

                        <div>
                            <label for="subject" class="block text-sm font-medium mb-2 text-warm-800">Subject</label>
                            <input type="text" id="subject" name="subject" value="{{ old('subject') }}" required class="contact-input @error('subject') border-red-500 @enderror" placeholder="What's this about?">
                            @error('subject')<p class="text-red-500 text-sm mt-1">{{ $message }}</p>@enderror
                        </div>

                        <div>
                            <label for="message" class="block text-sm font-medium mb-2 text-warm-800">Message</label>
                            <textarea id="message" name="message" rows="6" required class="contact-input @error('message') border-red-500 @enderror" placeholder="Tell us how we can help...">{{ old('message') }}</textarea>
                            @error('message')<p class="text-red-500 text-sm mt-1">{{ $message }}</p>@enderror
                        </div>

                        <button type="submit" class="inline-block px-10 py-4 rounded-full font-semibold text-lg transition-all duration-300 hover:scale-105 hover:shadow-lg bg-warm-500 text-warm-900">
                            Send Message
                        </button>
                    </form>
                </div>
            </div>

            {{-- Sidebar --}}
            <div class="md:col-span-2 space-y-8">
                @if (!empty($settings->operatingHours))
                <div class="p-8 rounded-2xl bg-white border border-warm-200">
                    <x-storefront.eyebrow align="left" line-opacity="0.5" class="mb-6">{{ $content['hours_eyebrow'] ?? 'Hours' }}</x-storefront.eyebrow>
                    <div class="space-y-3">
                        @foreach ($settings->operatingHours as $day => $hours)
                        <div class="flex justify-between text-sm">
                            <span class="font-medium text-warm-800">{{ ucfirst($day) }}</span>
                            <span class="text-warm-600">
                                @if (isset($hours['open'], $hours['close']))
                                    @time($hours['open']) – @time($hours['close'])
                                @else
                                    Closed
                                @endif
                            </span>
                        </div>
                        @endforeach
                    </div>
                    <div class="mt-5 p-3 rounded-xl text-sm" style="background: var(--warm-100); color: var(--warm-600);">
                        📋 Orders need {{ $settings->leadTimeHours }}h advance notice.
                    </div>
                </div>
                @endif

                {{-- Map Placeholder --}}
                @if ($settings->storeAddress)
                <div class="rounded-2xl overflow-hidden" style="aspect-ratio: 16/10; background: var(--warm-200);">
                    <div class="w-full h-full flex items-center justify-center">
                        <div class="text-center px-6">
                            <svg class="w-8 h-8 mx-auto mb-2 text-warm-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"/></svg>
                            <p class="text-sm font-medium text-warm-500">{{ $settings->storeAddress }}</p>
                        </div>
                    </div>
                </div>
                @endif

                @if ($settings->allergyDisclaimer)
                <div class="p-6 rounded-2xl" style="background: var(--warm-200); border-left: 3px solid var(--warm-500);">
                    <p class="text-xs uppercase tracking-[0.2em] font-semibold mb-2 text-warm-500">Allergy Info</p>
                    <p class="text-sm leading-relaxed text-warm-600">{{ $settings->allergyDisclaimer }}</p>
                </div>
                @endif
            </div>
        </div>
    </div>
</section>

{{-- FAQ --}}
@if (!empty($settings->faqItems))
<x-storefront.dark-section :show-radial="false">
    <div class="max-w-5xl mx-auto px-4">
        <div class="text-center mb-12">
            <x-storefront.eyebrow line-opacity="0.5" class="mb-4">{{ $content['faq_eyebrow'] ?? 'FAQ' }}</x-storefront.eyebrow>
            <h2 class="font-display text-3xl md:text-5xl font-bold text-warm-100">{{ $content['faq_heading'] ?? 'Common Questions' }}</h2>
        </div>
        <div class="grid md:grid-cols-2 gap-x-12 gap-y-8">
            @foreach ($settings->faqItems as $faq)
            <div class="p-6 rounded-2xl bg-warm-800 border border-warm-700/15">
                <h3 class="font-display text-lg font-semibold mb-3 text-warm-200">{{ $faq['question'] }}</h3>
                <p class="leading-relaxed text-sm text-warm-400">{{ $faq['answer'] }}</p>
            </div>
            @endforeach
        </div>
    </div>
</x-storefront.dark-section>
@endif
</x-layouts.storefront>
