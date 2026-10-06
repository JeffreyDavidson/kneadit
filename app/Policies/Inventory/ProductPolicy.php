<?php

declare(strict_types=1);

namespace App\Policies\Inventory;

use App\Enums\Staff\UserRole;
use App\Models\Inventory\Product;
use App\Models\Staff\User;
use App\Policies\Platform\RolePolicy;
use Illuminate\Auth\Access\Response;

class ProductPolicy extends RolePolicy
{
    #[\Override]
    protected UserRole $minimumRole = UserRole::Staff;

    /**
     * A product with past orders can't be deleted, so those orders keep their
     * lines; deactivating it hides it from the storefront instead. A review keeps
     * no copy of the product name, so a product with reviews is protected too.
     */
    #[\Override]
    public function delete(User $user, mixed $model): bool|Response
    {
        if (! $user->role->meetsRequirement($this->minimumRole)) {
            return false;
        }

        if (! $model instanceof Product) {
            return false;
        }

        if (! $model->exists) {
            return true;
        }

        if ($model->orderItems()->exists()) {
            return Response::deny("{$model->name} is on past orders, so it can't be deleted. Deactivate it instead to take it off the storefront.");
        }

        if ($model->reviews()->exists()) {
            return Response::deny("{$model->name} has customer reviews, so it can't be deleted. Deactivate it instead, or remove its reviews first.");
        }

        return true;
    }
}
