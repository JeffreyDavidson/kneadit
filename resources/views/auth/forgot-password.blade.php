<x-layouts.storefront>
    <link rel="stylesheet" href="{{ asset('css/contact.css') }}" />

    {{-- Photo-Forward Hero --}}
    <x-storefront.hero-section
        :image="$settings->heroImageUrl()"
        :image-alt="$settings->store->name . ' contact'"
        image-class="hero-img"
    >
        <div class="relative z-10 flex min-h-[55vh] flex-col items-center justify-end px-4 pb-20 text-center">
            <x-storefront.eyebrow class="hero-fade-1 mb-6">
                {{ $content['hero_eyebrow'] ?? 'Get in Touch' }}</x-storefront.eyebrow>
            <h1 class="hero-fade-1 font-display text-warm-100 mb-4 text-3xl leading-none font-bold sm:text-5xl md:text-7xl lg:text-8xl">
                {!! nl2br(e($content['hero_title'] ?? "We'd Love to\nHear From You")) !!}
            </h1>
            <p class="hero-fade-2 font-script text-warm-400 text-2xl md:text-3xl">Let's start a conversation</p>
            <p class="hero-fade-3 text-warm-100 mx-auto mt-4 max-w-xl text-lg md:text-xl">
                {{ $content['hero_subtitle'] ?? 'Questions, special requests, or just want to say hello — we\'re all ears.' }}
            </p>
        </div>
    </x-storefront.hero-section>

    {{-- Contact Info Cards --}}
    @if ($settings->store->address || $settings->store->phone || $settings->store->email)
        <section class="bg-warm-800">
            <div class="mx-auto max-w-5xl px-4 py-10">
                <div class="grid gap-4 sm:grid-cols-3">
                    @if ($settings->store->address)
                        <div class="info-card bg-warm-700/10 border-warm-700/15 rounded-2xl border p-6 text-center">
                            <div class="bg-warm-500/15 mx-auto mb-3 flex h-10 w-10 items-center justify-center rounded-full">
                                <x-heroicon-o-map-pin class="text-warm-500 h-5 w-5" />
                            </div>
                            <p class="text-warm-500 mb-2 text-xs tracking-[0.2em] uppercase">Address</p>
                            <p class="text-warm-300 text-sm">{{ $settings->store->address }}</p>
                        </div>
                    @endif
                    @if ($settings->store->phone)
                        <div class="info-card bg-warm-700/10 border-warm-700/15 rounded-2xl border p-6 text-center">
                            <div class="bg-warm-500/15 mx-auto mb-3 flex h-10 w-10 items-center justify-center rounded-full">
                                <x-heroicon-o-phone class="text-warm-500 h-5 w-5" />
                            </div>
                            <p class="text-warm-500 mb-2 text-xs tracking-[0.2em] uppercase">Phone</p>
                            <p class="text-sm">
                                <a
                                    href="tel:{{ $settings->store->phone }}"
                                    class="text-warm-300"
                                >{{ $settings->store->phone }}</a>
                            </p>
                        </div>
                    @endif
                    @if ($settings->store->email)
                        <div class="info-card bg-warm-700/10 border-warm-700/15 rounded-2xl border p-6 text-center">
                            <div class="bg-warm-500/15 mx-auto mb-3 flex h-10 w-10 items-center justify-center rounded-full">
                                <x-heroicon-o-envelope class="text-warm-500 h-5 w-5" />
                            </div>
                            <p class="text-warm-500 mb-2 text-xs tracking-[0.2em] uppercase">Email</p>
                            <p class="text-sm">
                                <a
                                    href="mailto:{{ $settings->store->email }}"
                                    class="text-warm-300"
                                >{{ $settings->store->email }}</a>
                            </p>
                        </div>
                    @endif
                </div>
            </div>
        </section>
    @endif

    {{-- Form + Sidebar --}}
    <section class="bg-warm-100">
        <div class="mx-auto max-w-6xl px-4 py-16 md:py-24">
            <div class="grid gap-12 md:grid-cols-5 md:gap-16">
                {{-- Form --}}
                <div class="md:col-span-3">
                    <x-storefront.eyebrow align="left" line-opacity="0.5" class="mb-8">
                        {{ $content['form_eyebrow'] ?? 'Send a Message' }}</x-storefront.eyebrow>

                    @session('success')
                        <x-storefront.alert variant="light" :dismiss-after="5000">
                            <p class="font-medium">{{ $value }}</p>
                        </x-storefront.alert>
                    @endsession

                    <div class="border-warm-200 rounded-2xl border bg-white p-8 shadow-2xl md:p-10">
                        <form
                            action="{{ route('contact.store') }}"
                            method="POST"
                            class="space-y-6"
                            x-data="{ submitting: false }"
                            @submit="submitting = true"
                            data-test="contact-form"
                        >
                            @csrf
                            <div class="grid gap-6 sm:grid-cols-2">
                                <div>
                                    <label for="name" class="text-warm-800 mb-2 block text-sm font-medium"
                                        >Full Name</label>
                                    <input
                                        type="text"
                                        id="name"
                                        name="name"
                                        value="{{ old('name') }}"
                                        required
                                        class="storefront-input @error('name') border-red-500 @enderror"
                                        placeholder="Jane Smith"
                                        data-test="contact-form-name"
                                        @error('name') aria-invalid="true" aria-describedby="name-error" @enderror
                                    />
                                    @error('name')
                                        <p id="name-error" class="mt-1 text-sm text-red-500" role="alert">
                                            {{ $message }}
                                        </p>
                                    @enderror
                                </div>
                                <div>
                                    <label for="email" class="text-warm-800 mb-2 block text-sm font-medium"
                                        >Email</label>
                                    <input
                                        type="email"
                                        id="email"
                                        name="email"
                                        value="{{ old('email') }}"
                                        required
                                        class="storefront-input @error('email') border-red-500 @enderror"
                                        placeholder="jane@example.com"
                                        data-test="contact-form-email"
                                        @error('email') aria-invalid="true" aria-describedby="email-error" @enderror
                                    />
                                    @error('email')
                                        <p id="email-error" class="mt-1 text-sm text-red-500" role="alert">
                                            {{ $message }}
                                        </p>
                                    @enderror
                                </div>
                            </div>

                            <div>
                                <label for="subject" class="text-warm-800 mb-2 block text-sm font-medium"
                                    >Subject</label>
                                <input
                                    type="text"
                                    id="subject"
                                    name="subject"
                                    value="{{ old('subject') }}"
                                    required
                                    class="storefront-input @error('subject') border-red-500 @enderror"
                                    placeholder="What's this about?"
                                    data-test="contact-form-subject"
                                    @error('subject') aria-invalid="true" aria-describedby="subject-error" @enderror
                                />
                                @error('subject')
                                    <p id="subject-error" class="mt-1 text-sm text-red-500" role="alert">
                                        {{ $message }}
                                    </p>
                                @enderror
                            </div>

                            <div>
                                <label for="message" class="text-warm-800 mb-2 block text-sm font-medium"
                                    >Message</label>
                                <textarea
                                    id="message"
                                    name="message"
                                    rows="6"
                                    required
                                    class="storefront-input @error('message') border-red-500 @enderror"
                                    placeholder="Tell us how we can help..."
                                    data-test="contact-form-message"
                                    @error('message') aria-invalid="true" aria-describedby="message-error" @enderror
                                >{{ old('message') }}</textarea>
                                @error('message')
                                    <p id="message-error" class="mt-1 text-sm text-red-500" role="alert">
                                        {{ $message }}
                                    </p>
                                @enderror
                            </div>

                            <x-storefront.buttons.async-submit
                                type="submit"
                                :idle-text="$content['send_button'] ?? 'Send Message'"
                                loading-text="Sending..."
                                class="px-10 font-semibold hover:scale-105 disabled:transform-none"
                                data-test="contact-form-submit"
                            />
                        </form>
                    </div>
                </div>

                {{-- Sidebar --}}
                <div class="space-y-8 md:col-span-2">
                    @if (! empty($settings->homepage->operatingHours))
                        <div class="border-warm-200 rounded-2xl border bg-white p-8">
                            <x-storefront.eyebrow align="left" line-opacity="0.5" class="mb-6">
                                {{ $content['hours_eyebrow'] ?? 'Hours' }}</x-storefront.eyebrow>
                            <div class="space-y-3">
                                @foreach ($settings->homepage->operatingHours as $day => $hours)
                                    <div class="flex justify-between text-sm">
                                        <span class="text-warm-800 font-medium">{{ ucfirst($day) }}</span>
                                        <span class="text-warm-600">
                                            @if (isset($hours['open'], $hours['close']))
                                                @time($hours['open'])
                                                –
                                                @time($hours['close'])
                                            @else
                                                Closed
                                            @endif
                                        </span>
                                    </div>
                                @endforeach
                            </div>
                            <div class="bg-warm-100 text-warm-600 mt-5 rounded-xl p-3 text-sm">
                                📋 Orders need {{ $settings->orders->leadTimeHours }}h advance notice.
                            </div>
                        </div>
                    @endif

                    {{-- Map Placeholder --}}
                    @if ($settings->store->address)
                        <div class="bg-warm-200 aspect-[16/10] overflow-hidden rounded-2xl">
                            <div class="flex h-full w-full items-center justify-center">
                                <div class="px-6 text-center">
                                    <x-heroicon-o-map-pin class="text-warm-400 mx-auto mb-2 h-8 w-8" />
                                    <p class="text-warm-500 text-sm font-medium">{{ $settings->store->address }}</p>
                                </div>
                            </div>
                        </div>
                    @endif

                    @if ($settings->branding->allergyDisclaimer)
                        <div class="bg-warm-200 border-warm-500 rounded-2xl border-l-[3px] p-6">
                            <p class="text-warm-500 mb-2 text-xs font-semibold tracking-[0.2em] uppercase">
                                Allergy Info
                            </p>
                            <p class="text-warm-600 text-sm leading-relaxed">
                                {{ $settings->branding->allergyDisclaimer }}
                            </p>
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </section>

    {{-- FAQ --}}
    @if (! empty($settings->homepage->faqItems))
        <x-storefront.dark-section :show-radial="false">
            <div class="mx-auto max-w-5xl px-4">
                <div class="mb-12 text-center">
                    <x-storefront.eyebrow line-opacity="0.5" class="mb-4">
                        {{ $content['faq_eyebrow'] ?? 'FAQ' }}</x-storefront.eyebrow>
                    <h2 class="font-display text-warm-100 text-3xl font-bold md:text-5xl">
                        {{ $content['faq_heading'] ?? 'Common Questions' }}
                    </h2>
                </div>
                <div class="grid gap-x-12 gap-y-4 md:grid-cols-2">
                    @foreach ($settings->homepage->faqItems as $faq)
                        <div
                            class="bg-warm-800 border-warm-700/15 overflow-hidden rounded-2xl border"
                            x-data="{ open: false }"
                        >
                            <button
                                @click="open = ! open"
                                class="flex w-full items-center justify-between p-6 text-left"
                                :aria-expanded="open"
                            >
                                <h3 class="font-display text-warm-200 pr-4 text-lg font-semibold">
                                    {{ $faq['question'] }}
                                </h3>
                                <x-heroicon-o-chevron-down
                                    class="text-warm-500 h-5 w-5 flex-shrink-0 transition-transform duration-200"
                                    ::class="open ? 'rotate-180' : ''"
                                    stroke-width="2"
                                />
                            </button>
                            <div x-show="open" x-collapse>
                                <p class="text-warm-400 px-6 pb-6 text-sm leading-relaxed">{{ $faq['answer'] }}</p>
                            </div>
                        </div>
                    @endforeach
                </div>
            </div>
        </x-storefront.dark-section>
    @endif
</x-layouts.storefront>
