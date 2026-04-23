@extends('emails.layout')

@php
/** @var App\Models\Engagement\CustomerCampaign $campaign */
/** @var string $storeName */
@endphp

@section('title', $campaign->subject)

@section('content')
    {!! nl2br(e($campaign->body)) !!}
@endsection
