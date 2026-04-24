@extends('emails.layout')

@php
/** @var App\Models\Customers\Customer $customer */
/** @var string $body */
/** @var string $storeName */
@endphp

@section('title', $customer->name ? "Hi {$customer->name}" : 'A note from ' . $storeName)

@section('content')
    @if ($customer->name)
        <p>Hi {{ $customer->name }},</p>
    @endif

    {!! nl2br(e($body)) !!}
@endsection
