@extends('emails.layout')

@section('title', $product->name . ' is Back!')

@section('content')
<p style="margin: 0 0 15px;">Hello {{ $customerName ?: 'there' }},</p>

<p style="margin: 0 0 20px;">Great news — <strong>{{ $product->name }}</strong> is back and available at {{ $storeName }}!</p>

<div style="background-color: #fef9ef; border-radius: 8px; padding: 20px; margin: 0 0 20px; border-left: 4px solid #d4920c; text-align: center;">
    @if($product->image)
        <img src="{{ asset('storage/' . $product->image) }}" alt="{{ $product->name }}" style="max-width: 200px; border-radius: 8px; margin-bottom: 15px;">
    @endif
    <div style="font-size: 20px; font-weight: 700; color: #1c1410; margin-bottom: 5px;">{{ $product->name }}</div>
    <div style="font-size: 16px; color: #8b6844;">${{ number_format($product->price, 2) }}</div>
</div>

<p style="margin: 0 0 20px;">Don't miss out — order now before it's gone again!</p>

<div style="text-align: center; margin: 25px 0;">
    <a href="{{ route('order.create') }}" style="display: inline-block; background-color: #d4920c; color: #ffffff; padding: 14px 30px; border-radius: 8px; text-decoration: none; font-weight: 600; font-size: 16px;">
        Order Now
    </a>
</div>
@endsection
