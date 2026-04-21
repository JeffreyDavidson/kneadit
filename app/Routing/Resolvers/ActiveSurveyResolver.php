<?php

namespace App\Routing\Resolvers;

use App\Models\Engagement\Survey;

final class ActiveSurveyResolver
{
    public function __invoke(int|string $id): Survey
    {
        return Survey::query()
            ->where('is_active', true)
            ->where('id', $id)
            ->firstOrFail();
    }
}
