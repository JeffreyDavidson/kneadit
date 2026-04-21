<?php

namespace App\Models\Engagement;

use Database\Factories\Engagement\SurveyFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\UseFactory;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * @property array<int, array<string, mixed>> $questions
 * @property-read Collection<int, SurveyResponse> $responses
 * @property-read int|null $responses_count
 *
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Survey newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Survey newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Survey query()
 *
 * @mixin \Eloquent
 */
#[Fillable('title', 'description', 'questions', 'is_active', 'responses_count')]
#[UseFactory(SurveyFactory::class)]
class Survey extends Model
{
    /** @use HasFactory<SurveyFactory> */
    use HasFactory;

    protected function casts(): array
    {
        return [
            'questions' => 'array',
            'is_active' => 'boolean',
            'responses_count' => 'integer',
        ];
    }

    /**
     * @return HasMany<SurveyResponse, $this>
     */
    public function responses(): HasMany
    {
        return $this->hasMany(SurveyResponse::class);
    }
}
