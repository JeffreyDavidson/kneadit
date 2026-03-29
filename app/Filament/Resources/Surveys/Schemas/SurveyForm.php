<?php

namespace App\Filament\Resources\Surveys\Schemas;

use Filament\Forms\Components\Repeater;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TagsInput;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Schema;

class SurveyForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema->components([
            TextInput::make('title')
                ->required()
                ->maxLength(255),

            Textarea::make('description')
                ->rows(3),

            Toggle::make('is_active')
                ->label('Active')
                ->default(true),

            Repeater::make('questions')
                ->schema([
                    Select::make('type')
                        ->options([
                            'rating' => 'Rating (1-5)',
                            'text' => 'Text Response',
                            'multiple_choice' => 'Multiple Choice',
                        ])
                        ->required()
                        ->live(),

                    TextInput::make('question')
                        ->required()
                        ->maxLength(500),

                    TagsInput::make('options')
                        ->placeholder('Add option')
                        ->visible(fn (callable $get) => $get('type') === 'multiple_choice'),
                ])
                ->columns(1)
                ->defaultItems(1)
                ->addActionLabel('Add Question')
                ->required(),
        ]);
    }
}
