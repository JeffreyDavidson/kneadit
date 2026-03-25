<?php

namespace App\Filament\Pages;

use App\Models\Setting;
use BackedEnum;
use Filament\Actions\Action;
use Filament\Forms\Components\Repeater;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Notifications\Notification;
use Filament\Pages\Page;
use Filament\Schemas\Components\Actions;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Components\Tabs;
use Filament\Schemas\Components\Tabs\Tab;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;

class ManagePageContent extends Page
{
    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedDocumentText;

    protected static string|\UnitEnum|null $navigationGroup = 'Settings';

    protected static ?string $navigationLabel = 'Page Content';

    protected static ?int $navigationSort = 3;

    protected string $view = 'filament.pages.manage-page-content';

    protected static ?string $title = 'Page Content';

    /** @var array<string, mixed> */
    public array $pageContent = [];

    public function mount(): void
    {
        $this->pageContent = json_decode(Setting::get('page_content', '{}'), true) ?: [];
    }

    public function content(Schema $schema): Schema
    {
        return $schema
            ->schema([
                Section::make('Storefront Page Content')
                    ->description('Customize all text that appears on your storefront pages. Use {{store_name}} for your bakery name and {{lead_time}} for order lead time hours.')
                    ->schema([
                        Tabs::make('pages')
                            ->schema([
                                $this->menuTab(),
                                $this->aboutTab(),
                                $this->contactTab(),
                                $this->reviewsTab(),
                                $this->galleryTab(),
                                $this->cateringTab(),
                                $this->giftCardsTab(),
                                $this->loyaltyTab(),
                                $this->orderTrackingTab(),
                                $this->orderConfirmationTab(),
                                $this->submitReviewTab(),
                                $this->surveyTab(),
                            ])
                            ->columnSpanFull(),
                    ]),

                Actions::make([
                    Action::make('save')
                        ->label('Save Page Content')
                        ->color('primary')
                        ->action('save'),
                ])
                    ->alignEnd()
                    ->columnSpanFull(),
            ]);
    }

    protected function menuTab(): Tab
    {
        return Tab::make('Menu')
            ->schema([
                Grid::make(2)->schema([
                    TextInput::make('pageContent.menu.hero_eyebrow')
                        ->label('Hero Eyebrow')
                        ->helperText('Use {{store_name}} for bakery name'),
                    TextInput::make('pageContent.menu.hero_title')
                        ->label('Hero Title'),
                ]),
                Textarea::make('pageContent.menu.hero_subtitle')
                    ->label('Hero Subtitle')
                    ->rows(2),
                TextInput::make('pageContent.menu.category_eyebrow')
                    ->label('Category Section Eyebrow'),
                Section::make('Call to Action')->schema([
                    Grid::make(2)->schema([
                        TextInput::make('pageContent.menu.cta_script')
                            ->label('CTA Script Text'),
                        TextInput::make('pageContent.menu.cta_button')
                            ->label('CTA Button Text'),
                    ]),
                    TextInput::make('pageContent.menu.cta_heading')
                        ->label('CTA Heading'),
                    TextInput::make('pageContent.menu.cta_description')
                        ->label('CTA Description')
                        ->helperText('Use {{lead_time}} for order lead time hours'),
                ])->compact(),
            ]);
    }

    protected function aboutTab(): Tab
    {
        return Tab::make('About')
            ->schema([
                TextInput::make('pageContent.about.hero_eyebrow')
                    ->label('Hero Eyebrow'),
                TextInput::make('pageContent.about.story_eyebrow')
                    ->label('Story Section Eyebrow'),
                Section::make('Values Section')->schema([
                    Grid::make(2)->schema([
                        TextInput::make('pageContent.about.values_eyebrow')
                            ->label('Values Eyebrow'),
                        TextInput::make('pageContent.about.values_heading')
                            ->label('Values Heading'),
                    ]),
                    Repeater::make('pageContent.about.values')
                        ->label('Value Cards')
                        ->schema([
                            TextInput::make('title')->required(),
                            Textarea::make('description')->rows(2)->required(),
                        ])
                        ->defaultItems(3)
                        ->maxItems(6),
                ])->compact(),
                Grid::make(2)->schema([
                    TextInput::make('pageContent.about.location_eyebrow')
                        ->label('Location Eyebrow'),
                    TextInput::make('pageContent.about.social_eyebrow')
                        ->label('Social Eyebrow'),
                ]),
                Section::make('Call to Action')->schema([
                    Grid::make(3)->schema([
                        TextInput::make('pageContent.about.cta_script')
                            ->label('CTA Script Text'),
                        TextInput::make('pageContent.about.cta_heading')
                            ->label('CTA Heading'),
                        TextInput::make('pageContent.about.cta_button')
                            ->label('CTA Button Text'),
                    ]),
                ])->compact(),
            ]);
    }

    protected function contactTab(): Tab
    {
        return Tab::make('Contact')
            ->schema([
                Grid::make(2)->schema([
                    TextInput::make('pageContent.contact.hero_eyebrow')
                        ->label('Hero Eyebrow'),
                    TextInput::make('pageContent.contact.form_eyebrow')
                        ->label('Form Section Eyebrow'),
                ]),
                Textarea::make('pageContent.contact.hero_title')
                    ->label('Hero Title')
                    ->helperText('Use line breaks for multi-line titles')
                    ->rows(2),
                Textarea::make('pageContent.contact.hero_subtitle')
                    ->label('Hero Subtitle')
                    ->rows(2),
                Grid::make(3)->schema([
                    TextInput::make('pageContent.contact.hours_eyebrow')
                        ->label('Hours Eyebrow'),
                    TextInput::make('pageContent.contact.faq_eyebrow')
                        ->label('FAQ Eyebrow'),
                    TextInput::make('pageContent.contact.faq_heading')
                        ->label('FAQ Heading'),
                ]),
            ]);
    }

    protected function reviewsTab(): Tab
    {
        return Tab::make('Reviews')
            ->schema([
                Grid::make(2)->schema([
                    TextInput::make('pageContent.reviews.hero_eyebrow')
                        ->label('Hero Eyebrow'),
                    TextInput::make('pageContent.reviews.hero_title')
                        ->label('Hero Title'),
                ]),
                Grid::make(2)->schema([
                    TextInput::make('pageContent.reviews.rating_eyebrow')
                        ->label('Rating Breakdown Eyebrow'),
                    TextInput::make('pageContent.reviews.all_reviews_label')
                        ->label('All Reviews Label'),
                ]),
                Grid::make(2)->schema([
                    TextInput::make('pageContent.reviews.empty_heading')
                        ->label('Empty State Heading'),
                    TextInput::make('pageContent.reviews.empty_description')
                        ->label('Empty State Description'),
                ]),
                Section::make('Call to Action')->schema([
                    Grid::make(2)->schema([
                        TextInput::make('pageContent.reviews.cta_script')
                            ->label('CTA Script Text'),
                        TextInput::make('pageContent.reviews.cta_button')
                            ->label('CTA Button Text'),
                    ]),
                    TextInput::make('pageContent.reviews.cta_heading')
                        ->label('CTA Heading'),
                    TextInput::make('pageContent.reviews.cta_description')
                        ->label('CTA Description'),
                ])->compact(),
            ]);
    }

    protected function galleryTab(): Tab
    {
        return Tab::make('Gallery')
            ->schema([
                Grid::make(2)->schema([
                    TextInput::make('pageContent.gallery.hero_eyebrow')
                        ->label('Hero Eyebrow'),
                    TextInput::make('pageContent.gallery.hero_title')
                        ->label('Hero Title'),
                ]),
                Textarea::make('pageContent.gallery.hero_subtitle')
                    ->label('Hero Subtitle')
                    ->rows(2),
                Section::make('Empty State')->schema([
                    TextInput::make('pageContent.gallery.empty_heading')
                        ->label('Heading'),
                    Textarea::make('pageContent.gallery.empty_description')
                        ->label('Description')
                        ->rows(2),
                    TextInput::make('pageContent.gallery.empty_script')
                        ->label('Script Text'),
                ])->compact(),
                Section::make('Upload Section')->schema([
                    Grid::make(3)->schema([
                        TextInput::make('pageContent.gallery.upload_eyebrow')
                            ->label('Eyebrow'),
                        TextInput::make('pageContent.gallery.upload_heading')
                            ->label('Heading'),
                        TextInput::make('pageContent.gallery.upload_description')
                            ->label('Description'),
                    ]),
                ])->compact(),
            ]);
    }

    protected function cateringTab(): Tab
    {
        return Tab::make('Catering')
            ->schema([
                Grid::make(2)->schema([
                    TextInput::make('pageContent.catering.hero_eyebrow')
                        ->label('Hero Eyebrow'),
                    TextInput::make('pageContent.catering.hero_title')
                        ->label('Hero Title'),
                ]),
                TextInput::make('pageContent.catering.hero_subtitle')
                    ->label('Hero Subtitle'),
                TextInput::make('pageContent.catering.hero_button')
                    ->label('Hero Button Text'),
                Section::make('Occasions')->schema([
                    Grid::make(2)->schema([
                        TextInput::make('pageContent.catering.occasions_eyebrow')
                            ->label('Eyebrow'),
                        TextInput::make('pageContent.catering.occasions_heading')
                            ->label('Heading'),
                    ]),
                    Repeater::make('pageContent.catering.occasions')
                        ->label('Occasion Cards')
                        ->schema([
                            TextInput::make('title')->required(),
                            Textarea::make('description')->rows(2)->required(),
                        ])
                        ->defaultItems(3)
                        ->maxItems(6),
                ])->compact(),
                Section::make('Process Steps')->schema([
                    Grid::make(2)->schema([
                        TextInput::make('pageContent.catering.process_eyebrow')
                            ->label('Eyebrow'),
                        TextInput::make('pageContent.catering.process_heading')
                            ->label('Heading'),
                    ]),
                    Repeater::make('pageContent.catering.process_steps')
                        ->label('Steps')
                        ->schema([
                            TextInput::make('title')->required(),
                            TextInput::make('description')->required(),
                        ])
                        ->defaultItems(4)
                        ->maxItems(6),
                ])->compact(),
                Section::make('Testimonial')->schema([
                    TextInput::make('pageContent.catering.testimonial_script')
                        ->label('Script Text'),
                    Textarea::make('pageContent.catering.testimonial_quote')
                        ->label('Quote')
                        ->rows(3),
                    TextInput::make('pageContent.catering.testimonial_attribution')
                        ->label('Attribution'),
                ])->compact(),
                Grid::make(2)->schema([
                    TextInput::make('pageContent.catering.form_eyebrow')
                        ->label('Form Eyebrow'),
                    TextInput::make('pageContent.catering.form_heading')
                        ->label('Form Heading'),
                ]),
            ]);
    }

    protected function giftCardsTab(): Tab
    {
        return Tab::make('Gift Cards')
            ->schema([
                Grid::make(2)->schema([
                    TextInput::make('pageContent.gift_cards.hero_eyebrow')
                        ->label('Hero Eyebrow'),
                    TextInput::make('pageContent.gift_cards.hero_subtitle')
                        ->label('Hero Subtitle'),
                ]),
                Textarea::make('pageContent.gift_cards.hero_title')
                    ->label('Hero Title')
                    ->helperText('Use line breaks for multi-line')
                    ->rows(2),
                Grid::make(2)->schema([
                    TextInput::make('pageContent.gift_cards.preview_label')
                        ->label('Preview Label'),
                    TextInput::make('pageContent.gift_cards.amount_label')
                        ->label('Amount Label'),
                ]),
                TextInput::make('pageContent.gift_cards.balance_heading')
                    ->label('Balance Check Heading'),
                Grid::make(2)->schema([
                    TextInput::make('pageContent.gift_cards.details_eyebrow')
                        ->label('Details Eyebrow'),
                    TextInput::make('pageContent.gift_cards.details_heading')
                        ->label('Details Heading'),
                ]),
                TextInput::make('pageContent.gift_cards.recipient_label')
                    ->label('Recipient Section Label'),
                Grid::make(2)->schema([
                    TextInput::make('pageContent.gift_cards.success_heading')
                        ->label('Success Heading'),
                    TextInput::make('pageContent.gift_cards.success_description')
                        ->label('Success Description'),
                ]),
            ]);
    }

    protected function loyaltyTab(): Tab
    {
        return Tab::make('Loyalty')
            ->schema([
                TextInput::make('pageContent.loyalty.hero_eyebrow')
                    ->label('Hero Eyebrow'),
                TextInput::make('pageContent.loyalty.hero_subtitle')
                    ->label('Hero Subtitle'),
                TextInput::make('pageContent.loyalty.paused_message')
                    ->label('Program Paused Message'),
                TextInput::make('pageContent.loyalty.check_heading')
                    ->label('Check Points Heading'),
                Grid::make(2)->schema([
                    TextInput::make('pageContent.loyalty.rewards_eyebrow')
                        ->label('Rewards Eyebrow'),
                    TextInput::make('pageContent.loyalty.rewards_heading')
                        ->label('Rewards Heading'),
                ]),
                Section::make('How It Works')->schema([
                    Grid::make(2)->schema([
                        TextInput::make('pageContent.loyalty.how_it_works_eyebrow')
                            ->label('Eyebrow'),
                        TextInput::make('pageContent.loyalty.how_it_works_heading')
                            ->label('Heading'),
                    ]),
                    Repeater::make('pageContent.loyalty.how_it_works_steps')
                        ->label('Steps')
                        ->schema([
                            TextInput::make('title')->required(),
                            TextInput::make('description')
                                ->required()
                                ->helperText('Use {{points_per_dollar}} for points value'),
                        ])
                        ->defaultItems(3)
                        ->maxItems(5),
                ])->compact(),
            ]);
    }

    protected function orderTrackingTab(): Tab
    {
        return Tab::make('Order Tracking')
            ->schema([
                Grid::make(2)->schema([
                    TextInput::make('pageContent.order_tracking.hero_eyebrow')
                        ->label('Hero Eyebrow'),
                    TextInput::make('pageContent.order_tracking.hero_title')
                        ->label('Hero Title'),
                ]),
                TextInput::make('pageContent.order_tracking.hero_subtitle')
                    ->label('Hero Subtitle'),
                Grid::make(2)->schema([
                    TextInput::make('pageContent.order_tracking.email_label')
                        ->label('Email Label'),
                    TextInput::make('pageContent.order_tracking.lookup_button')
                        ->label('Lookup Button Text'),
                ]),
                Section::make('Empty State')->schema([
                    TextInput::make('pageContent.order_tracking.empty_heading')
                        ->label('Heading'),
                    TextInput::make('pageContent.order_tracking.empty_description_prefix')
                        ->label('Description Prefix'),
                    TextInput::make('pageContent.order_tracking.empty_hint')
                        ->label('Hint Text'),
                ])->compact(),
                Grid::make(3)->schema([
                    TextInput::make('pageContent.order_tracking.items_label')
                        ->label('Items Label'),
                    TextInput::make('pageContent.order_tracking.messages_label')
                        ->label('Messages Label'),
                    TextInput::make('pageContent.order_tracking.reorder_button')
                        ->label('Reorder Button'),
                ]),
                Section::make('Call to Action')->schema([
                    Grid::make(3)->schema([
                        TextInput::make('pageContent.order_tracking.cta_script')
                            ->label('Script Text'),
                        TextInput::make('pageContent.order_tracking.cta_heading')
                            ->label('Heading'),
                        TextInput::make('pageContent.order_tracking.cta_button')
                            ->label('Button Text'),
                    ]),
                ])->compact(),
            ]);
    }

    protected function orderConfirmationTab(): Tab
    {
        return Tab::make('Order Confirmation')
            ->schema([
                Grid::make(2)->schema([
                    TextInput::make('pageContent.order_confirmation.hero_eyebrow')
                        ->label('Hero Eyebrow'),
                    TextInput::make('pageContent.order_confirmation.hero_title')
                        ->label('Hero Title'),
                ]),
                Textarea::make('pageContent.order_confirmation.hero_description')
                    ->label('Hero Description')
                    ->rows(2),
                TextInput::make('pageContent.order_confirmation.details_heading')
                    ->label('Order Details Heading'),
                Grid::make(2)->schema([
                    TextInput::make('pageContent.order_confirmation.journey_eyebrow')
                        ->label('Journey Eyebrow'),
                    TextInput::make('pageContent.order_confirmation.journey_heading')
                        ->label('Journey Heading'),
                ]),
                Repeater::make('pageContent.order_confirmation.journey_steps')
                    ->label('Journey Steps')
                    ->schema([
                        TextInput::make('title')->required(),
                        TextInput::make('description')
                            ->label('Description (general/pickup)'),
                        TextInput::make('description_delivery')
                            ->label('Description (delivery variant)')
                            ->helperText('Leave blank if same as above'),
                        TextInput::make('description_pickup')
                            ->label('Description (pickup variant)')
                            ->helperText('Leave blank if same as above'),
                    ])
                    ->defaultItems(3)
                    ->maxItems(5),
            ]);
    }

    protected function submitReviewTab(): Tab
    {
        return Tab::make('Submit Review')
            ->schema([
                Grid::make(2)->schema([
                    TextInput::make('pageContent.submit_review.hero_eyebrow')
                        ->label('Hero Eyebrow'),
                    TextInput::make('pageContent.submit_review.hero_title')
                        ->label('Hero Title'),
                ]),
                Grid::make(2)->schema([
                    TextInput::make('pageContent.submit_review.rating_label')
                        ->label('Rating Label'),
                    TextInput::make('pageContent.submit_review.submit_button')
                        ->label('Submit Button Text'),
                ]),
                Grid::make(2)->schema([
                    TextInput::make('pageContent.submit_review.comment_label')
                        ->label('Comment Label'),
                    TextInput::make('pageContent.submit_review.comment_placeholder')
                        ->label('Comment Placeholder'),
                ]),
                TextInput::make('pageContent.submit_review.photo_label')
                    ->label('Photo Upload Label'),
                Section::make('Success State')->schema([
                    Grid::make(2)->schema([
                        TextInput::make('pageContent.submit_review.success_title')
                            ->label('Title'),
                        TextInput::make('pageContent.submit_review.success_description')
                            ->label('Description'),
                    ]),
                ])->compact(),
            ]);
    }

    protected function surveyTab(): Tab
    {
        return Tab::make('Survey')
            ->schema([
                TextInput::make('pageContent.survey.hero_eyebrow')
                    ->label('Hero Eyebrow'),
                Grid::make(2)->schema([
                    TextInput::make('pageContent.survey.submit_button')
                        ->label('Submit Button Text'),
                    TextInput::make('pageContent.survey.submit_footer')
                        ->label('Submit Footer Text'),
                ]),
                Section::make('Success State')->schema([
                    Grid::make(2)->schema([
                        TextInput::make('pageContent.survey.success_title')
                            ->label('Title'),
                        TextInput::make('pageContent.survey.success_description')
                            ->label('Description'),
                    ]),
                ])->compact(),
            ]);
    }

    public function save(): void
    {
        try {
            Setting::set('page_content', json_encode($this->pageContent));

            Notification::make()
                ->title('Page content saved successfully!')
                ->success()
                ->send();
        } catch (\Exception $e) {
            Notification::make()
                ->title('Error saving page content')
                ->body('There was an error saving. Please try again.')
                ->danger()
                ->send();
        }
    }
}
