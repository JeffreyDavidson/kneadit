<?php

namespace App\Enums\Engagement;

use Filament\Support\Contracts\HasLabel;

enum SurveyQuestionType: string implements HasLabel
{
    case Rating = 'rating';
    case Text = 'text';
    case MultipleChoice = 'multiple_choice';

    public function getLabel(): string
    {
        return match ($this) {
            self::Rating => 'Rating',
            self::Text => 'Text',
            self::MultipleChoice => 'Multiple Choice',
        };
    }
}
