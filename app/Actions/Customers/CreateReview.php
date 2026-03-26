<?php

namespace App\Actions\Customers;

use App\Models\Order;
use App\Models\Review;
use Illuminate\Http\UploadedFile;

class CreateReview
{
    public function __invoke(Order $order, int $rating, ?string $comment = null, ?UploadedFile $photo = null): Review
    {
        $photoPath = $photo?->store('review-photos', 'public');

        return Review::query()->create([
            'customer_name' => $order->customer->name ?? 'Customer',
            'customer_email' => $order->customer->email ?? '',
            'order_id' => $order->id,
            'rating' => $rating,
            'comment' => $comment,
            'photo_path' => $photoPath,
            'is_approved' => false,
        ]);
    }
}
