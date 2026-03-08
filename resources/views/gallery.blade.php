@extends('layouts.storefront')

@section('content')
<div class="max-w-7xl mx-auto px-4 py-12">
    <!-- Header -->
    <div class="text-center mb-16">
        <p class="font-script text-xl mb-2" style="color: var(--warm-500);">From our happy customers</p>
        <h1 class="font-display text-4xl md:text-5xl font-bold mb-4" style="color: var(--warm-900);">
            Customer Gallery
        </h1>
        <p class="text-lg" style="color: var(--warm-700);">
            See what our customers are creating and enjoying!
        </p>
    </div>

    <!-- Photo Grid -->
    @if($photos->count() > 0)
    <div class="columns-1 sm:columns-2 lg:columns-3 gap-6 mb-16">
        @foreach($photos as $photo)
        <div class="break-inside-avoid mb-6">
            <div class="card overflow-hidden {{ $photo->is_featured ? 'ring-2' : '' }}" style="{{ $photo->is_featured ? 'ring-color: var(--warm-500);' : '' }}">
                @if($photo->is_featured)
                <div class="px-3 py-1 text-xs font-semibold text-center" style="background: var(--warm-500); color: white;">
                    ⭐ Featured
                </div>
                @endif
                <img 
                    src="{{ asset('storage/customer-photos/' . basename($photo->photo_path)) }}" 
                    alt="Photo by {{ $photo->customer_name }}"
                    class="w-full object-cover"
                    loading="lazy"
                >
                <div class="p-4">
                    @if($photo->caption)
                    <p class="italic mb-2" style="color: var(--warm-700);">"{{ $photo->caption }}"</p>
                    @endif
                    <p class="font-semibold text-sm" style="color: var(--warm-900);">
                        — {{ Str::of($photo->customer_name)->explode(' ')->first() }}
                    </p>
                    @if($photo->product)
                    <p class="text-xs mt-1" style="color: var(--warm-500);">{{ $photo->product->name }}</p>
                    @endif
                </div>
            </div>
        </div>
        @endforeach
    </div>

    {{ $photos->links() }}
    @else
    <div class="text-center py-16 card mb-16">
        <p class="text-6xl mb-4">📸</p>
        <p class="text-lg" style="color: var(--warm-700);">No photos yet. Be the first to share!</p>
    </div>
    @endif

    <!-- Submission Form -->
    <div class="max-w-2xl mx-auto">
        <div class="card p-8">
            <div class="text-center mb-8">
                <h2 class="font-display text-2xl font-semibold mb-2" style="color: var(--warm-900);">Share Your Photo</h2>
                <p style="color: var(--warm-700);">Show off your order! Photos will appear after approval.</p>
            </div>

            @if(session('success'))
            <div class="rounded-lg p-4 mb-6 text-center" style="background: #d1fae5; color: #065f46;">
                {{ session('success') }}
            </div>
            @endif

            @if($errors->any())
            <div class="rounded-lg p-4 mb-6" style="background: #fee2e2; color: #991b1b;">
                <ul class="list-disc list-inside">
                    @foreach($errors->all() as $error)
                    <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
            @endif

            <form action="{{ route('gallery.submit') }}" method="POST" enctype="multipart/form-data" class="space-y-6">
                @csrf
                <div class="grid md:grid-cols-2 gap-6">
                    <div>
                        <label class="block text-sm font-medium mb-2" style="color: var(--warm-800);">Your Name *</label>
                        <input type="text" name="customer_name" value="{{ old('customer_name') }}" required
                            class="w-full rounded-lg border px-4 py-3" style="border-color: var(--warm-300);">
                    </div>
                    <div>
                        <label class="block text-sm font-medium mb-2" style="color: var(--warm-800);">Your Email *</label>
                        <input type="email" name="customer_email" value="{{ old('customer_email') }}" required
                            class="w-full rounded-lg border px-4 py-3" style="border-color: var(--warm-300);">
                    </div>
                </div>

                <div>
                    <label class="block text-sm font-medium mb-2" style="color: var(--warm-800);">Photo * <span class="font-normal">(JPG, PNG, or WebP — max 5MB)</span></label>
                    <input type="file" name="photo" accept="image/jpeg,image/png,image/webp" required
                        class="w-full rounded-lg border px-4 py-3" style="border-color: var(--warm-300);">
                </div>

                <div>
                    <label class="block text-sm font-medium mb-2" style="color: var(--warm-800);">Caption</label>
                    <textarea name="caption" rows="3" class="w-full rounded-lg border px-4 py-3" style="border-color: var(--warm-300);">{{ old('caption') }}</textarea>
                </div>

                <div>
                    <label class="block text-sm font-medium mb-2" style="color: var(--warm-800);">Which product? (optional)</label>
                    <select name="product_id" class="w-full rounded-lg border px-4 py-3" style="border-color: var(--warm-300);">
                        <option value="">— Select a product —</option>
                        @foreach($products as $product)
                        <option value="{{ $product->id }}" {{ old('product_id') == $product->id ? 'selected' : '' }}>{{ $product->name }}</option>
                        @endforeach
                    </select>
                </div>

                <button type="submit" class="btn-primary w-full py-3 text-lg">
                    Submit Photo
                </button>
            </form>
        </div>
    </div>
</div>
@endsection
