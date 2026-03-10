@extends('layouts.storefront')

@section('content')
<style>
    .gallery-item {
        position: relative;
        border-radius: 16px;
        overflow: hidden;
        cursor: pointer;
        transition: all 0.4s cubic-bezier(0.25, 0.46, 0.45, 0.94);
    }
    .gallery-item:hover {
        transform: translateY(-4px);
        box-shadow: 0 20px 50px rgba(28, 20, 16, 0.3);
    }
    .gallery-item:hover img {
        transform: scale(1.05);
    }
    .gallery-item img {
        transition: transform 0.6s cubic-bezier(0.25, 0.46, 0.45, 0.94);
    }
    .gallery-item:hover .gallery-caption {
        opacity: 1;
        transform: translateY(0);
    }
    .gallery-caption {
        opacity: 0;
        transform: translateY(10px);
        transition: all 0.4s ease;
    }
    .gallery-input {
        width: 100%;
        padding: 0.875rem 1.25rem;
        border-radius: 0.75rem;
        border: 1.5px solid rgba(139,104,68,0.25);
        background: var(--warm-800);
        font-family: var(--font-body);
        font-size: 1rem;
        color: var(--warm-200);
        transition: border-color 0.3s, box-shadow 0.3s;
        outline: none;
    }
    .gallery-input:focus {
        border-color: var(--warm-500);
        box-shadow: 0 0 0 3px rgba(212,146,12,0.15);
    }
    .gallery-input::placeholder {
        color: var(--warm-600);
    }
    @keyframes galleryFadeUp {
        from { opacity: 0; transform: translateY(20px); }
        to   { opacity: 1; transform: translateY(0); }
    }
    .gallery-fade-1 { animation: galleryFadeUp 0.7s ease-out 0.2s both; }
    .gallery-fade-2 { animation: galleryFadeUp 0.7s ease-out 0.4s both; }
</style>

{{-- Dark Hero Banner --}}
<section class="relative overflow-hidden" style="background: var(--warm-900); min-height: 45vh;">
    <div class="absolute inset-0 opacity-[0.03]" style="background-image: url('data:image/svg+xml,%3Csvg viewBox=%220 0 256 256%22 xmlns=%22http://www.w3.org/2000/svg%22%3E%3Cfilter id=%22noise%22%3E%3CfeTurbulence type=%22fractalNoise%22 baseFrequency=%220.9%22 numOctaves=%224%22 stitchTiles=%22stitch%22/%3E%3C/filter%3E%3Crect width=%22100%25%22 height=%22100%25%22 filter=%22url(%23noise)%22/%3E%3C/svg%3E');"></div>
    <div class="absolute inset-0" style="background: radial-gradient(ellipse at 50% 80%, rgba(212,146,12,0.08), transparent 60%);"></div>

    <div class="relative z-10 flex flex-col items-center justify-center text-center px-4" style="min-height: 45vh; padding-top: 8vh;">
        <div class="gallery-fade-1 flex items-center gap-3 mb-6">
            <span class="block w-8 h-px" style="background: var(--warm-500);"></span>
            <span class="uppercase tracking-[0.25em] text-xs font-semibold" style="color: var(--warm-500);">From Our Customers</span>
            <span class="block w-8 h-px" style="background: var(--warm-500);"></span>
        </div>
        <h1 class="gallery-fade-1 font-display text-5xl md:text-7xl lg:text-8xl font-bold leading-none mb-6" style="color: var(--warm-100);">
            Customer Gallery
        </h1>
        <p class="gallery-fade-2 text-lg md:text-xl max-w-xl mx-auto" style="color: var(--warm-400);">
            See what our customers are creating and enjoying!
        </p>
    </div>
</section>

{{-- Masonry Gallery with Lightbox --}}
<section style="background: var(--warm-100);" x-data="{ lightbox: false, lightboxSrc: '', lightboxCaption: '', lightboxAuthor: '' }">
    <div class="max-w-7xl mx-auto px-4 py-16 md:py-24">
        @if($photos->count() > 0)
        <div class="columns-1 sm:columns-2 lg:columns-3 gap-6 mb-12">
            @foreach($photos as $photo)
            <div class="break-inside-avoid mb-6">
                <div class="gallery-item"
                     @click="lightboxSrc = '{{ asset('storage/customer-photos/' . basename($photo->photo_path)) }}'; lightboxCaption = '{{ addslashes($photo->caption ?? '') }}'; lightboxAuthor = '{{ addslashes($photo->customer_name) }}'; lightbox = true"
                     style="background: var(--warm-200);">
                    @if($photo->is_featured)
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
                        @if($photo->caption)
                        <p class="italic text-sm mb-2" style="color: var(--warm-200);">"{{ $photo->caption }}"</p>
                        @endif
                        <div class="flex items-center gap-2">
                            <div class="w-7 h-7 rounded-full flex items-center justify-center text-xs font-bold" style="background: rgba(212,146,12,0.3); color: var(--warm-400);">
                                {{ strtoupper(substr($photo->customer_name, 0, 1)) }}
                            </div>
                            <span class="text-sm font-semibold" style="color: var(--warm-300);">{{ Str::of($photo->customer_name)->explode(' ')->first() }}</span>
                        </div>
                        @if($photo->product)
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
        <div class="text-center py-20">
            <div class="w-20 h-20 rounded-full mx-auto mb-6 flex items-center justify-center" style="background: var(--warm-200);">
                <span class="text-3xl">📸</span>
            </div>
            <p class="font-display text-2xl font-bold mb-2" style="color: var(--warm-800);">No photos yet</p>
            <p class="text-lg" style="color: var(--warm-600);">Be the first to share!</p>
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
<section class="relative py-20 overflow-hidden" style="background: var(--warm-900);">
    <div class="absolute inset-0 opacity-[0.03]" style="background-image: url('data:image/svg+xml,%3Csvg viewBox=%220 0 256 256%22 xmlns=%22http://www.w3.org/2000/svg%22%3E%3Cfilter id=%22noise%22%3E%3CfeTurbulence type=%22fractalNoise%22 baseFrequency=%220.9%22 numOctaves=%224%22 stitchTiles=%22stitch%22/%3E%3C/filter%3E%3Crect width=%22100%25%22 height=%22100%25%22 filter=%22url(%23noise)%22/%3E%3C/svg%3E');"></div>
    <div class="absolute inset-0" style="background: radial-gradient(ellipse at 50% 50%, rgba(212,146,12,0.06), transparent 60%);"></div>
    <div class="relative z-10 max-w-2xl mx-auto px-4">
        <div class="text-center mb-10">
            <div class="flex items-center justify-center gap-3 mb-4">
                <span class="block w-8 h-px" style="background: var(--warm-500); opacity: 0.5;"></span>
                <span class="uppercase tracking-[0.25em] text-xs font-semibold" style="color: var(--warm-500);">Share Yours</span>
                <span class="block w-8 h-px" style="background: var(--warm-500); opacity: 0.5;"></span>
            </div>
            <h2 class="font-display text-3xl md:text-4xl font-bold mb-3" style="color: var(--warm-100);">Share Your Photo</h2>
            <p style="color: var(--warm-400);">Show off your order! Photos will appear after approval.</p>
        </div>

        @if(session('success'))
        <div class="rounded-2xl p-5 mb-6 text-center" style="background: rgba(34,197,94,0.15); border: 1px solid rgba(34,197,94,0.3); color: #86efac;">
            {{ session('success') }}
        </div>
        @endif

        @if($errors->any())
        <div class="rounded-2xl p-5 mb-6" style="background: rgba(239,68,68,0.15); border: 1px solid rgba(239,68,68,0.3); color: #fca5a5;">
            <ul class="list-disc list-inside text-sm">
                @foreach($errors->all() as $error)
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

                <div>
                    <label class="block text-sm font-medium mb-2" style="color: var(--warm-300);">Photo * <span class="font-normal" style="color: var(--warm-500);">(JPG, PNG, or WebP — max 5MB)</span></label>
                    <input type="file" name="photo" accept="image/jpeg,image/png,image/webp" required class="gallery-input">
                </div>

                <div>
                    <label class="block text-sm font-medium mb-2" style="color: var(--warm-300);">Caption</label>
                    <textarea name="caption" rows="3" class="gallery-input">{{ old('caption') }}</textarea>
                </div>

                <div>
                    <label class="block text-sm font-medium mb-2" style="color: var(--warm-300);">Which product? (optional)</label>
                    <select name="product_id" class="gallery-input">
                        <option value="">— Select a product —</option>
                        @foreach($products as $product)
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
@endsection
