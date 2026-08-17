<?php

namespace App\Http\Controllers\Storefront;

use App\Actions\Customers\JoinProductWaitlist;
use App\Http\Controllers\Controller;
use App\Http\Requests\Storefront\StoreProductWaitlistRequest;
use App\Http\Responses\ApiResponse;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;

class ProductWaitlistController extends Controller
{
    public function __invoke(StoreProductWaitlistRequest $request, JoinProductWaitlist $joinWaitlist): JsonResponse|RedirectResponse
    {
        $joinWaitlist(
            productId: $request->integer('product_id'),
            customerEmail: $request->string('customer_email')->toString(),
            customerName: $request->filled('customer_name') ? $request->string('customer_name')->toString() : null,
        );

        if ($request->wantsJson()) {
            return ApiResponse::success(message: 'You\'ll be notified when this item is available!');
        }

        return back()->with('waitlist_success', 'You\'ll be notified when this item is available!');
    }
}
