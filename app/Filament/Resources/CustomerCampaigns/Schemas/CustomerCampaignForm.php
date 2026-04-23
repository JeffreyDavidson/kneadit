<?php

namespace App\Filament\Resources\CustomerCampaigns\Schemas;

use App\Enums\Customers\RfmSegment;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

class CustomerCampaignForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Campaign Details')
                    ->columnSpanFull()
                    ->components([
                        TextInput::make('name')
                            ->label('Internal name')
                            ->helperText('For your reference only — not shown to customers.')
                            ->required()
                            ->maxLength(255),

                        Select::make('target_segment')
                            ->label('Audience')
                            ->options(self::segmentOptions())
                            ->default('all')
                            ->required()
                            ->helperText('Customers with at least one paid order who match this RFM segment.'),

                        TextInput::make('subject')
                            ->label('Email subject')
                            ->required()
                            ->maxLength(255),

                        Textarea::make('body')
                            ->label('Email body')
                            ->required()
                            ->rows(10)
                            ->columnSpanFull()
                            ->helperText('Plain text — line breaks preserved.'),
                    ]),
            ]);
    }

    /** @return array<string, string> */
    private static function segmentOptions(): array
    {
        $options = ['all' => 'All paid customers'];

        foreach (RfmSegment::cases() as $segment) {
            $options[$segment->value] = $segment->getLabel() . ' — ' . $segment->description();
        }

        return $options;
    }
}
