<?php

declare(strict_types=1);

namespace App\Builders\Customers;

use App\Enums\Customers\CustomerReferralStatus;
use App\Models\Customers\CustomerReferral;
use App\Models\Orders\Order;
use Illuminate\Database\Eloquent\Builder;

/** @extends Builder<CustomerReferral> */
class CustomerReferralQueryBuilder extends Builder
{
    public function pending(): static
    {
        $this->where('status', CustomerReferralStatus::Pending);

        return $this;
    }

    public function notCancelled(): static
    {
        $this->where('status', '!=', CustomerReferralStatus::Cancelled);

        return $this;
    }

    public function forOrder(Order $order): static
    {
        $this->where('order_id', $order->id);

        return $this;
    }

    public function forReferredCustomer(int $customerId): static
    {
        $this->where('referred_customer_id', $customerId);

        return $this;
    }
}
