@extends('layouts.storefront')

@section('content')
<div class="max-w-4xl mx-auto px-4 py-12">
    <div class="text-center mb-12">
        <div class="inline-flex items-center justify-center w-20 h-20 bg-green-100 rounded-full mb-6">
            <svg class="w-10 h-10 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
            </svg>
        </div>
        
        <h1 class="font-display text-4xl font-bold text-warm-900 mb-4">
            Order Confirmed!
        </h1>
        
        <p class="text-warm-700 text-lg mb-2">
            Thank you for your order. We'll start preparing your items right away.
        </p>
        
        <p class="text-warm-600 font-semibold">
            Order Number: <span class="font-mono bg-warm-100 px-3 py-1 rounded">{{ $order->order_number }}</span>
        </p>
    </div>

    <div class="grid md:grid-cols-2 gap-8">
        <!-- Order Details -->
        <div class="card p-6">
            <h2 class="font-display text-xl font-semibold text-warm-900 mb-6">Order Details</h2>
            
            <div class="space-y-4">
                @foreach($order->orderItems as $item)
                <div class="flex justify-between items-center border-b border-warm-200 pb-3 last:border-b-0 last:pb-0">
                    <div>
                        <h4 class="font-medium text-warm-900">
                            {{ $item->product->name ?? 'Product' }}
                        </h4>
                        <p class="text-sm text-warm-600">
                            Quantity: {{ $item->quantity }} × ${{ number_format($item->unit_price, 2) }}
                        </p>
                    </div>
                    <span class="font-semibold text-warm-900">
                        ${{ number_format($item->total_price, 2) }}
                    </span>
                </div>
                @endforeach
            </div>
            
            <div class="border-t border-warm-300 pt-4 mt-6 space-y-2">
                <div class="flex justify-between">
                    <span>Subtotal:</span>
                    <span>${{ number_format($order->subtotal, 2) }}</span>
                </div>
                
                @if($order->delivery_fee > 0)
                <div class="flex justify-between">
                    <span>Delivery Fee:</span>
                    <span>${{ number_format($order->delivery_fee, 2) }}</span>
                </div>
                @endif
                
                @if($order->discount_amount > 0)
                <div class="flex justify-between text-green-600">
                    <span>
                        Discount
                        @if($order->coupon)
                        ({{ $order->coupon->code }})
                        @endif
                    </span>
                    <span>-${{ number_format($order->discount_amount, 2) }}</span>
                </div>
                @endif
                
                <div class="flex justify-between font-bold text-lg border-t border-warm-300 pt-2">
                    <span>Total:</span>
                    <span>${{ number_format($order->total, 2) }}</span>
                </div>
            </div>
        </div>

        <!-- Customer & Delivery Info -->
        <div class="card p-6">
            <h2 class="font-display text-xl font-semibold text-warm-900 mb-6">Delivery Information</h2>
            
            <div class="space-y-4">
                <div>
                    <h4 class="font-medium text-warm-900 mb-1">Customer</h4>
                    <p class="text-warm-700">{{ $order->customer->name }}</p>
                    <p class="text-warm-700">{{ $order->customer->email }}</p>
                    @if($order->customer->phone)
                    <p class="text-warm-700">{{ $order->customer->phone }}</p>
                    @endif
                </div>
                
                <div>
                    <h4 class="font-medium text-warm-900 mb-1">
                        {{ $order->delivery_type === 'delivery' ? 'Delivery' : 'Pickup' }} Details
                    </h4>
                    <p class="text-warm-700">
                        <span class="font-medium">Date:</span> 
                        {{ \Carbon\Carbon::parse($order->delivery_date)->format('l, F j, Y') }}
                    </p>
                    @if($order->delivery_time)
                    <p class="text-warm-700">
                        <span class="font-medium">Time:</span> 
                        {{ $order->delivery_time }}
                    </p>
                    @endif
                    
                    @if($order->delivery_type === 'delivery' && $order->delivery_address)
                    <p class="text-warm-700">
                        <span class="font-medium">Address:</span><br>
                        {{ $order->delivery_address }}
                    </p>
                    @endif
                </div>
                
                @if($order->notes)
                <div>
                    <h4 class="font-medium text-warm-900 mb-1">Special Instructions</h4>
                    <p class="text-warm-700">{{ $order->notes }}</p>
                </div>
                @endif
                
                <div>
                    <h4 class="font-medium text-warm-900 mb-1">Status</h4>
                    <span class="inline-block px-3 py-1 rounded-full text-sm font-medium
                        @if($order->status === 'pending')
                            bg-yellow-100 text-yellow-800
                        @elseif($order->status === 'confirmed')
                            bg-blue-100 text-blue-800
                        @elseif($order->status === 'preparing')
                            bg-orange-100 text-orange-800
                        @elseif($order->status === 'ready')
                            bg-green-100 text-green-800
                        @elseif($order->status === 'completed')
                            bg-green-100 text-green-800
                        @elseif($order->status === 'cancelled')
                            bg-red-100 text-red-800
                        @else
                            bg-gray-100 text-gray-800
                        @endif
                    ">
                        {{ ucfirst($order->status) }}
                    </span>
                </div>
            </div>
        </div>
    </div>

    <!-- What's Next -->
    <div class="card p-8 mt-8 text-center">
        <h2 class="font-display text-2xl font-semibold text-warm-900 mb-4">What Happens Next?</h2>
        
        <div class="grid md:grid-cols-3 gap-6 text-center">
            <div>
                <div class="w-12 h-12 bg-warm-200 rounded-full flex items-center justify-center mx-auto mb-3">
                    <span class="font-bold text-warm-700">1</span>
                </div>
                <h3 class="font-semibold text-warm-900 mb-2">Order Confirmation</h3>
                <p class="text-sm text-warm-700">
                    You'll receive an email confirmation with your order details.
                </p>
            </div>
            
            <div>
                <div class="w-12 h-12 bg-warm-200 rounded-full flex items-center justify-center mx-auto mb-3">
                    <span class="font-bold text-warm-700">2</span>
                </div>
                <h3 class="font-semibold text-warm-900 mb-2">Preparation</h3>
                <p class="text-sm text-warm-700">
                    Our bakers will start preparing your items fresh for your pickup/delivery date.
                </p>
            </div>
            
            <div>
                <div class="w-12 h-12 bg-warm-200 rounded-full flex items-center justify-center mx-auto mb-3">
                    <span class="font-bold text-warm-700">3</span>
                </div>
                <h3 class="font-semibold text-warm-900 mb-2">
                    {{ $order->delivery_type === 'delivery' ? 'Delivery' : 'Pickup' }}
                </h3>
                <p class="text-sm text-warm-700">
                    @if($order->delivery_type === 'delivery')
                        We'll deliver your fresh items to your address on the scheduled date.
                    @else
                        Your items will be ready for pickup on your scheduled date.
                    @endif
                </p>
            </div>
        </div>
        
        <div class="mt-8 space-y-4">
            <p class="text-warm-700">
                <strong>Questions about your order?</strong> Contact us at 
                <a href="{{ route('contact') }}" class="text-warm-600 hover:underline">our contact page</a>
                or reference your order number: <strong>{{ $order->order_number }}</strong>
            </p>
            
            <div class="flex flex-col sm:flex-row gap-4 justify-center items-center">
                <a href="{{ route('home') }}" class="btn-primary">
                    Continue Shopping
                </a>
                <button onclick="window.print()" class="btn-secondary">
                    Print Order Details
                </button>
            </div>
        </div>
    </div>
</div>
@endsection

@section('styles')
<style>
@media print {
    .nav, footer, button, .btn-primary, .btn-secondary {
        display: none !important;
    }
    
    body {
        background: white !important;
    }
    
    .card {
        border: 1px solid #ddd !important;
        box-shadow: none !important;
    }
}
</style>
@endsection