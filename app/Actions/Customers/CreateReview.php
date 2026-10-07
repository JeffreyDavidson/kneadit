<?php

namespace App\Actions\Customers;

use App\Events\Customers\LowReviewReceived;
use App\Models\Engagement\Review;
use App\Models\Orders\Order;
use App\Services\Settings\TenantSettings;
use App\Services\Support\PrivateImageStore;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

/**
 * Records a customer's review of an order. An order has one review: sending the
 * form again (a second tab, a refresh, a retried request) updates that review
 * rather than adding another, and puts it back in the moderation queue.
 */
class CreateReview
{
    public function __construct(
        private readonly TenantSettings $settings,
        private readonly PrivateImageStore $images,
    ) {}

    public function __invoke(Order $order, int $rating, ?string $comment = null, ?UploadedFile $photo = null): Review
    {
        $photoPath = $photo instanceof UploadedFile
            ? $this->images->store($photo, 'review-photos')
            : null;

        [$review, $previousRating, $previousPhotoPath] = DB::transaction(function () use ($order, $rating, $comment, $photoPath): array {
            // Lock the order so two simultaneous submissions queue up and the second one sees the first.
            Order::query()->whereKey($order->id)->lockForUpdate()->first();

            $review = Review::query()->where('order_id', $order->id)->first();
            $previousRating = $review?->rating;
            $previousPhotoPath = $review?->photo_path;

            $review ??= new Review(['order_id' => $order->id]);
            $review->fill([
                'customer_name' => $order->customer->name ?? 'Customer',
                'customer_email' => $order->customer->email ?? '',
                'rating' => $rating,
                'comment' => $comment,
                'photo_path' => $photoPath ?? $previousPhotoPath,
                'is_approved' => false,
            ])->save();

            return [$review, $previousRating, $previousPhotoPath];
        });

        if ($photoPath !== null && $previousPhotoPath !== null) {
            Storage::disk('public')->delete($previousPhotoPath);
        }

        if ($this->isNewlyLow($rating, $previousRating)) {
            event(new LowReviewReceived($review));
        }

        return $review;
    }

    /**
     * The owner is alerted once per review: when it arrives low, or when an edit drops it to low.
     */
    private function isNewlyLow(int $rating, ?int $previousRating): bool
    {
        $threshold = $this->settings->engagement->lowReviewAlertThreshold;

        if ($threshold <= 0 || $rating > $threshold) {
            return false;
        }

        return $previousRating === null || $previousRating > $threshold;
    }
}
