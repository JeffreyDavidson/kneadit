<?php

namespace App\Http\Controllers\Storefront;

use App\Actions\Customers\JoinProductWaitlist;
use App\Http\Controllers\Controller;
use App\Http\Requests\StoreProductWaitlistRequest;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;

class ProductWaitlistController extends Controller
{
    public function __invoke(StoreProductWaitlistRequest $request, JoinProductWaitlist $joinWaitlist): JsonResponse|RedirectResponse
    {
        $joinWaitlist(
            productId: $request->validated('product_id'),
            customerEmail: $request->validated('customer_email'),
            customerName: $request->validated('customer_name'),
        );

        if ($request->wantsJson()) {
            return response()->json([
                'message' => 'You\'ll be notified when this item is available!',
            ]);
        }

        return back()->with('waitlist_success', 'You\'ll be notified when this item is available!');
    }
}
