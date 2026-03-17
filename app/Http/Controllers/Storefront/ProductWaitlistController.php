<?php

namespace App\Http\Controllers\Storefront;

use App\Http\Controllers\Controller;
use App\Models\ProductWaitlist;
use Illuminate\Http\Request;

class ProductWaitlistController extends Controller
{
    /**
     * Join the waitlist for an out-of-season product.
     */
    public function __invoke(Request $request)
    {
        $request->validate([
            'product_id' => ['required', 'exists:products,id'],
            'customer_email' => ['required', 'email', 'max:255'],
            'customer_name' => ['nullable', 'string', 'max:255'],
        ]);

        ProductWaitlist::updateOrCreate(
            [
                'product_id' => $request->product_id,
                'customer_email' => $request->customer_email,
            ],
            [
                'customer_name' => $request->customer_name,
                'notified_at' => null,
                'created_at' => now(),
            ]
        );

        if ($request->wantsJson()) {
            return response()->json(['message' => 'You\'ll be notified when this item is available!']);
        }

        return back()->with('waitlist_success', 'You\'ll be notified when this item is available!');
    }
}
