<?php

namespace App\Models\Customers;

use App\Casts\StripTagsCast;
use Database\Factories\Customers\ContactMessageFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
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
#[Fillable('name', 'email', 'subject', 'message', 'is_read')]
class ContactMessage extends Model
{
    /** @use HasFactory<ContactMessageFactory> */
    use HasFactory;

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

    protected static function newFactory(): ContactMessageFactory
    {
        return ContactMessageFactory::new();
    }
}
