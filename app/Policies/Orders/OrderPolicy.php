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
}
