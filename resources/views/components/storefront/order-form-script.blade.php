{{-- The order page owns the Alpine state, but the layout stays readable through an explicit component. --}}
@props(['settings', 'hydratedCartItems' => [], 'hydratedCartName' => null, 'hydratedCartEmail' => null])

@include('shared.storefront.order-form-script', [
    'settings' => $settings,
    'hydratedCartItems' => $hydratedCartItems,
    'hydratedCartName' => $hydratedCartName,
    'hydratedCartEmail' => $hydratedCartEmail,
])
