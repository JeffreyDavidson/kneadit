@php
    $count = $config['count'] ?? 4;
    $title = $config['title'] ?? 'Customer Gallery';
    $subtitle = $config['subtitle'] ?? 'Shared by our community';
    try {
        $customerPhotos = \App\Models\CustomerPhoto::approved()->featured()->with('product')->latest()->take($count)->get();
    } catch (\Exception $e) {
        $customerPhotos = collect();
    }
@endphp
@if($customerPhotos->count() > 0)
<section class="py-20 px-4" style="background: var(--warm-200);">
    <div class="max-w-6xl mx-auto">
        <div class="text-center mb-14">
            <p class="font-script text-xl mb-2" style="color: var(--warm-500);">{{ $subtitle }}</p>
            <h2 class="font-display text-3xl md:text-5xl font-semibold" style="color: var(--warm-900);">{{ $title }}</h2>
        </div>

        <div class="grid sm:grid-cols-2 lg:grid-cols-4 gap-6">
            @foreach($customerPhotos as $photo)
            <div class="rounded-2xl overflow-hidden group" style="background: white;">
                <div class="overflow-hidden">
                    <img src="{{ asset('storage/customer-photos/' . basename($photo->photo_path)) }}" 
                         alt="Photo by {{ $photo->customer_name }}" 
                         class="w-full h-48 object-cover transition-transform duration-500 group-hover:scale-110" loading="lazy">
                </div>
                <div class="p-4">
                    @if($photo->caption)
                    <p class="text-sm italic mb-2" style="color: var(--warm-700);">"{{ Str::limit($photo->caption, 60) }}"</p>
                    @endif
                    <p class="text-sm font-semibold" style="color: var(--warm-900);">— {{ Str::of($photo->customer_name)->explode(' ')->first() }}</p>
                </div>
            </div>
            @endforeach
        </div>

        <div class="text-center mt-10">
            <a href="{{ route('storefront.gallery') }}" class="inline-flex items-center gap-2 font-display font-medium transition-colors hover:underline" style="color: var(--warm-700);">
                View Full Gallery →
            </a>
        </div>
    </div>
</section>
@endif
