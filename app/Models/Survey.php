<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * @property-read Collection<int, SurveyResponse> $responses
 * @property-read int|null $responses_count
 *
 * @method static \Database\Factories\SurveyFactory factory($count = null, $state = [])
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Survey newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Survey newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Survey query()
 *
 * @mixin \Eloquent
 */
class Survey extends Model
{
    use HasFactory;

    protected $fillable = [
        'title',
        'description',
        'questions',
        'is_active',
        'responses_count',
    ];

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
