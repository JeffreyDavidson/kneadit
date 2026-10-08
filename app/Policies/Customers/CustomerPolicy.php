<?php

declare(strict_types=1);

namespace App\Policies\Customers;

use App\Actions\Customers\AnonymiseCustomer;
use App\Models\Customers\Customer;
use App\Models\Staff\User;
use App\Policies\Platform\RolePolicy;
use Illuminate\Auth\Access\Response;

class CustomerPolicy extends RolePolicy
{
    /**
     * Deleting a customer cascades to their orders, so a customer with any
     * order history can't be deleted.
     */
    #[\Override]
    public function delete(User $user, mixed $model): bool|Response
    {
        if (! parent::delete($user, $model)) {
            return false;
        }

        if (! $model instanceof Customer) {
            return false;
        }

        if ($model->orders()->exists()) {
            return Response::deny("{$model->name} has orders, so they can't be deleted. Anonymise them instead to remove their personal details and keep the order history.");
        }

        return true;
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
