@php
/** @var string $alertMessage */
/** @var string $alertSubject */
@endphp
@extends('emails.platform.layout')

@section('title', $alertSubject)

@section('content')
    <p style="margin: 0; white-space: pre-wrap;">{{ $alertMessage }}</p>
@endsection
