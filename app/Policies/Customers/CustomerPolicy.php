<?php

declare(strict_types=1);

namespace App\Policies\Customers;

use App\Actions\Customers\AnonymiseCustomer;
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

    /**
     * Anonymising keeps the customer's orders, so it is allowed for a customer
     * who has them, but only once.
     */
    public function anonymise(User $user, Customer $customer): bool
    {
        if (! $user->role->meetsRequirement($this->minimumRole)) {
            return false;
        }

        return ! resolve(AnonymiseCustomer::class)->isAnonymised($customer);
    }
}
