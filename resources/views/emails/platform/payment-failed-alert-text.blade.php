@php
/** @var \App\Models\Staff\User $user */
/** @var \App\Models\Platform\Tenant|null $tenant */
/** @var float $amount */
@endphp

Payment failed for {{ $user->name }} ({{ $user->email }})
@if($tenant)
Tenant: {{ $tenant->store_name }} ({{ $tenant->id }})
@endif
Amount: {{ \Illuminate\Support\Number::currency($amount) }}
