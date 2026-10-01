@extends('emails.layout')

@php
/** @var string $storeName */
/** @var string $primaryColor */
/** @var string $secondaryColor */
/** @var string $storeEmail */
/** @var string $storePhone */
/** @var string $storeAddress */
/** @var string|null $logoUrl */
@endphp


@section('content')
    <div style="color: {{ $secondaryColor }}; font-size: 16px; line-height: 1.6;">
        {!! clean($body) !!}
    </div>
@endsection
