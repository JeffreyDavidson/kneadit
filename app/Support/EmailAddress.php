<?php

declare(strict_types=1);

namespace App\Support;

use Illuminate\Support\Str;

/**
 * The one definition of how a customer email address is stored and matched.
 *
 * SQLite compares text case-sensitively, so `Bob@x.com` and `bob@x.com` would
 * otherwise be two different customers. Emails are written and looked up
 * lowercased and trimmed.
 */
final class EmailAddress
{
    public static function normalize(string $email): string
    {
        return Str::lower(trim($email));
    }
}
