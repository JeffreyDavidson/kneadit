<?php

namespace App\Models;

use App\Casts\StripTagsCast;
use Database\Factories\ContactMessageFactory;
use Illuminate\Database\Eloquent\Attributes\Scope;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

/**
 * @method static \Database\Factories\ContactMessageFactory factory($count = null, $state = [])
 * @method static Builder<static>|ContactMessage newModelQuery()
 * @method static Builder<static>|ContactMessage newQuery()
 * @method static Builder<static>|ContactMessage query()
 * @method static Builder<static>|ContactMessage read()
 * @method static Builder<static>|ContactMessage unread()
 *
 * @mixin \Eloquent
 */
class ContactMessage extends Model
{
    /** @use HasFactory<ContactMessageFactory> */
    use HasFactory;

    protected $fillable = [
        'name',
        'email',
        'subject',
        'message',
        'is_read',
    ];

    protected function casts(): array
    {
        return [
            'is_read' => 'boolean',
            'name' => StripTagsCast::class,
            'subject' => StripTagsCast::class,
            'message' => StripTagsCast::class,
        ];
    }

    /** @param Builder<ContactMessage> $query */
    #[Scope]
    protected function unread(Builder $query): void
    {
        $query->where('is_read', false);
    }

    /** @param Builder<ContactMessage> $query */
    #[Scope]
    protected function read(Builder $query): void
    {
        $query->where('is_read', true);
    }
}
