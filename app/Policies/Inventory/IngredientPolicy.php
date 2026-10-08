<?php

declare(strict_types=1);

namespace App\Policies\Inventory;

use App\Models\Inventory\Ingredient;
use App\Models\Staff\User;
use App\Policies\Platform\RolePolicy;
use Illuminate\Auth\Access\Response;

class IngredientPolicy extends RolePolicy
{
    #[\Override]
    public function delete(User $user, mixed $model): bool|Response
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

        if ($model->recipes()->exists()) {
            return Response::deny("{$model->name} is used in recipes, so it can't be deleted. Deactivate it instead, or remove it from those recipes first.");
        }

        if ($model->stockAdjustments()->exists()) {
            return Response::deny("{$model->name} has stock history, so it can't be deleted. Deactivate it instead to keep its records.");
        }

        return true;
    }
}
