<?php

namespace App\Models\Engagement;

use App\Models\Orders\Order;
use Database\Factories\Engagement\SurveyResponseFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\WithoutTimestamps;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * @property-read Order|null $order
 * @property-read Survey|null $survey
 *
 * @method static \Illuminate\Database\Eloquent\Builder<static>|SurveyResponse newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|SurveyResponse newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|SurveyResponse query()
 *
 * @mixin \Eloquent
 */
#[WithoutTimestamps]
#[Fillable('survey_id', 'customer_name', 'customer_email', 'answers', 'order_id', 'created_at')]
class SurveyResponse extends Model
{
    /** @use HasFactory<SurveyResponseFactory> */
    use HasFactory;

    protected function casts(): array
    {
        return [
            'answers' => 'array',
            'created_at' => 'datetime',
        ];
    }

    /**
     * @return BelongsTo<Survey, $this>
     */
    public function survey(): BelongsTo
    {
        return $this->belongsTo(Survey::class);
    }

    /**
     * @return BelongsTo<Order, $this>
     */
    public function order(): BelongsTo
    {
        return $this->belongsTo(Order::class);
    }

    protected static function newFactory(): SurveyResponseFactory
    {
        return SurveyResponseFactory::new();
    }
}
