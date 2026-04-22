<x-layouts.storefront>
<link rel="stylesheet" href="{{ asset('css/contact.css') }}">


{{-- Photo-Forward Hero --}}
<x-storefront.hero-section :image="$settings->heroImageUrl()" :image-alt="$settings->store->name . ' contact'" image-class="contact-hero-img">
    <div class="relative z-10 flex flex-col items-center justify-end text-center px-4 pb-20 min-h-[55vh]">
        <x-storefront.eyebrow class="contact-fade-1 mb-6">{{ $content['hero_eyebrow'] ?? 'Get in Touch' }}</x-storefront.eyebrow>
        <h1 class="contact-fade-1 font-display text-3xl sm:text-5xl md:text-7xl lg:text-8xl font-bold leading-none mb-4 text-warm-100">
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
@if ($settings->store->address || $settings->store->phone || $settings->store->email)
<section class="bg-warm-800">
    <div class="max-w-5xl mx-auto px-4 py-10">
        <div class="grid sm:grid-cols-3 gap-4">
            @if ($settings->store->address)
            <div class="info-card p-6 rounded-2xl text-center bg-warm-700/10 border border-warm-700/15">
                <div class="w-10 h-10 rounded-full mx-auto mb-3 flex items-center justify-center bg-warm-500/15">
                    <x-heroicon-o-map-pin class="w-5 h-5 text-warm-500" />
                </div>
                <p class="text-xs uppercase tracking-[0.2em] mb-2 text-warm-500">Address</p>
                <p class="text-sm text-warm-300">{{ $settings->store->address }}</p>
            </div>
            @endif
            @if ($settings->store->phone)
            <div class="info-card p-6 rounded-2xl text-center bg-warm-700/10 border border-warm-700/15">
                <div class="w-10 h-10 rounded-full mx-auto mb-3 flex items-center justify-center bg-warm-500/15">
                    <x-heroicon-o-phone class="w-5 h-5 text-warm-500" />
                </div>
                <p class="text-xs uppercase tracking-[0.2em] mb-2 text-warm-500">Phone</p>
                <p class="text-sm"><a href="tel:{{ $settings->store->phone }}" class="text-warm-300">{{ $settings->store->phone }}</a></p>
            </div>
            @endif
            @if ($settings->store->email)
            <div class="info-card p-6 rounded-2xl text-center bg-warm-700/10 border border-warm-700/15">
                <div class="w-10 h-10 rounded-full mx-auto mb-3 flex items-center justify-center bg-warm-500/15">
                    <x-heroicon-o-envelope class="w-5 h-5 text-warm-500" />
                </div>
                <p class="text-xs uppercase tracking-[0.2em] mb-2 text-warm-500">Email</p>
                <p class="text-sm"><a href="mailto:{{ $settings->store->email }}" class="text-warm-300">{{ $settings->store->email }}</a></p>
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
                <x-storefront.alert variant="light" :dismiss-after="5000">
                    <p class="font-medium">{{ $value }}</p>
                </x-storefront.alert>
                @endsession

                <div class="p-8 md:p-10 rounded-2xl bg-white shadow-2xl border border-warm-200">
                    <form action="{{ route('contact.store') }}" method="POST" class="space-y-6" x-data="{ submitting: false }" @submit="submitting = true" data-test="contact-form">
                        @csrf
                        <div class="grid sm:grid-cols-2 gap-6">
                            <div>
                                <label for="name" class="block text-sm font-medium mb-2 text-warm-800">Full Name</label>
                                <input type="text" id="name" name="name" value="{{ old('name') }}" required class="storefront-input @error('name') border-red-500 @enderror" placeholder="Jane Smith" data-test="contact-form-name" @error('name') aria-invalid="true" aria-describedby="name-error" @enderror>
                                @error('name')<p id="name-error" class="text-red-500 text-sm mt-1" role="alert">{{ $message }}</p>@enderror
                            </div>
                            <div>
                                <label for="email" class="block text-sm font-medium mb-2 text-warm-800">Email</label>
                                <input type="email" id="email" name="email" value="{{ old('email') }}" required class="storefront-input @error('email') border-red-500 @enderror" placeholder="jane@example.com" data-test="contact-form-email" @error('email') aria-invalid="true" aria-describedby="email-error" @enderror>
                                @error('email')<p id="email-error" class="text-red-500 text-sm mt-1" role="alert">{{ $message }}</p>@enderror
                            </div>
                        </div>

                        <div>
                            <label for="subject" class="block text-sm font-medium mb-2 text-warm-800">Subject</label>
                            <input type="text" id="subject" name="subject" value="{{ old('subject') }}" required class="storefront-input @error('subject') border-red-500 @enderror" placeholder="What's this about?" data-test="contact-form-subject" @error('subject') aria-invalid="true" aria-describedby="subject-error" @enderror>
                            @error('subject')<p id="subject-error" class="text-red-500 text-sm mt-1" role="alert">{{ $message }}</p>@enderror
                        </div>

                        <div>
                            <label for="message" class="block text-sm font-medium mb-2 text-warm-800">Message</label>
                            <textarea id="message" name="message" rows="6" required class="storefront-input @error('message') border-red-500 @enderror" placeholder="Tell us how we can help..." data-test="contact-form-message" @error('message') aria-invalid="true" aria-describedby="message-error" @enderror>{{ old('message') }}</textarea>
                            @error('message')<p id="message-error" class="text-red-500 text-sm mt-1" role="alert">{{ $message }}</p>@enderror
                        </div>

                        <x-storefront.buttons.async-submit
                            type="submit"
                            :idle-text="$content['send_button'] ?? 'Send Message'"
                            loading-text="Sending..."
                            class="px-10 font-semibold hover:scale-105 disabled:transform-none"
                            data-test="contact-form-submit" />
                    </form>
                </div>
            </div>

            {{-- Sidebar --}}
            <div class="md:col-span-2 space-y-8">
                @if (!empty($settings->homepage->operatingHours))
                <div class="p-8 rounded-2xl bg-white border border-warm-200">
                    <x-storefront.eyebrow align="left" line-opacity="0.5" class="mb-6">{{ $content['hours_eyebrow'] ?? 'Hours' }}</x-storefront.eyebrow>
                    <div class="space-y-3">
                        @foreach ($settings->homepage->operatingHours as $day => $hours)
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
                    <div class="mt-5 p-3 rounded-xl text-sm bg-warm-100 text-warm-600">
                        📋 Orders need {{ $settings->orders->leadTimeHours }}h advance notice.
                    </div>
                </div>
                @endif

                {{-- Map Placeholder --}}
                @if ($settings->store->address)
                <div class="rounded-2xl overflow-hidden aspect-[16/10] bg-warm-200">
                    <div class="w-full h-full flex items-center justify-center">
                        <div class="text-center px-6">
                            <x-heroicon-o-map-pin class="w-8 h-8 mx-auto mb-2 text-warm-400" />
                            <p class="text-sm font-medium text-warm-500">{{ $settings->store->address }}</p>
                        </div>
                    </div>
                </div>
                @endif

                @if ($settings->branding->allergyDisclaimer)
                <div class="p-6 rounded-2xl bg-warm-200 border-l-[3px] border-warm-500">
                    <p class="text-xs uppercase tracking-[0.2em] font-semibold mb-2 text-warm-500">Allergy Info</p>
                    <p class="text-sm leading-relaxed text-warm-600">{{ $settings->branding->allergyDisclaimer }}</p>
                </div>
                @endif
            </div>
        </div>
    </div>
</section>

{{-- FAQ --}}
@if (!empty($settings->homepage->faqItems))
<x-storefront.dark-section :show-radial="false">
    <div class="max-w-5xl mx-auto px-4">
        <div class="text-center mb-12">
            <x-storefront.eyebrow line-opacity="0.5" class="mb-4">{{ $content['faq_eyebrow'] ?? 'FAQ' }}</x-storefront.eyebrow>
            <h2 class="font-display text-3xl md:text-5xl font-bold text-warm-100">{{ $content['faq_heading'] ?? 'Common Questions' }}</h2>
        </div>
        <div class="grid md:grid-cols-2 gap-x-12 gap-y-4">
            @foreach ($settings->homepage->faqItems as $faq)
            <div class="rounded-2xl bg-warm-800 border border-warm-700/15 overflow-hidden" x-data="{ open: false }">
                <button @click="open = !open" class="flex items-center justify-between w-full p-6 text-left" :aria-expanded="open">
                    <h3 class="font-display text-lg font-semibold text-warm-200 pr-4">{{ $faq['question'] }}</h3>
                    <x-heroicon-o-chevron-down class="w-5 h-5 flex-shrink-0 text-warm-500 transition-transform duration-200" ::class="open ? 'rotate-180' : ''" stroke-width="2" />
                </button>
                <div x-show="open" x-collapse>
                    <p class="leading-relaxed text-sm text-warm-400 px-6 pb-6">{{ $faq['answer'] }}</p>
                </div>
            </div>
            @endforeach
        </div>
    </div>
</x-storefront.dark-section>
@endif
</x-layouts.storefront>
