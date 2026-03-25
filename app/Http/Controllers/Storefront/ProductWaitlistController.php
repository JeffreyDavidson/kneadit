<?php

namespace App\Http\Controllers\Storefront;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreProductWaitlistRequest;
use App\Models\ProductWaitlist;
use Illuminate\Http\JsonResponse;

class ProductWaitlistController extends Controller
{
    /**
     * Join the waitlist for an out-of-season product.
     */
    public function __invoke(StoreProductWaitlistRequest $request): JsonResponse
    {

        ProductWaitlist::query()->updateOrCreate([
            'product_id' => $request->product_id,
            'customer_email' => $request->customer_email,
        ], [
            'customer_name' => $request->customer_name,
            'notified_at' => null,
            'created_at' => now(),
        ]);

        if ($request->wantsJson()) {
            return response()->json(['message' => 'You\'ll be notified when this item is available!']);
        }

        return back()->with('waitlist_success', 'You\'ll be notified when this item is available!');
    }
}
