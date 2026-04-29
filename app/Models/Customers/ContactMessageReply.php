<?php

namespace App\Models\Customers;

use App\Models\Staff\User;
use Database\Factories\Customers\ContactMessageReplyFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\UseFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * @property-read ContactMessage $contactMessage
 * @property-read User|null $sentBy
 *
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ContactMessageReply newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ContactMessageReply newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ContactMessageReply query()
 *
 * @mixin \Eloquent
 */
#[Fillable('contact_message_id', 'user_id', 'subject', 'body', 'sent_at')]
#[UseFactory(ContactMessageReplyFactory::class)]
class ContactMessageReply extends Model
{
    /** @use HasFactory<ContactMessageReplyFactory> */
    use HasFactory;

    protected function casts(): array
    {
        return [
            'sent_at' => 'datetime',
        ];
    }

    /**
     * @return BelongsTo<ContactMessage, $this>
     */
    public function contactMessage(): BelongsTo
    {
        return $this->belongsTo(ContactMessage::class);
    }

    /**
     * @return BelongsTo<User, $this>
     */
    public function sentBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }
}
