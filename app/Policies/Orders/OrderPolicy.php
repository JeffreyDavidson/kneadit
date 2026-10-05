<?php

declare(strict_types=1);

namespace App\Policies\Orders;

use App\Enums\Staff\UserRole;
use App\Models\Orders\Order;
use App\Models\Staff\User;
use App\Policies\Platform\RolePolicy;
use App\Services\Orders\OrderDeletionGuard;

class OrderPolicy extends RolePolicy
{
    #[\Override]
    protected UserRole $minimumRole = UserRole::Staff;

    /**
     * Staff can cancel orders but only managers and above can delete them, and
     * only while nothing financial has happened on the order.
     */
    #[\Override]
    public function delete(User $user, mixed $model): bool
    {
        if (! $user->role->meetsRequirement(UserRole::Manager)) {
            return false;
        }

        return $model instanceof Order && resolve(OrderDeletionGuard::class)->canDelete($model);
    }

    /**
     * Staff can cancel an order only while no customer money is held for it.
     * Cancelling a paid or part-paid order (which refunds it) is for managers
     * and above.
     */
    public function cancel(User $user, Order $order): bool
    {
        if ($user->role->meetsRequirement(UserRole::Manager)) {
            return true;
        }

        return ! $order->payment_status->holdsPayment();
    }

    /** Refunding money back to a customer is for managers and above. */
    public function refund(User $user, Order $order): bool
    {
        return $user->role->meetsRequirement(UserRole::Manager);
    }
}
