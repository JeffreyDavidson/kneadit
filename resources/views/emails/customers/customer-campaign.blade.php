@extends('emails.layout')

@php
/** @var App\Models\Engagement\CustomerCampaign $campaign */
/** @var string|null $trackingPixelUrl */
/** @var string $storeName */
@endphp

@section('title', $campaign->subject)

@section('content')
    {!! nl2br(e($campaign->body)) !!}

    @if ($trackingPixelUrl)
        <img src="{{ $trackingPixelUrl }}" width="1" height="1" alt="" style="display:block; width:1px; height:1px; border:0;" />
    @endif
@endsection
