<?php

namespace App\Actions\Customers;

use App\Models\Customers\CateringInquiry;
use App\Models\Customers\Customer;
use App\Models\Customers\CustomerFavorite;
use App\Models\Customers\CustomerPhoto;
use App\Models\Customers\WaitlistEntry;
use App\Models\Engagement\CustomerCampaignLog;
use App\Models\Engagement\Review;
use App\Models\Engagement\SurveyResponse;
use App\Models\Inventory\ProductWaitlist;
use App\Models\Operations\ActivityLog;
use App\Models\Orders\Cart;
use App\Models\Orders\Order;
use App\Services\Audit\ActivityLogRedactor;
use Illuminate\Database\Eloquent\Collection as EloquentCollection;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

/**
 * Removes what the bakery holds about a customer outside their own row: the
 * records that carry only their email address (no foreign key ties them to the
 * customer), their saved carts, and their values in the activity log.
 *
 * Favorites, campaign logs, waitlists, photos, survey responses and carts are
 * deleted, and a survey response filed against one of their orders keeps its row
 * without the name, email and answers. A review is kept, without the reviewer's
 * name, email or photo, only if it was approved; otherwise it is deleted. A
 * catering inquiry is kept with its contact fields anonymised and its free text
 * (details, dietary requirements, venue address, notes) removed. Safe to run again.
 */
class EraseCustomerPersonalRecords
{
    /** The columns of the customer row that identify a person. */
    private const array CUSTOMER_KEYS = ['name', 'email', 'phone', 'address', 'city', 'state', 'zip', 'notes', 'birthday'];

    /** Stands in for a required text column that has to be emptied. */
    private const string REMOVED = '[removed]';

    private const array REVIEW_KEYS = ['customer_name', 'customer_email', 'photo_path'];

    private const array INQUIRY_KEYS = ['customer_name', 'customer_email', 'customer_phone', 'details', 'dietary_requirements', 'venue_address', 'notes'];

    public function __construct(private readonly ActivityLogRedactor $redactor) {}

    public function __invoke(int $customerId, string $email): void
    {
        DB::transaction(function () use ($customerId, $email): void {
            $this->deleteEmailKeyedRecords($email);
            $this->blankSurveyResponsesOnTheirOrders($customerId);
            $this->deleteCarts($customerId, $email);
            $this->eraseReviews($customerId, $email);
            $this->anonymiseInquiries($customerId, $email);
            $this->redactActivityLog(Customer::class, [$customerId], self::CUSTOMER_KEYS);
        });
    }

    private function deleteEmailKeyedRecords(string $email): void
    {
        CustomerFavorite::query()->where('customer_email', $email)->delete();
        CustomerCampaignLog::query()->where('customer_email', $email)->delete();
        ProductWaitlist::query()->where('customer_email', $email)->delete();
        WaitlistEntry::query()->where('customer_email', $email)->delete();
        SurveyResponse::query()->where('customer_email', $email)->delete();

        $photos = CustomerPhoto::query()->where('customer_email', $email);
        Storage::disk('public')->delete($photos->pluck('photo_path')->all());
        $photos->delete();
    }

    /**
     * A survey response filed against one of the customer's orders but not under
     * their email survives the delete above; keep the row, drop who and what.
     */
    private function blankSurveyResponsesOnTheirOrders(int $customerId): void
    {
        SurveyResponse::query()
            ->whereIn('order_id', Order::query()->where('customer_id', $customerId)->select('id'))
            ->update([
                'customer_name' => null,
                'customer_email' => null,
                'answers' => '[]',
            ]);
    }

    private function deleteCarts(int $customerId, string $email): void
    {
        Cart::query()
            ->where('customer_id', $customerId)
            ->orWhere('customer_email', $email)
            ->delete();
    }

    private function eraseReviews(int $customerId, string $email): void
    {
        $reviews = Review::query()->where('customer_email', $email);

        $ids = $reviews->pluck('id')->all();
        Storage::disk('public')->delete($reviews->pluck('photo_path')->filter()->all());

        Review::query()
            ->where('customer_email', $email)
            ->where('is_approved', false)
            ->delete();

        Review::query()
            ->where('customer_email', $email)
            ->update([
                'customer_name' => AnonymiseCustomer::placeholderName($customerId),
                'customer_email' => AnonymiseCustomer::placeholderEmail($customerId),
                'photo_path' => null,
            ]);

        $this->redactActivityLog(Review::class, $ids, self::REVIEW_KEYS);
    }

    private function anonymiseInquiries(int $customerId, string $email): void
    {
        $inquiries = CateringInquiry::query()->where('customer_email', $email);

        $ids = $inquiries->pluck('id')->all();

        $inquiries->update([
            'customer_name' => AnonymiseCustomer::placeholderName($customerId),
            'customer_email' => AnonymiseCustomer::placeholderEmail($customerId),
            'customer_phone' => null,
            'details' => self::REMOVED,
            'dietary_requirements' => null,
            'venue_address' => null,
            'notes' => null,
        ]);

        $this->redactActivityLog(CateringInquiry::class, $ids, self::INQUIRY_KEYS);
    }

    /**
     * @param  class-string<Model>  $modelType
     * @param  array<array-key, mixed>  $modelIds
     * @param  list<string>  $keys
     */
    private function redactActivityLog(string $modelType, array $modelIds, array $keys): void
    {
        ActivityLog::query()
            ->where('model_type', $modelType)
            ->whereIn('model_id', $modelIds)
            ->whereNotNull('properties')
            ->chunkById(200, function (EloquentCollection $logs) use ($keys): void {
                foreach ($logs as $log) {
                    if ($log->properties === null) {
                        continue;
                    }

                    $log->update(['properties' => $this->redactor->redactKeys($log->properties, $keys)]);
                }
            });
    }
}
