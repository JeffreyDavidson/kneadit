<?php

declare(strict_types=1);

namespace App\Policies\Inventory;

use App\Models\Inventory\Ingredient;
use App\Models\Staff\User;
use App\Policies\Platform\RolePolicy;

class IngredientPolicy extends RolePolicy
{
    public function delete(User $user, mixed $model): bool
    {
        if (! parent::delete($user, $model)) {
            return false;
        }

        if (! $model instanceof Ingredient) {
            return false;
        }

        if (! $model->exists) {
            return true;
        }

        return ! $model->stockAdjustments()->exists()
            && ! $model->recipes()->exists();
    }
}
