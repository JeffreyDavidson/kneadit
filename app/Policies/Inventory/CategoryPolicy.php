<?php

declare(strict_types=1);

namespace App\Policies\Inventory;

use App\Models\Inventory\Category;
use App\Models\Staff\User;
use App\Policies\Platform\RolePolicy;
use Illuminate\Auth\Access\Response;

class CategoryPolicy extends RolePolicy
{
    /**
     * A category that still has products can't be deleted; deactivating it
     * hides it instead, and its products can be moved to another category.
     */
    #[\Override]
    public function delete(User $user, mixed $model): bool|Response
    {
        if (! $user->role->meetsRequirement($this->minimumRole)) {
            return false;
        }

        if (! $model instanceof Category) {
            return false;
        }

        if (! $model->exists) {
            return true;
        }

        if ($model->products()->exists()) {
            return Response::deny("{$model->name} still has products, so it can't be deleted. Deactivate it instead, or move its products to another category first.");
        }

        return true;
    }
}
