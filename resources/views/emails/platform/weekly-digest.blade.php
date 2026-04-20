@extends('emails.layout')

@php
/** @var string $storeName */
/** @var string $primaryColor */
/** @var string $secondaryColor */
/** @var string $storeEmail */
/** @var string $storePhone */
/** @var string $storeAddress */
/** @var string|null $logoUrl */
/** @var array<string, mixed> $stats */
/** @var \Illuminate\Database\Eloquent\Collection<int, \App\Models\Orders\OrderItem> $topProducts */
/** @var \Illuminate\Support\Collection<int, array{name: string, days_since_last_order: ?int}> $atRiskCustomers */
/** @var int $upcomingCount */
/** @var string $adminUrl */
@endphp


@section('title', 'Weekly Digest')

@section('content')
    <h2 style="margin: 0 0 5px; font-size: 22px; color: {{ $secondaryColor }};">📊 Your Weekly Digest</h2>
    <p style="margin: 0 0 25px; color: #6b5c4d; font-size: 14px;">Here's how last week went at {{ $storeName }}.</p>

    {{-- This Week's Numbers --}}
    <div style="background-color: #fef9ef; border-radius: 10px; padding: 20px; margin-bottom: 25px;">
        <h3 style="margin: 0 0 15px; font-size: 16px; color: {{ $primaryColor }};">This Week's Numbers</h3>
        <table style="width: 100%; border-collapse: collapse;">
            <tr>
                <td style="text-align: center; padding: 10px;">
                    <div style="font-size: 28px; font-weight: 700; color: {{ $secondaryColor }};">{{ $stats['total_orders'] }}</div>
                    <div style="font-size: 12px; color: #6b5c4d; text-transform: uppercase;">Orders</div>
                </td>
                <td style="text-align: center; padding: 10px;">
                    <div style="font-size: 28px; font-weight: 700; color: {{ $secondaryColor }};">${{ $stats['total_revenue'] }}</div>
                    <div style="font-size: 12px; color: #6b5c4d; text-transform: uppercase;">Revenue</div>
                </td>
            </tr>
            <tr>
                <td style="text-align: center; padding: 10px;">
                    <div style="font-size: 28px; font-weight: 700; color: {{ $secondaryColor }};">{{ $stats['new_customers'] }}</div>
                    <div style="font-size: 12px; color: #6b5c4d; text-transform: uppercase;">New Customers</div>
                </td>
                <td style="text-align: center; padding: 10px;">
                    <div style="font-size: 28px; font-weight: 700; color: {{ $secondaryColor }};">${{ $stats['avg_order_value'] }}</div>
                    <div style="font-size: 12px; color: #6b5c4d; text-transform: uppercase;">Avg Order</div>
                </td>
            </tr>
        </table>
    </div>

    {{-- Top Products --}}
    @if ($topProducts->isNotEmpty())
        <div style="margin-bottom: 25px;">
            <h3 style="margin: 0 0 12px; font-size: 16px; color: {{ $primaryColor }};">🏆 Top Products</h3>
            <table style="width: 100%; border-collapse: collapse;">
                @foreach ($topProducts as $i => $item)
                    <tr style="border-bottom: 1px solid #f0e6d6;">
                        <td style="padding: 8px 5px; font-size: 14px;">
                            <span style="color: {{ $primaryColor }}; font-weight: 700;">{{ $i + 1 }}.</span>
                            {{ $item->product->name ?? 'Unknown' }}
                        </td>
                        <td style="padding: 8px 5px; text-align: right; font-size: 14px; color: #6b5c4d;">
                            {{ (int) $item->total_qty }} sold
                        </td>
                    </tr>
                @endforeach
            </table>
        </div>
    @endif

    {{-- At-Risk Customers --}}
    @if ($atRiskCustomers->isNotEmpty())
        <div style="margin-bottom: 25px;">
            <h3 style="margin: 0 0 12px; font-size: 16px; color: {{ $primaryColor }};">⚠️ At-Risk Customers</h3>
            <p style="margin: 0 0 10px; font-size: 13px; color: #6b5c4d;">Haven't ordered in 30+ days — consider reaching out!</p>
            <table style="width: 100%; border-collapse: collapse;">
                @foreach ($atRiskCustomers as $customer)
                    <tr style="border-bottom: 1px solid #f0e6d6;">
                        <td style="padding: 8px 5px; font-size: 14px;">{{ $customer['name'] }}</td>
                        <td style="padding: 8px 5px; text-align: right; font-size: 13px; color: #6b5c4d;">
                            {{ $customer['days_since_last_order'] }}d ago
                        </td>
                    </tr>
                @endforeach
            </table>
        </div>
    @endif

    {{-- Upcoming --}}
    <div style="background-color: #fef9ef; border-radius: 10px; padding: 15px 20px; margin-bottom: 25px;">
        <h3 style="margin: 0 0 5px; font-size: 16px; color: {{ $primaryColor }};">📅 Coming Up This Week</h3>
        <p style="margin: 0; font-size: 14px; color: {{ $secondaryColor }};">
            <strong>{{ $upcomingCount }}</strong> {{ Str::plural('order', $upcomingCount) }} scheduled
        </p>
    </div>

    {{-- Quick Actions --}}
    <div style="text-align: center; margin-top: 25px;">
        <h3 style="margin: 0 0 15px; font-size: 16px; color: {{ $primaryColor }};">Quick Actions</h3>
        <a href="{{ $adminUrl }}/orders/create" style="display: inline-block; background-color: {{ $primaryColor }}; color: #ffffff; text-decoration: none; padding: 10px 20px; border-radius: 8px; font-weight: 600; font-size: 14px; margin: 5px;">+ New Order</a>
        <a href="{{ $adminUrl }}/orders" style="display: inline-block; background-color: {{ $secondaryColor }}; color: #fef9ef; text-decoration: none; padding: 10px 20px; border-radius: 8px; font-weight: 600; font-size: 14px; margin: 5px;">View Orders</a>
        <a href="{{ $adminUrl }}/customers" style="display: inline-block; background-color: #6b5c4d; color: #ffffff; text-decoration: none; padding: 10px 20px; border-radius: 8px; font-weight: 600; font-size: 14px; margin: 5px;">Customers</a>
    </div>
@endsection
