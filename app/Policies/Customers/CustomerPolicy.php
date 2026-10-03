<?php

declare(strict_types=1);

namespace App\Policies\Customers;

use App\Models\Customers\Customer;
use App\Models\Staff\User;
use App\Policies\Platform\RolePolicy;

class CustomerPolicy extends RolePolicy
{
    /**
     * Deleting a customer cascades to their orders, so a customer with any
     * order history can't be deleted.
     */
    #[\Override]
    public function delete(User $user, mixed $model): bool
    {
        if (! parent::delete($user, $model)) {
            return false;
        }

        return $model instanceof Customer && ! $model->orders()->exists();
    }
}
