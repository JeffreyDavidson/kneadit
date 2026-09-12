<?php

namespace App\Actions\Inventory;

use App\DataTransferObjects\Settings\EngagementSettings;
use App\Mail\Customers\ProductAvailableMail;
use App\Models\Inventory\Product;
use App\Models\Inventory\ProductWaitlist;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Mail;

class NotifyProductWaitlist
{
    public function __construct(
        private EngagementSettings $engagementSettings,
    ) {}

    /**
     * Queue "product is back" emails for every waitlist entry on the product
     * that hasn't been notified yet, and mark them notified atomically.
     *
     * Returns the number of notifications queued. Returns 0 (and does nothing)
     * when the tenant has disabled the product_available email toggle.
     */
    public function __invoke(Product $product): int
    {
        if (! $this->engagementSettings->emailProductAvailableEnabled) {
            return 0;
        }

        return DB::transaction(function () use ($product): int {
            $entries = ProductWaitlist::query()
                ->where('product_id', $product->id)
                ->whereNull('notified_at')
                ->lockForUpdate()
                ->get();

            if ($entries->isEmpty()) {
                return 0;
            }

            foreach ($entries as $entry) {
                Mail::to($entry->customer_email)->queue(
                    new ProductAvailableMail($product, (string) ($entry->customer_name ?? '')),
                );
            }

            ProductWaitlist::query()
                ->whereKey($entries->pluck('id')->all())
                ->update(['notified_at' => now()]);

            return $entries->count();
        });
    }
}
