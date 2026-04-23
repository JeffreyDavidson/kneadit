<?php

namespace App\Actions\Customers;

use App\Events\Customers\LowReviewReceived;
use App\Models\Engagement\Review;
use App\Models\Orders\Order;
use App\Services\Settings\TenantSettings;
use Illuminate\Http\UploadedFile;

class CreateReview
{
    public function __construct(
        private TenantSettings $settings,
    ) {}

    public function __invoke(Order $order, int $rating, ?string $comment = null, ?UploadedFile $photo = null): Review
    {
        $photoPath = $photo?->store('review-photos', 'public');

        $review = Review::query()->create([
            'customer_name' => $order->customer->name ?? 'Customer',
            'customer_email' => $order->customer->email ?? '',
            'order_id' => $order->id,
            'rating' => $rating,
            'comment' => $comment,
            'photo_path' => $photoPath,
            'is_approved' => false,
        ]);

        $threshold = $this->settings->engagement->lowReviewAlertThreshold;
        if ($threshold > 0 && $rating <= $threshold) {
            event(new LowReviewReceived($review));
        }

        return $review;
    }
}
