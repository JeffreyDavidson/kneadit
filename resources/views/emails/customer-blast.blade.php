@extends('emails.layout')

@section('content')
    <div style="color: {{ $secondaryColor }}; font-size: 16px; line-height: 1.6;">
        {!! $body !!}
    </div>

    @if(!empty($unsubscribeNote))
    <p style="margin: 20px 0 0; color: #a89585; font-size: 11px; text-align: center;">
        Reply STOP to unsubscribe
    </p>
    @endif
@endsection
