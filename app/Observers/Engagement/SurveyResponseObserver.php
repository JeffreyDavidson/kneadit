<?php

declare(strict_types=1);

namespace App\Observers\Engagement;

use App\Models\Engagement\SurveyResponse;

/**
 * SurveyResponse uses #[WithoutTimestamps] to opt out of Laravel's
 * automatic timestamp handling (the table has no updated_at column).
 * The created_at column is nullable with no DB default, so without
 * this observer any caller that forgets to pass created_at would
 * write a NULL row.
 */
class SurveyResponseObserver
{
    public function creating(SurveyResponse $response): void
    {
        $response->created_at ??= now();
    }
}
