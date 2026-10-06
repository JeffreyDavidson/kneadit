@php
/** @var array<int, array{id: string, name: string, email: string}> $unapproved */
@endphp
@extends('emails.platform.layout')

@section('title', 'Free-forever grant integrity alert')

@section('content')
    <h2 style="margin: 0 0 16px; color: #1c1410; font-size: 22px;">🚨 Free-forever grant integrity alert</h2>

    <p style="margin: 0 0 16px;">
        The following tenant(s) are marked <code>free_forever = true</code> but have no active grant recorded in
        <code>free_forever_grants</code>. Someone may have bypassed the admin UI (direct database write, compromised
        account, or overlooked migration). Investigate before dismissing.
    </p>

    <table style="width: 100%; border-collapse: collapse; margin: 16px 0;">
        <thead>
            <tr>
                <th style="text-align: left; border-bottom: 1px solid #e8d0b0; padding: 8px 12px; font-size: 14px;">Tenant ID</th>
                <th style="text-align: left; border-bottom: 1px solid #e8d0b0; padding: 8px 12px; font-size: 14px;">Name</th>
                <th style="text-align: left; border-bottom: 1px solid #e8d0b0; padding: 8px 12px; font-size: 14px;">Email</th>
            </tr>
        </thead>
        <tbody>
            @foreach ($unapproved as $tenant)
                <tr>
                    <td style="padding: 8px 12px; font-family: monospace; font-size: 14px;">{{ $tenant['id'] }}</td>
                    <td style="padding: 8px 12px; font-size: 14px;">{{ $tenant['name'] }}</td>
                    <td style="padding: 8px 12px; font-size: 14px;">{{ $tenant['email'] }}</td>
                </tr>
            @endforeach
        </tbody>
    </table>

    <p style="margin: 0; color: #6b4c3b; font-size: 14px;">
        Next step: open the central admin → Tenants → check the activity log for each unexpected tenant. If legitimate,
        use the Grant Free Forever bulk action (which writes an approved grant row) to retroactively approve it.
    </p>
@endsection
