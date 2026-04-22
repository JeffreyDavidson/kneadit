<x-layouts.storefront>
<link rel="stylesheet" href="{{ asset('css/gallery.css') }}">


{{-- Photo-Forward Hero --}}
<x-storefront.hero-section :image="$settings->heroImageUrl()" :image-alt="$settings->store->name . ' gallery'" image-class="gallery-hero-img">
    <div class="relative z-10 flex flex-col items-center justify-end text-center px-4 pb-20 min-h-[55vh]">
        <x-storefront.eyebrow class="gallery-fade-1 mb-6">{{ $content['hero_eyebrow'] ?? 'From Our Customers' }}</x-storefront.eyebrow>
        <h1 class="gallery-fade-1 font-display text-3xl sm:text-5xl md:text-7xl lg:text-8xl font-bold leading-none mb-4 text-warm-100">
            {{ $content['hero_title'] ?? 'Customer Gallery' }}
        </h1>
        <p class="gallery-fade-2 font-script text-2xl md:text-3xl text-warm-400">
            Moments worth sharing
        </p>
        <p class="gallery-fade-3 text-lg md:text-xl max-w-xl mx-auto mt-4 text-warm-400">
            {{ $content['hero_subtitle'] ?? 'See what our customers are creating and enjoying!' }}
        </p>
    </div>
</x-storefront.hero-section>

{{-- Masonry Gallery with Lightbox --}}
<section class="bg-warm-100" x-data="{ lightbox: false, lightboxSrc: '', lightboxCaption: '', lightboxAuthor: '' }">
    <div class="max-w-7xl mx-auto px-4 py-16 md:py-24">
        @if ($photos->count() > 0)
        <div class="columns-1 sm:columns-2 lg:columns-3 gap-6 mb-12">
            @foreach ($photos as $photo)
            <div class="break-inside-avoid mb-6">
                <div class="gallery-item"
                     x-data="{ src: @js(asset('storage/customer-photos/' . basename($photo->photo_path))), caption: @js($photo->caption ?? ''), author: @js($photo->customer_name) }"
                     @click="lightboxSrc = src; lightboxCaption = caption; lightboxAuthor = author; lightbox = true"
                     class="bg-warm-200">
                    @if ($photo->is_featured)
                    <div class="absolute top-3 left-3 z-10 px-3 py-1 rounded-full text-xs font-bold uppercase tracking-wider bg-warm-500 text-warm-900 inline-flex items-center gap-1">
                        <x-heroicon-s-star class="w-3.5 h-3.5" />
                        Featured
                    </div>
                    @endif
                    <img
                        src="{{ asset('storage/customer-photos/' . basename($photo->photo_path)) }}"
                        alt="Photo by {{ $photo->customer_name }}"
                        class="w-full object-cover"
                        loading="lazy"
                    >
                    {{-- Hover Caption Overlay --}}
                    <div class="gallery-caption absolute inset-0 flex flex-col justify-end p-5" style="background: linear-gradient(to top, rgba(28,20,16,0.85) 0%, transparent 60%);">
                        @if ($photo->caption)
                        <p class="italic text-sm mb-2 text-warm-200">"{{ $photo->caption }}"</p>
                        @endif
                        <div class="flex items-center gap-2">
                            <x-storefront.avatar-initial :name="$photo->customer_name" size="sm" dark />
                            <span class="text-sm font-semibold text-warm-300">{{ Str::of($photo->customer_name)->explode(' ')->first() }}</span>
                        </div>
                        @if ($photo->product)
                        <p class="text-xs mt-1 text-warm-500">{{ $photo->product->name }}</p>
                        @endif
                    </div>
                </div>
            </div>
            @endforeach
        </div>

        <div class="flex justify-center mb-12">
            {{ $photos->links() }}
        </div>
        @else
        <div class="max-w-2xl mx-auto text-center py-16">
            {{-- Decorative icon --}}
            <div class="w-20 h-20 rounded-full mx-auto mb-8 flex items-center justify-center bg-warm-200">
                <x-heroicon-o-camera class="w-10 h-10 text-warm-500" />
            </div>

            <h2 class="font-display text-3xl font-bold mb-4 text-warm-900">{{ $content['empty_heading'] ?? 'Your Photos Will Shine Here' }}</h2>
            <p class="text-lg leading-relaxed mb-6 text-warm-600">
                {{ $content['empty_description'] ?? 'We\'d love to see what you\'re baking and enjoying! Share a photo of your order and it\'ll appear right here for the whole community to see.' }}
            </p>
            <p class="font-script text-xl mb-10 text-warm-500">{{ $content['empty_script'] ?? 'Be the first to share!' }}</p>

            {{-- Faux gallery preview --}}
            <div class="grid grid-cols-3 gap-3 opacity-20">
                @for ($i = 0; $i < 6; $i++)
                <div class="rounded-xl overflow-hidden" style="aspect-ratio: {{ [1, '4/5', 1, '5/4', 1, '4/5'][$i] }}; background: linear-gradient(135deg, var(--warm-200), var(--warm-300));"></div>
                @endfor
            </div>

            <a href="#share-photo" class="inline-block mt-10 px-8 py-4 rounded-full font-semibold transition-all duration-300 hover:scale-105" style="background: var(--warm-900); color: var(--warm-100);">
                Share Your Photo ↓
            </a>
        </div>
        @endif
    </div>

    {{-- Lightbox Modal --}}
    <div x-show="lightbox" x-transition:enter="transition ease-out duration-300" x-transition:enter-start="opacity-0" x-transition:enter-end="opacity-100" x-transition:leave="transition ease-in duration-200" x-transition:leave-start="opacity-100" x-transition:leave-end="opacity-0"
         class="fixed inset-0 z-50 flex items-center justify-center p-4"
         style="background: rgba(28,20,16,0.92); backdrop-filter: blur(8px);"
         @click.self="lightbox = false" @keydown.escape.window="lightbox = false"
         role="dialog" aria-label="Photo lightbox">
        <button @click="lightbox = false" class="absolute top-6 right-6 w-11 h-11 rounded-full flex items-center justify-center text-xl transition-all duration-200 hover:scale-110" style="background: rgba(255,255,255,0.1); color: var(--warm-300);" aria-label="Close lightbox">&times;</button>
        <div class="max-w-4xl w-full" @click.stop>
            <img :src="lightboxSrc" :alt="lightboxCaption || 'Customer photo'" class="w-full max-h-[75vh] object-contain rounded-xl">
            <div class="mt-4 text-center" x-show="lightboxCaption || lightboxAuthor">
                <p x-show="lightboxCaption" class="italic text-lg text-warm-300" x-text="'&quot;' + lightboxCaption + '&quot;'"></p>
                <p x-show="lightboxAuthor" class="text-sm mt-2 font-semibold text-warm-500" x-text="'— ' + lightboxAuthor"></p>
            </div>
        </div>
    </div>
</section>

{{-- Upload CTA + Form --}}
<x-storefront.dark-section id="share-photo" padding="py-24" style="scroll-margin-top: 80px;">
    <div class="max-w-2xl mx-auto px-4">
        <div class="text-center mb-12">
            <x-storefront.eyebrow line-opacity="0.5" class="mb-6">{{ $content['upload_eyebrow'] ?? 'Share Yours' }}</x-storefront.eyebrow>
            <h2 class="font-display text-3xl md:text-5xl font-bold mb-4 text-warm-100">{{ $content['upload_heading'] ?? 'Share Your Photo' }}</h2>
            <p class="text-lg text-warm-400">{{ $content['upload_description'] ?? 'Show off your order! Photos will appear after approval.' }}</p>
        </div>

        @session('success')
        <x-storefront.alert :dismiss-after="5000">{{ $value }}</x-storefront.alert>
        @endsession

        @if ($errors->any())
        <x-storefront.alert type="error">
            <ul class="list-disc list-inside text-sm">
                @foreach ($errors->all() as $error)
                <li>{{ $error }}</li>
                @endforeach
            </ul>
        </x-storefront.alert>
        @endif

        <div class="p-8 md:p-10 rounded-2xl bg-warm-800 border border-warm-700/20">
            <form action="{{ route('gallery.submit') }}" method="POST" enctype="multipart/form-data" class="space-y-6" x-data="{ submitting: false }" @submit="submitting = true" data-test="gallery-upload-form">
                @csrf
                <div class="grid md:grid-cols-2 gap-6">
                    <div>
                        <label class="block text-sm font-medium mb-2 text-warm-300">Your Name *</label>
                        <input type="text" name="customer_name" value="{{ old('customer_name') }}" required class="storefront-input-dark" data-test="gallery-upload-form-customer-name">
                    </div>
                    <div>
                        <label class="block text-sm font-medium mb-2 text-warm-300">Your Email *</label>
                        <input type="email" name="customer_email" value="{{ old('customer_email') }}" required class="storefront-input-dark" data-test="gallery-upload-form-customer-email">
                    </div>
                </div>

                <div x-data="{ fileName: '' }">
                    <label class="block text-sm font-medium mb-2 text-warm-300">Photo * <span class="font-normal text-warm-500">(JPG, PNG, or WebP — max 5MB)</span></label>
                    <label class="block cursor-pointer rounded-xl p-8 text-center transition-all duration-300 hover:border-opacity-60"
                           style="border: 2px dashed rgba(212,146,12,0.25); background: rgba(212,146,12,0.03);"
                           onmouseover="this.style.borderColor='rgba(212,146,12,0.5)';this.style.background='rgba(212,146,12,0.06)'"
                           onmouseout="this.style.borderColor='rgba(212,146,12,0.25)';this.style.background='rgba(212,146,12,0.03)'">
                        <input type="file" name="photo" accept="image/jpeg,image/png,image/webp" required class="hidden" @change="fileName = $event.target.files[0]?.name || ''" data-test="gallery-upload-form-photo">
                        <div x-show="!fileName">
                            <x-heroicon-o-arrow-up-tray class="w-10 h-10 mx-auto mb-3 text-warm-500/60" />
                            <p class="font-medium mb-1 text-warm-300">Click to upload or drag & drop</p>
                            <p class="text-sm text-warm-500">JPG, PNG, or WebP up to 5MB</p>
                        </div>
                        <div x-show="fileName" class="flex items-center justify-center gap-2">
                            <x-heroicon-o-check class="w-5 h-5 text-green-400" stroke-width="2" />
                            <span class="font-medium text-warm-300" x-text="fileName"></span>
                        </div>
                    </label>
                </div>

                <div>
                    <label class="block text-sm font-medium mb-2 text-warm-300">Caption</label>
                    <textarea name="caption" rows="3" class="storefront-input-dark" data-test="gallery-upload-form-caption">{{ old('caption') }}</textarea>
                </div>

                <div>
                    <label class="block text-sm font-medium mb-2 text-warm-300">Which product? (optional)</label>
                    <select name="product_id" class="storefront-input-dark" data-test="gallery-upload-form-product-id">
                        <option value="">— Select a product —</option>
                        @foreach ($products as $product)
                        <option value="{{ $product->id }}" @selected(old('product_id') == $product->id)>{{ $product->name }}</option>
                        @endforeach
                    </select>
                </div>

                <x-storefront.buttons.async-submit
                    type="submit"
                    idle-text="Submit Photo"
                    loading-text="Uploading..."
                    class="w-full font-semibold hover:scale-[1.02] justify-center"
                    data-test="gallery-upload-form-submit" />
            </form>
        </div>
    </div>
</x-storefront.dark-section>
</x-layouts.storefront>
