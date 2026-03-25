<?php

namespace App\Models;

use Database\Factories\SupportReplyFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Carbon;

/**
 * @property int $id
 * @property int $ticket_id
 * @property string $author_type
 * @property string $author_name
 * @property string $body
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 * @property-read SupportTicket $ticket
 *
 * @method static \Database\Factories\SupportReplyFactory factory($count = null, $state = [])
 * @method static \Illuminate\Database\Eloquent\Builder<static>|SupportReply newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|SupportReply newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|SupportReply query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|SupportReply whereAuthorName($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|SupportReply whereAuthorType($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|SupportReply whereBody($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|SupportReply whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|SupportReply whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|SupportReply whereTicketId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|SupportReply whereUpdatedAt($value)
 *
 * @mixin \Eloquent
 */
class SupportReply extends Model
{
    /** @use HasFactory<SupportReplyFactory> */
    use HasFactory;

    protected $connection = 'central';

    protected $fillable = [
        'ticket_id',
        'author_type',
        'author_name',
        'body',
    ];

    /**
     * @return BelongsTo<SupportTicket, $this>
     */
    public function ticket(): BelongsTo
    {
        return $this->belongsTo(SupportTicket::class, 'ticket_id');
    }
}
