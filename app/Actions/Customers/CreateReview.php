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

        [$review, $previousPhotoPath, $alertLow] = DB::transaction(function () use ($order, $rating, $comment, $photoPath): array {
            // Lock the order so two simultaneous submissions queue up and the second one sees the first.
            Order::query()->whereKey($order->id)->lockForUpdate()->first();

            $review = Review::query()->where('order_id', $order->id)->first();
            $previousPhotoPath = $review?->photo_path;
            $alertLow = $this->shouldAlertLow($rating, $review);

            $review ??= new Review(['order_id' => $order->id]);
            $review->fill([
                'customer_name' => $order->customer->name ?? 'Customer',
                'customer_email' => $order->customer->email ?? '',
                'rating' => $rating,
                'comment' => $comment,
                'photo_path' => $photoPath ?? $previousPhotoPath,
                'is_approved' => false,
                'low_rating_alerted_at' => $alertLow ? now() : $review->low_rating_alerted_at,
            ])->save();

            return [$review, $previousPhotoPath, $alertLow];
        });

        if ($photoPath !== null && $previousPhotoPath !== null) {
            Storage::disk('public')->delete($previousPhotoPath);
        }

        if ($alertLow) {
            event(new LowReviewReceived($review));
        }

        return $review;
    }

    /**
     * The owner is alerted once per review: when it arrives low, or when an edit drops it to low.
     * low_rating_alerted_at remembers the alert, so raising the rating and lowering it again
     * doesn't alert twice. Reviews from before that column have no record, so an edit from
     * low to low is still never an alert.
     */
    private function shouldAlertLow(int $rating, ?Review $existing): bool
    {
        $threshold = $this->settings->engagement->lowReviewAlertThreshold;

        if ($threshold <= 0 || $rating > $threshold) {
            return false;
        }

        if (! $existing instanceof Review) {
            return true;
        }

        return $existing->low_rating_alerted_at === null && $existing->rating > $threshold;
    }
}
