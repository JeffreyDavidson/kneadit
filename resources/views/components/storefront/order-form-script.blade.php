{{-- The order page owns the Alpine state, but the layout stays readable through an explicit component. --}}
@props(['settings', 'earliestDeliveryDate', 'maxQuantity', 'quantityLimitMessage', 'hydratedCartItems' => [], 'hydratedCartName' => null, 'hydratedCartEmail' => null, 'removedItemNames' => []])

@include('shared.storefront.order-form-script', [
    'settings' => $settings,
    'hydratedCartItems' => $hydratedCartItems,
    'hydratedCartName' => $hydratedCartName,
    'hydratedCartEmail' => $hydratedCartEmail,
    'removedItemNames' => $removedItemNames,
    'maxQuantity' => $maxQuantity,
    'quantityLimitMessage' => $quantityLimitMessage,
    'earliestDeliveryDate' => $earliestDeliveryDate,
])
