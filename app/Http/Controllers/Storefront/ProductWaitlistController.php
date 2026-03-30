<?php

namespace App\Http\Controllers\Storefront;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreProductWaitlistRequest;
use App\Models\ProductWaitlist;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;

class ProductWaitlistController extends Controller
{
    /**
     * Join the waitlist for an out-of-season product.
     */
    public function __invoke(StoreProductWaitlistRequest $request): JsonResponse|RedirectResponse
    {
        $validated = $request->validated();

        ProductWaitlist::query()->updateOrCreate([
            'product_id' => $validated['product_id'],
            'customer_email' => $validated['customer_email'],
        ], [
            'customer_name' => $validated['customer_name'] ?? null,
            'notified_at' => null,
            'created_at' => now(),
        ]);

        if ($request->wantsJson()) {
            return response()->json([
                'message' => 'You\'ll be notified when this item is available!',
            ]);
        }

        return back()->with('waitlist_success', 'You\'ll be notified when this item is available!');
    }
}
