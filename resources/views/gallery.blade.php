<x-layouts.storefront>
<link rel="stylesheet" href="{{ asset('css/gallery.css') }}">


{{-- Photo-Forward Hero --}}
<section class="relative overflow-hidden" style="min-height: 55vh;">
    <div class="absolute inset-0">
        <img src="{{ $settings->heroImageUrl() }}" alt="{{ $settings->storeName }} gallery" class="w-full h-full object-cover gallery-hero-img">
    </div>
    <div class="absolute inset-0" style="background: linear-gradient(to bottom, rgba(28,20,16,0.4) 0%, rgba(28,20,16,0.65) 50%, rgba(28,20,16,0.95) 100%);"></div>
    <div class="absolute inset-0 opacity-[0.03]" style="background-image: url('data:image/svg+xml,%3Csvg viewBox=%220 0 256 256%22 xmlns=%22http://www.w3.org/2000/svg%22%3E%3Cfilter id=%22noise%22%3E%3CfeTurbulence type=%22fractalNoise%22 baseFrequency=%220.9%22 numOctaves=%224%22 stitchTiles=%22stitch%22/%3E%3C/filter%3E%3Crect width=%22100%25%22 height=%22100%25%22 filter=%22url(%23noise)%22/%3E%3C/svg%3E');"></div>

    <div class="relative z-10 flex flex-col items-center justify-end text-center px-4 pb-20" style="min-height: 55vh;">
        <div class="gallery-fade-1 flex items-center gap-3 mb-6">
            <span class="block w-8 h-px" style="background: var(--warm-500);"></span>
            <span class="uppercase tracking-[0.25em] text-xs font-semibold" style="color: var(--warm-500);">{{ $content['hero_eyebrow'] ?? 'From Our Customers' }}</span>
            <span class="block w-8 h-px" style="background: var(--warm-500);"></span>
        </div>
        <h1 class="gallery-fade-1 font-display text-5xl md:text-7xl lg:text-8xl font-bold leading-none mb-4" style="color: var(--warm-100);">
            {{ $content['hero_title'] ?? 'Customer Gallery' }}
        </h1>
        <p class="gallery-fade-2 font-script text-2xl md:text-3xl" style="color: var(--warm-400);">
            Moments worth sharing
        </p>
        <p class="gallery-fade-3 text-lg md:text-xl max-w-xl mx-auto mt-4" style="color: var(--warm-400);">
            {{ $content['hero_subtitle'] ?? 'See what our customers are creating and enjoying!' }}
        </p>
    </div>
</section>

{{-- Masonry Gallery with Lightbox --}}
<section style="background: var(--warm-100);" x-data="{ lightbox: false, lightboxSrc: '', lightboxCaption: '', lightboxAuthor: '' }">
    <div class="max-w-7xl mx-auto px-4 py-16 md:py-24">
        @if ($photos->count() > 0)
        <div class="columns-1 sm:columns-2 lg:columns-3 gap-6 mb-12">
            @foreach ($photos as $photo)
            <div class="break-inside-avoid mb-6">
                <div class="gallery-item"
                     @click="lightboxSrc = '{{ asset('storage/customer-photos/' . basename($photo->photo_path)) }}'; lightboxCaption = '{{ addslashes($photo->caption ?? '') }}'; lightboxAuthor = '{{ addslashes($photo->customer_name) }}'; lightbox = true"
                     style="background: var(--warm-200);">
                    @if ($photo->is_featured)
                    <div class="absolute top-3 left-3 z-10 px-3 py-1 rounded-full text-xs font-bold uppercase tracking-wider" style="background: var(--warm-500); color: var(--warm-900);">
                        ⭐ Featured
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
                        <p class="italic text-sm mb-2" style="color: var(--warm-200);">"{{ $photo->caption }}"</p>
                        @endif
                        <div class="flex items-center gap-2">
                            <div class="w-7 h-7 rounded-full flex items-center justify-center text-xs font-bold" style="background: rgba(212,146,12,0.3); color: var(--warm-400);">
                                {{ strtoupper(substr($photo->customer_name, 0, 1)) }}
                            </div>
                            <span class="text-sm font-semibold" style="color: var(--warm-300);">{{ Str::of($photo->customer_name)->explode(' ')->first() }}</span>
                        </div>
                        @if ($photo->product)
                        <p class="text-xs mt-1" style="color: var(--warm-500);">{{ $photo->product->name }}</p>
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
            <div class="w-20 h-20 rounded-full mx-auto mb-8 flex items-center justify-center" style="background: var(--warm-200);">
                <svg class="w-10 h-10" style="color: var(--warm-500);" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M6.827 6.175A2.31 2.31 0 015.186 7.23c-.38.054-.757.112-1.134.175C2.999 7.58 2.25 8.507 2.25 9.574V18a2.25 2.25 0 002.25 2.25h15A2.25 2.25 0 0021.75 18V9.574c0-1.067-.75-1.994-1.802-2.169a47.865 47.865 0 00-1.134-.175 2.31 2.31 0 01-1.64-1.055l-.822-1.316a2.192 2.192 0 00-1.736-1.039 48.774 48.774 0 00-5.232 0 2.192 2.192 0 00-1.736 1.039l-.821 1.316z"/>
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M16.5 12.75a4.5 4.5 0 11-9 0 4.5 4.5 0 019 0z"/>
                </svg>
            </div>

            <h2 class="font-display text-3xl font-bold mb-4" style="color: var(--warm-900);">{{ $content['empty_heading'] ?? 'Your Photos Will Shine Here' }}</h2>
            <p class="text-lg leading-relaxed mb-6" style="color: var(--warm-600);">
                {{ $content['empty_description'] ?? 'We\'d love to see what you\'re baking and enjoying! Share a photo of your order and it\'ll appear right here for the whole community to see.' }}
            </p>
            <p class="font-script text-xl mb-10" style="color: var(--warm-500);">{{ $content['empty_script'] ?? 'Be the first to share!' }}</p>

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
         @click.self="lightbox = false" @keydown.escape.window="lightbox = false">
        <button @click="lightbox = false" class="absolute top-6 right-6 w-10 h-10 rounded-full flex items-center justify-center text-xl transition-all duration-200 hover:scale-110" style="background: rgba(255,255,255,0.1); color: var(--warm-300);">&times;</button>
        <div class="max-w-4xl w-full" @click.stop>
            <img :src="lightboxSrc" alt="" class="w-full max-h-[75vh] object-contain rounded-xl">
            <div class="mt-4 text-center" x-show="lightboxCaption || lightboxAuthor">
                <p x-show="lightboxCaption" class="italic text-lg" style="color: var(--warm-300);" x-text="'\"' + lightboxCaption + '\"'"></p>
                <p x-show="lightboxAuthor" class="text-sm mt-2 font-semibold" style="color: var(--warm-500);" x-text="'— ' + lightboxAuthor"></p>
            </div>
        </div>
    </div>
</section>

{{-- Upload CTA + Form --}}
<section id="share-photo" class="relative py-24 overflow-hidden" style="background: var(--warm-900); scroll-margin-top: 80px;">
    <div class="absolute inset-0 opacity-[0.03]" style="background-image: url('data:image/svg+xml,%3Csvg viewBox=%220 0 256 256%22 xmlns=%22http://www.w3.org/2000/svg%22%3E%3Cfilter id=%22noise%22%3E%3CfeTurbulence type=%22fractalNoise%22 baseFrequency=%220.9%22 numOctaves=%224%22 stitchTiles=%22stitch%22/%3E%3C/filter%3E%3Crect width=%22100%25%22 height=%22100%25%22 filter=%22url(%23noise)%22/%3E%3C/svg%3E');"></div>
    <div class="absolute inset-0" style="background: radial-gradient(ellipse at 50% 50%, rgba(212,146,12,0.06), transparent 60%);"></div>
    <div class="relative z-10 max-w-2xl mx-auto px-4">
        <div class="text-center mb-12">
            <div class="flex items-center justify-center gap-3 mb-6">
                <span class="block w-8 h-px" style="background: var(--warm-500); opacity: 0.5;"></span>
                <span class="uppercase tracking-[0.25em] text-xs font-semibold" style="color: var(--warm-500);">{{ $content['upload_eyebrow'] ?? 'Share Yours' }}</span>
                <span class="block w-8 h-px" style="background: var(--warm-500); opacity: 0.5;"></span>
            </div>
            <h2 class="font-display text-3xl md:text-5xl font-bold mb-4" style="color: var(--warm-100);">{{ $content['upload_heading'] ?? 'Share Your Photo' }}</h2>
            <p class="text-lg" style="color: var(--warm-400);">{{ $content['upload_description'] ?? 'Show off your order! Photos will appear after approval.' }}</p>
        </div>

        @if (session('success'))
        <div class="rounded-2xl p-5 mb-6 text-center" style="background: rgba(34,197,94,0.15); border: 1px solid rgba(34,197,94,0.3); color: #86efac;">
            {{ session('success') }}
        </div>
        @endif

        @if ($errors->any())
        <div class="rounded-2xl p-5 mb-6" style="background: rgba(239,68,68,0.15); border: 1px solid rgba(239,68,68,0.3); color: #fca5a5;">
            <ul class="list-disc list-inside text-sm">
                @foreach ($errors->all() as $error)
                <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
        @endif

        <div class="p-8 md:p-10 rounded-2xl" style="background: var(--warm-800); border: 1px solid rgba(139,104,68,0.2);">
            <form action="{{ route('gallery.submit') }}" method="POST" enctype="multipart/form-data" class="space-y-6">
                @csrf
                <div class="grid md:grid-cols-2 gap-6">
                    <div>
                        <label class="block text-sm font-medium mb-2" style="color: var(--warm-300);">Your Name *</label>
                        <input type="text" name="customer_name" value="{{ old('customer_name') }}" required class="gallery-input">
                    </div>
                    <div>
                        <label class="block text-sm font-medium mb-2" style="color: var(--warm-300);">Your Email *</label>
                        <input type="email" name="customer_email" value="{{ old('customer_email') }}" required class="gallery-input">
                    </div>
                </div>

                <div x-data="{ fileName: '' }">
                    <label class="block text-sm font-medium mb-2" style="color: var(--warm-300);">Photo * <span class="font-normal" style="color: var(--warm-500);">(JPG, PNG, or WebP — max 5MB)</span></label>
                    <label class="block cursor-pointer rounded-xl p-8 text-center transition-all duration-300 hover:border-opacity-60"
                           style="border: 2px dashed rgba(212,146,12,0.25); background: rgba(212,146,12,0.03);"
                           onmouseover="this.style.borderColor='rgba(212,146,12,0.5)';this.style.background='rgba(212,146,12,0.06)'"
                           onmouseout="this.style.borderColor='rgba(212,146,12,0.25)';this.style.background='rgba(212,146,12,0.03)'">
                        <input type="file" name="photo" accept="image/jpeg,image/png,image/webp" required class="hidden" @change="fileName = $event.target.files[0]?.name || ''">
                        <div x-show="!fileName">
                            <svg class="w-10 h-10 mx-auto mb-3" style="color: var(--warm-500); opacity: 0.6;" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M3 16.5v2.25A2.25 2.25 0 005.25 21h13.5A2.25 2.25 0 0021 18.75V16.5m-13.5-9L12 3m0 0l4.5 4.5M12 3v13.5"/>
                            </svg>
                            <p class="font-medium mb-1" style="color: var(--warm-300);">Click to upload or drag & drop</p>
                            <p class="text-sm" style="color: var(--warm-500);">JPG, PNG, or WebP up to 5MB</p>
                        </div>
                        <div x-show="fileName" class="flex items-center justify-center gap-2">
                            <svg class="w-5 h-5" style="color: #4ade80;" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4.5 12.75l6 6 9-13.5"/></svg>
                            <span class="font-medium" style="color: var(--warm-300);" x-text="fileName"></span>
                        </div>
                    </label>
                </div>

                <div>
                    <label class="block text-sm font-medium mb-2" style="color: var(--warm-300);">Caption</label>
                    <textarea name="caption" rows="3" class="gallery-input">{{ old('caption') }}</textarea>
                </div>

                <div>
                    <label class="block text-sm font-medium mb-2" style="color: var(--warm-300);">Which product? (optional)</label>
                    <select name="product_id" class="gallery-input">
                        <option value="">— Select a product —</option>
                        @foreach ($products as $product)
                        <option value="{{ $product->id }}" {{ old('product_id') == $product->id ? 'selected' : '' }}>{{ $product->name }}</option>
                        @endforeach
                    </select>
                </div>

                <button type="submit" class="w-full py-4 rounded-full font-semibold text-lg transition-all duration-300 hover:scale-[1.02] hover:shadow-lg" style="background: var(--warm-500); color: var(--warm-900);">
                    Submit Photo
                </button>
            </form>
        </div>
    </div>
</section>
</x-layouts.storefront>
