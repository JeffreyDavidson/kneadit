<?php

declare(strict_types=1);

namespace App\Support\Auth;

use App\Support\EmailAddress;
use Illuminate\Auth\EloquentUserProvider;
use Illuminate\Contracts\Auth\Authenticatable;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Query\Expression;

/**
 * Looks staff users up by email without regard to case or surrounding spaces.
 *
 * Sign-in and password-reset lookups both go through retrieveByCredentials(), so
 * `FOO@x.com` finds the account stored as `foo@x.com`. Accounts created before
 * emails were normalized may still be stored with capitals, so when the exact
 * address matches nothing, the lowercased column is compared as well. An exact
 * match always wins, which keeps two legacy accounts that differ only by case apart.
 */
class NormalizedEmailUserProvider extends EloquentUserProvider
{
    /**
     * @param  array<string, mixed>  $credentials
     */
    #[\Override]
    public function retrieveByCredentials(#[\SensitiveParameter] array $credentials): ?Authenticatable
    {
        $user = parent::retrieveByCredentials($credentials);

        if ($user instanceof Authenticatable || ! is_string($credentials['email'] ?? null)) {
            return $user;
        }

        $email = EmailAddress::normalize($credentials['email']);
        unset($credentials['email']);

        $credentials['email'] = fn (Builder $query): Builder => $query->where(new Expression('lower(email)'), $email);

        return parent::retrieveByCredentials($credentials);
    }
}
