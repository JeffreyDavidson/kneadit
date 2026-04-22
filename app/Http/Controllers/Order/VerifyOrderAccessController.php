<?php

namespace App\Http\Controllers\Order;

use App\Http\Controllers\Controller;
use App\Http\Requests\Order\VerifyOrderAccessRequest;
use App\Models\Orders\Order;
use App\Services\Orders\OrderAccessGuard;
use App\Services\Settings\TenantSettings;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;

class VerifyOrderAccessController extends Controller
{
    public function show(Order $order, TenantSettings $settings): View
    {
        return view('storefront.order-verify', [
            'order' => $order,
            'settings' => $settings,
        ]);
    }

    public function store(Order $order, VerifyOrderAccessRequest $request): RedirectResponse
    {
        $submitted = strtolower(trim($request->string('email')->value()));
        $expected = strtolower(trim((string) $order->customer?->email));

        if ($expected === '' || $submitted !== $expected) {
            return back()
                ->withInput()
                ->withErrors(['email' => 'That email does not match the order.']);
        }

        OrderAccessGuard::grant($order);

        return redirect()->intended(
            route('order.confirmation', ['order' => $order->order_number]),
        );
    }
}
