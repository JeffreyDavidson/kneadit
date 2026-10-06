@php
/** @var string $body */
/** @var string $emailSubject */
@endphp
@extends('emails.platform.layout')

@section('title', $emailSubject)

@section('content')
    <div style="margin: 0 0 24px;">{!! clean($body) !!}</div>

    <p style="margin: 0; color: #6b4c3b; font-size: 14px;">You're receiving this as the owner of a KneadIt bakery.</p>
@endsection
