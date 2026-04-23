@extends('emails.layout')

@php
/** @var \Illuminate\Support\Collection<int, \App\Models\Inventory\Ingredient> $ingredients */
/** @var string $primaryColor */
/** @var string $secondaryColor */
/** @var string $storeName */
@endphp

@section('title', 'Low-Stock Alert')

@section('content')
    <p style="margin: 0 0 15px;">Hi {{ $storeName }} team,</p>

    <p style="margin: 0 0 20px;">The following {{ $ingredients->count() === 1 ? 'ingredient is' : 'ingredients are' }} at or below their low-stock threshold. You'll probably want to reorder before your next prep day.</p>

    <div style="background-color: #fef9ef; border-radius: 8px; padding: 20px; margin: 0 0 20px;">
        @foreach ($ingredients as $ingredient)
            <div style="padding: 10px 0; border-bottom: 1px solid #eee; display: flex; justify-content: space-between; align-items: center;">
                <div>
                    <div style="font-weight: 600; color: {{ $secondaryColor }};">{{ $ingredient->name }}</div>
                    <div style="font-size: 13px; color: #666;">Threshold: {{ $ingredient->low_stock_threshold }} {{ $ingredient->unit }}</div>
                </div>
                <div style="font-weight: 700; color: {{ (float) $ingredient->current_stock <= 0 ? '#b91c1c' : $primaryColor }};">
                    {{ $ingredient->current_stock }} {{ $ingredient->unit }}
                </div>
            </div>
        @endforeach
    </div>

    <p style="margin: 0 0 20px; font-size: 14px; color: #555;">You can update stock levels from Inventory → Ingredients in your dashboard.</p>
@endsection
