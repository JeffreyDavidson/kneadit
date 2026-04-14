<?php

namespace App\Models\Customers;

use App\Builders\Customers\ContactMessageQueryBuilder;
use App\Casts\StripTagsCast;
use Database\Factories\Customers\ContactMessageFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\UseEloquentBuilder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

/**
 * @method static \Database\Factories\ContactMessageFactory factory($count = null, $state = [])
 * @method static ContactMessageQueryBuilder|ContactMessage newModelQuery()
 * @method static ContactMessageQueryBuilder|ContactMessage newQuery()
 * @method static ContactMessageQueryBuilder|ContactMessage query()
 *
 * @mixin \Eloquent
 */
#[Fillable('name', 'email', 'subject', 'message', 'is_read')]
#[UseEloquentBuilder(ContactMessageQueryBuilder::class)]
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

    protected static function newFactory(): ContactMessageFactory
    {
        return ContactMessageFactory::new();
    }
}
