<x-layouts.storefront>
    <link rel="stylesheet" href="{{ asset('css/gallery.css') }}" />

    {{-- Photo-Forward Hero --}}
    <x-storefront.hero-section
        :image="$settings->heroImageUrl()"
        :image-alt="$settings->store->name . ' gallery'"
        image-class="hero-img"
    >
        <div class="relative z-10 flex min-h-[55vh] flex-col items-center justify-end px-4 pb-20 text-center">
            <x-storefront.eyebrow class="hero-fade-1 mb-6">
                {{ $content['hero_eyebrow'] ?? 'From Our Customers' }}</x-storefront.eyebrow>
            <h1 class="hero-fade-1 font-display text-warm-100 mb-4 text-3xl leading-none font-bold sm:text-5xl md:text-7xl lg:text-8xl">
                {{ $content['hero_title'] ?? 'Customer Gallery' }}
            </h1>
            <p class="hero-fade-2 font-script text-warm-400 text-2xl md:text-3xl">Moments worth sharing</p>
            <p class="hero-fade-3 text-warm-100 mx-auto mt-4 max-w-xl text-lg md:text-xl">
                {{ $content['hero_subtitle'] ?? 'See what our customers are creating and enjoying!' }}
            </p>
        </div>
    </x-storefront.hero-section>

    {{-- Masonry Gallery with Lightbox --}}
    <section class="bg-warm-100" x-data="galleryLightbox">
        <div class="mx-auto max-w-7xl px-4 py-16 md:py-24">
            @if ($photos->count() > 0)
                <div class="mb-12 columns-1 gap-6 sm:columns-2 lg:columns-3">
                    @foreach ($photos as $photo)
                        <div class="mb-6 break-inside-avoid">
                            <div
                                class="gallery-item bg-warm-200"
                                @click="show(@js(asset('storage/customer-photos/' . basename($photo->photo_path))), @js($photo->caption ?? ''), @js($photo->customer_name))"
                            >
                                @if ($photo->is_featured)
                                    <x-storefront.pill
                                        tone="solid"
                                        size="sm"
                                        class="absolute top-3 left-3 z-10 gap-1 !font-bold tracking-wider uppercase"
                                    >
                                        <x-heroicon-s-star class="h-3.5 w-3.5" />
                                        Featured
                                    </x-storefront.pill>
                                @endif
                                <img
                                    src="{{ asset('storage/customer-photos/' . basename($photo->photo_path)) }}"
                                    alt="Photo by {{ $photo->customer_name }}"
                                    class="w-full object-cover"
                                    loading="lazy"
                                />
                                {{-- Hover Caption Overlay --}}
                                <div class="gallery-caption from-warm-900/85 via-warm-900/40 absolute inset-0 flex flex-col justify-end bg-gradient-to-t to-transparent p-5">
                                    @if ($photo->caption)
                                        <p class="text-warm-200 mb-2 text-sm italic">"{{ $photo->caption }}"</p>
                                    @endif
                                    <div class="flex items-center gap-2">
                                        <x-storefront.avatar-initial :name="$photo->customer_name" size="sm" dark />
                                        <span class="text-warm-300 text-sm font-semibold">{{ Str::of($photo->customer_name)->explode(' ')->first() }}</span>
                                    </div>
                                    @if ($photo->product)
                                        <p class="text-warm-500 mt-1 text-xs">{{ $photo->product->name }}</p>
                                    @endif
                                </div>
                            </div>
                        </div>
                    @endforeach
                </div>

                <div class="mb-12 flex justify-center">{{ $photos->links() }}</div>
            @else
                <div class="mx-auto max-w-2xl py-16 text-center">
                    {{-- Decorative icon --}}
                    <x-storefront.icon-circle size="lg" variant="plain" class="mx-auto mb-8">
                        <x-heroicon-o-camera class="text-warm-500 h-10 w-10" />
                    </x-storefront.icon-circle>

                    <h2 class="font-display text-warm-900 mb-4 text-3xl font-bold">
                        {{ $content['empty_heading'] ?? 'Your Photos Will Shine Here' }}
                    </h2>
                    <p class="text-warm-600 mb-6 text-lg leading-relaxed">
                        {{ $content['empty_description'] ?? 'We\'d love to see what you\'re baking and enjoying! Share a photo of your order and it\'ll appear right here for the whole community to see.' }}
                    </p>
                    <p class="font-script text-warm-500 mb-10 text-xl">
                        {{ $content['empty_script'] ?? 'Be the first to share!' }}
                    </p>

                    {{-- Faux gallery preview --}}
                    <div class="grid grid-cols-3 gap-3 opacity-20">
                        @for ($i = 0; $i < 6; $i++)
                            <div
                                @class([
                                    'rounded-xl overflow-hidden bg-gradient-to-br from-warm-200 to-warm-300',
                                    'aspect-square' => $i % 2 === 0,
                                    'aspect-[4/5]' => $i === 1 || $i === 5,
                                    'aspect-[5/4]' => $i === 3,
                                ])
                            ></div>
                        @endfor
                    </div>

                    <x-storefront.button href="#share-photo" variant="dark" size="lg" class="mt-10">
                        Share Your Photo ↓
                    </x-storefront.button>
                </div>
            @endif
        </div>

        {{-- Lightbox Modal --}}
        <div
            x-show="open"
            x-transition:enter="transition ease-out duration-300"
            x-transition:enter-start="opacity-0"
            x-transition:enter-end="opacity-100"
            x-transition:leave="transition ease-in duration-200"
            x-transition:leave-start="opacity-100"
            x-transition:leave-end="opacity-0"
            class="bg-warm-900/90 fixed inset-0 z-50 flex items-center justify-center p-4 backdrop-blur"
            @click.self="close()"
            @keydown.escape.window="close()"
            role="dialog"
            aria-label="Photo lightbox"
        >
            <button
                @click="close()"
                class="text-warm-300 absolute top-6 right-6 flex h-11 w-11 items-center justify-center rounded-full bg-white/10 text-xl transition-all duration-200 hover:scale-110"
                aria-label="Close lightbox"
            >
                &times;
            </button>
            <div class="w-full max-w-4xl" @click.stop>
                <img
                    x-bind:src="src"
                    x-bind:alt="caption || 'Customer photo'"
                    class="max-h-[75vh] w-full rounded-xl object-contain"
                />
                <div class="mt-4 text-center" x-show="caption || author">
                    <p x-show="caption" class="text-warm-300 text-lg italic" x-text="'&quot;' + caption + '&quot;'"></p>
                    <p x-show="author" class="text-warm-500 mt-2 text-sm font-semibold" x-text="'— ' + author"></p>
                </div>
            </div>
        </div>
    </section>

    {{-- Upload CTA + Form --}}
    <x-storefront.dark-section id="share-photo" padding="py-24" class="scroll-mt-20">
        <div class="mx-auto max-w-2xl px-4">
            <div class="mb-12 text-center">
                <x-storefront.eyebrow line-opacity="0.5" class="mb-6">
                    {{ $content['upload_eyebrow'] ?? 'Share Yours' }}</x-storefront.eyebrow>
                <h2 class="font-display text-warm-100 mb-4 text-3xl font-bold md:text-5xl">
                    {{ $content['upload_heading'] ?? 'Share Your Photo' }}
                </h2>
                <p class="text-warm-400 text-lg">
                    {{ $content['upload_description'] ?? 'Show off your order! Photos will appear after approval.' }}
                </p>
            </div>

            @session('success')
                <x-storefront.alert :dismiss-after="5000">{{ $value }}</x-storefront.alert>
            @endsession

            @if ($errors->any())
                <x-storefront.alert type="error">
                    <ul class="list-inside list-disc text-sm">
                        @foreach ($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </x-storefront.alert>
            @endif

            <div class="bg-warm-800 border-warm-700/20 rounded-2xl border p-8 md:p-10">
                <form
                    action="{{ route('gallery.submit') }}"
                    method="POST"
                    enctype="multipart/form-data"
                    class="space-y-6"
                    x-data="{ submitting: false }"
                    @submit="submitting = true"
                    data-test="gallery-upload-form"
                >
                    @csrf
                    <div class="grid gap-6 md:grid-cols-2">
                        <div>
                            <label class="text-warm-300 mb-2 block text-sm font-medium">Your Name *</label>
                            <input
                                type="text"
                                name="customer_name"
                                value="{{ old('customer_name') }}"
                                required
                                class="storefront-input-dark"
                                data-test="gallery-upload-form-customer-name"
                            />
                        </div>
                        <div>
                            <label class="text-warm-300 mb-2 block text-sm font-medium">Your Email *</label>
                            <input
                                type="email"
                                name="customer_email"
                                value="{{ old('customer_email') }}"
                                required
                                class="storefront-input-dark"
                                data-test="gallery-upload-form-customer-email"
                            />
                        </div>
                    </div>

                    <div x-data="{ fileName: '' }">
                        <label class="text-warm-300 mb-2 block text-sm font-medium">Photo * <span class="text-warm-500 font-normal">(JPG, PNG, or WebP — max 5MB)</span></label>
                        <label class="border-warm-500/25 bg-warm-500/5 hover:border-warm-500/50 hover:bg-warm-500/10 block cursor-pointer rounded-xl border-2 border-dashed p-8 text-center transition-all duration-300">
                            <input
                                type="file"
                                name="photo"
                                accept="image/jpeg,image/png,image/webp"
                                required
                                class="hidden"
                                @change="fileName = $event.target.files[0]?.name || ''"
                                data-test="gallery-upload-form-photo"
                            />
                            <div x-show="! fileName">
                                <x-heroicon-o-arrow-up-tray class="text-warm-500/60 mx-auto mb-3 h-10 w-10" />
                                <p class="text-warm-300 mb-1 font-medium">Click to upload or drag & drop</p>
                                <p class="text-warm-500 text-sm">JPG, PNG, or WebP up to 5MB</p>
                            </div>
                            <div x-show="fileName" class="flex items-center justify-center gap-2">
                                <x-heroicon-o-check class="h-5 w-5 text-green-400" stroke-width="2" />
                                <span class="text-warm-300 font-medium" x-text="fileName"></span>
                            </div>
                        </label>
                    </div>

                    <div>
                        <label class="text-warm-300 mb-2 block text-sm font-medium">Caption</label>
                        <textarea
                            name="caption"
                            rows="3"
                            class="storefront-input-dark"
                            data-test="gallery-upload-form-caption"
                        >{{ old('caption') }}</textarea>
                    </div>

                    <div>
                        <label class="text-warm-300 mb-2 block text-sm font-medium">Which product? (optional)</label>
                        <select
                            name="product_id"
                            class="storefront-input-dark"
                            data-test="gallery-upload-form-product-id"
                        >
                            <option value="">— Select a product —</option>
                            @foreach ($products as $product)
                                <option value="{{ $product->id }}" @selected(old('product_id') == $product->id)>
                                    {{ $product->name }}
                                </option>
                            @endforeach
                        </select>
                    </div>

                    <x-storefront.buttons.async-submit
                        type="submit"
                        idle-text="Submit Photo"
                        loading-text="Uploading..."
                        class="w-full justify-center font-semibold hover:scale-[1.02]"
                        data-test="gallery-upload-form-submit"
                    />
                </form>
            </div>
        </div>
    </x-storefront.dark-section>
</x-layouts.storefront>
