<?php

namespace App\Services\Audit;

use App\Models\Staff\User;
use Illuminate\Support\Facades\Context;

/**
 * Typed wrapper around Laravel's Context facade for the "who's the
 * acting user?" question.
 *
 * Context state automatically propagates from a dispatching request
 * into queued jobs, which means the observer can read the actor in
 * either context without coupling to auth(). HTTP requests get the
 * actor populated via SetActorContext middleware; jobs inherit it;
 * console commands and unauthenticated paths fall back to System.
 */
final class ActorContext
{
    private const KEY_ID = 'actor_id';

    private const KEY_NAME = 'actor_name';

    public const SYSTEM_NAME = 'System';

    public static function id(): ?int
    {
        $id = Context::get(self::KEY_ID);

        return is_int($id) ? $id : null;
    }

    public static function name(): string
    {
        $name = Context::get(self::KEY_NAME);

        return is_string($name) && $name !== '' ? $name : self::SYSTEM_NAME;
    }

    public static function set(?User $user): void
    {
        Context::add(self::KEY_ID, $user?->id);
        Context::add(self::KEY_NAME, $user?->name);
    }

    public static function clear(): void
    {
        Context::forget(self::KEY_ID);
        Context::forget(self::KEY_NAME);
    }
}
