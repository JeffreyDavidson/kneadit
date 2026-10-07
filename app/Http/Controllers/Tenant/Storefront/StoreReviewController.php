<?php

namespace App\Http\Controllers\Tenant\Storefront;

use App\Actions\Customers\CreateReview;
use App\Http\Controllers\Controller;
use App\Http\Requests\Storefront\StoreReviewRequest;
use App\Models\Orders\Order;
use Illuminate\Http\RedirectResponse;

class StoreReviewController extends Controller
{
    public function __invoke(Order $order, StoreReviewRequest $request, CreateReview $createReview): RedirectResponse
    {
        $createReview(
            order: $order,
            rating: $request->integer('rating'),
            comment: $request->filled('comment') ? $request->string('comment')->toString() : null,
            photo: $request->file('photo'),
        );

        return to_route('storefront.reviewSubmitted', $order);
    }
}
