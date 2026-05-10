<?php

namespace App\Filament\Resources\EmailCampaigns\Schemas;

use Filament\Forms\Components\RichEditor;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Components\Utilities\Set;
use Filament\Schemas\Schema;

class EmailCampaignForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Campaign Details')
                    ->columnSpanFull()
                    ->components([
                        Select::make('template')
                            ->label('Start from template')
                            ->options([
                                'custom' => 'Custom',
                                'new_menu' => 'New Menu Items',
                                'holiday' => 'Holiday Special',
                                'miss_you' => 'We Miss You',
                            ])
                            ->default('custom')
                            ->live()
                            ->afterStateUpdated(function (string $state, Set $set) {
                                $templates = self::getTemplates();

                                if (isset($templates[$state])) {
                                    $set('subject', $templates[$state]['subject']);
                                    $set('body', $templates[$state]['body']);
                                }
                            })
                            ->dehydrated(false),

                        TextInput::make('subject')
                            ->required()
                            ->maxLength(255),

                        RichEditor::make('body')
                            ->required()
                            ->columnSpanFull(),
                    ]),
            ]);
    }

    /** @return array<string, mixed> */
    protected static function getTemplates(): array
    {
        return [
            'new_menu' => [
                'subject' => 'New Items Just Added to Our Menu!',
                'body' => '<h2>Fresh from the Oven!</h2><p>We\'ve been busy in the kitchen crafting some exciting new treats for you. Come by and try our latest creations — we think you\'re going to love them!</p><p><strong>New this week:</strong></p><ul><li>[Item 1 — e.g. Rosemary Focaccia]</li><li>[Item 2 — e.g. Chocolate Babka]</li><li>[Item 3 — e.g. Lemon Lavender Scones]</li></ul><p>Stop by or place your order online. We can\'t wait to hear what you think!</p>',
            ],
            'holiday' => [
                'subject' => 'Holiday Specials — Order Before They\'re Gone!',
                'body' => '<h2>The Holidays Are Here!</h2><p>Celebrate the season with our limited-time holiday specials. Perfect for gifting, gatherings, or treating yourself.</p><p><strong>Holiday favorites:</strong></p><ul><li>[Special 1 — e.g. Gingerbread Cookie Box]</li><li>[Special 2 — e.g. Cranberry Walnut Loaf]</li><li>[Special 3 — e.g. Peppermint Brownies]</li></ul><p><strong>Order by [date] to guarantee availability!</strong></p><p>Wishing you a sweet season from our kitchen to yours.</p>',
            ],
            'miss_you' => [
                'subject' => 'We Miss You! Here\'s a Little Something',
                'body' => '<h2>It\'s Been a While!</h2><p>We noticed it\'s been some time since your last visit, and we just wanted to say — we miss you!</p><p>To welcome you back, here\'s a special treat just for you:</p><p><strong>[Offer — e.g. 10% off your next order, free cookie with any purchase]</strong></p><p>We\'d love to see you again. Come by anytime — there\'s always something fresh waiting for you.</p>',
            ],
            'custom' => [
                'subject' => '',
                'body' => '',
            ],
        ];
    }
}
