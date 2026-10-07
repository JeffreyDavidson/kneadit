<?php

declare(strict_types=1);

namespace App\Policies\Platform;

use App\Enums\Staff\UserRole;
use App\Models\Platform\Tenant;
use App\Models\Staff\User;
use App\Queries\Platform\OwnerSubscriptionsQuery;
use Illuminate\Auth\Access\Response;

/**
 * Bakeries are managed only from the central admin, by platform admins.
 * Bakeries are hard-deleted, so there is no restore or force-delete ability.
 */
class TenantPolicy
{
    public function __construct(private readonly OwnerSubscriptionsQuery $subscriptions) {}

    public function before(User $user): ?bool
    {
        return $user->role === UserRole::PlatformAdmin ? null : false;
    }

    public function viewAny(User $user): bool
    {
        return true;
    }

    public function view(User $user, Tenant $tenant): bool
    {
        return true;
    }

    public function create(User $user): bool
    {
        return true;
    }

    public function update(User $user, Tenant $tenant): bool
    {
        return true;
    }

    /**
     * A bakery whose owner has any subscription that has not ended (past due
     * and incomplete ones included) must have it cancelled first; the
     * DeletingTenant listener is only a safety net.
     */
    public function delete(User $user, Tenant $tenant): Response
    {
        if ($tenant->owner instanceof User && $this->subscriptions->open($tenant->owner)->isNotEmpty()) {
            return Response::deny("Cancel this bakery's subscription first.");
        }

        return Response::allow();
    }

    public function deleteAny(User $user): bool
    {
        return true;
    }
}
