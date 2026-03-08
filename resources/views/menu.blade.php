@extends('layouts.storefront')

@section('content')
<div class="max-w-7xl mx-auto px-4 py-12">
    <!-- Header -->
    <div class="text-center mb-16">
        <p class="font-script text-xl mb-2" style="color: var(--warm-500);">Explore</p>
        <h1 class="font-display text-4xl md:text-5xl font-bold mb-4" style="color: var(--warm-900);">
            {{ \App\Models\Setting::get('store_name', 'Our') }} Menu
        </h1>
        <p class="text-lg max-w-2xl mx-auto" style="color: var(--warm-700);">
            Browse our full selection of handcrafted goods. When you're ready, place an order and we'll have everything freshly prepared for you.
        </p>
    </div>

    <!-- Categories & Products -->
    @forelse($categories as $category)
    <div class="mb-14">
        <div class="flex flex-col sm:flex-row sm:items-center mb-6 border-b pb-4" style="border-color: var(--warm-300);">
            <h2 class="font-display text-2xl md:text-3xl font-semibold" style="color: var(--warm-900);">
                {{ $category->name }}
            </h2>
            @if($category->description)
            <p class="sm:ml-6 mt-1 sm:mt-0 italic" style="color: var(--warm-700);">{{ $category->description }}</p>
            @endif
        </div>
        
        <div class="grid sm:grid-cols-2 lg:grid-cols-3 gap-6">
            @foreach($category->products as $product)
            <div class="card overflow-hidden flex flex-col">
                <!-- Product Image -->
                <div style="aspect-ratio: 4/3;">
                    @if($product->image)
                        <img src="{{ Storage::url($product->image) }}" alt="{{ $product->name }}" class="w-full h-full object-cover">
                    @else
                        <div class="w-full h-full flex items-center justify-center" style="background: linear-gradient(135deg, var(--warm-700), var(--warm-600));">
                            <span class="text-4xl font-display font-bold" style="color: var(--warm-200);">{{ strtoupper(substr($product->name, 0, 1)) }}</span>
                        </div>
                    @endif
                </div>

                <div class="p-6">
                <h3 class="font-display text-xl font-semibold mb-2" style="color: var(--warm-900);">
                    {{ $product->name }}
                    @if($product->seasonal_badge)
                        <span class="inline-block text-xs font-medium px-2 py-0.5 rounded-full bg-amber-100 text-amber-800 ml-2">{{ $product->seasonal_badge }}</span>
                    @endif
                </h3>
                
                @if($product->description)
                <p class="leading-relaxed mb-4 flex-grow" style="color: var(--warm-700);">
                    {{ $product->description }}
                </p>
                @else
                <div class="flex-grow"></div>
                @endif
                
                <div class="flex items-center justify-between mt-auto pt-4 border-t" style="border-color: var(--warm-200);">
                    <span class="font-bold text-xl" style="color: var(--warm-600);">
                        ${{ number_format($product->price, 2) }}
                    </span>
                    
                    @if($product->is_available)
                    <span class="text-green-600 text-sm font-medium px-3 py-1 rounded-full" style="background: #f0fdf4;">Available</span>
                    @else
                    <div x-data="{ showWaitlist: false, submitted: false }" class="text-right">
                        <button x-show="!showWaitlist && !submitted" @click="showWaitlist = true"
                            class="text-amber-700 text-sm font-medium px-3 py-1 rounded-full cursor-pointer hover:bg-amber-100" style="background: #fffbeb;">
                            🔔 Notify Me
                        </button>
                        <span x-show="submitted" class="text-green-600 text-sm font-medium">✓ We'll notify you!</span>
                        <form x-show="showWaitlist" @submit.prevent="fetch('{{ route('product-waitlist.join') }}', {method:'POST',headers:{'Content-Type':'application/json','X-CSRF-TOKEN':'{{ csrf_token() }}','Accept':'application/json'},body:JSON.stringify({product_id:{{ $product->id }},customer_email:$refs.email.value})}).then(()=>{submitted=true;showWaitlist=false})" class="flex gap-1 mt-1">
                            <input x-ref="email" type="email" required placeholder="Email" class="text-sm rounded border px-2 py-1 w-36" style="border-color: var(--warm-300);">
                            <button type="submit" class="text-sm px-2 py-1 rounded text-white" style="background: var(--warm-600);">Go</button>
                        </form>
                    </div>
                    @endif
                </div>
                </div>
            </div>
            @endforeach
        </div>
    </div>
    @empty
    <div class="text-center py-16">
        <p class="text-lg" style="color: var(--warm-700);">Our menu is being updated. Please check back soon!</p>
    </div>
    @endforelse

    <!-- CTA -->
    <div class="text-center mt-8">
        <div class="rounded-2xl p-12" style="background: var(--warm-200);">
            <h2 class="font-display text-3xl font-semibold mb-4" style="color: var(--warm-900);">
                See Something You Love?
            </h2>
            <p class="text-lg mb-8 max-w-2xl mx-auto" style="color: var(--warm-700);">
                Place an order and we'll have it freshly prepared just for you. All orders require {{ \App\Models\Setting::get('order_lead_time_hours', '24') }} hours advance notice.
            </p>
            <a href="{{ route('order.create') }}" class="btn-primary text-lg px-8 py-4 inline-block">
                Place an Order
            </a>
        </div>
    </div>
</div>
@endsection