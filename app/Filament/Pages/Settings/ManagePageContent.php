<?php

namespace App\Filament\Pages\Settings;

use App\Filament\Concerns\RequiresManagerRole;
use App\Filament\Pages\Settings\Schemas\PageContent\AboutTabSchema;
use App\Filament\Pages\Settings\Schemas\PageContent\CateringTabSchema;
use App\Filament\Pages\Settings\Schemas\PageContent\ContactTabSchema;
use App\Filament\Pages\Settings\Schemas\PageContent\GalleryTabSchema;
use App\Filament\Pages\Settings\Schemas\PageContent\GiftCardsTabSchema;
use App\Filament\Pages\Settings\Schemas\PageContent\LoyaltyTabSchema;
use App\Filament\Pages\Settings\Schemas\PageContent\MenuTabSchema;
use App\Filament\Pages\Settings\Schemas\PageContent\OrderConfirmationTabSchema;
use App\Filament\Pages\Settings\Schemas\PageContent\OrderTabSchema;
use App\Filament\Pages\Settings\Schemas\PageContent\OrderTrackingTabSchema;
use App\Filament\Pages\Settings\Schemas\PageContent\ReviewsTabSchema;
use App\Filament\Pages\Settings\Schemas\PageContent\SubmitReviewTabSchema;
use App\Filament\Pages\Settings\Schemas\PageContent\SurveyTabSchema;
use App\Services\Settings\SettingsManager;
use BackedEnum;
use Filament\Actions\Action;
use Filament\Notifications\Notification;
use Filament\Pages\Page;
use Filament\Schemas\Components\Tabs;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;

class ManagePageContent extends Page
{
    use RequiresManagerRole;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedDocumentText;

    protected static string|\UnitEnum|null $navigationGroup = 'Settings';

    protected static ?string $navigationLabel = 'Page Content';

    protected static ?int $navigationSort = 3;

    protected string $view = 'filament.pages.settings.manage-page-content';

    protected static ?string $title = 'Page Content';

    /** @var array<string, mixed> */
    public array $pageContent = [];

    public function mount(): void
    {
        $this->loadFromSettings();
    }

    protected function loadFromSettings(): void
    {
        $this->pageContent = json_decode(resolve(SettingsManager::class)->get('page_content', '{}'), true) ?: [];
    }

    protected function getHeaderActions(): array
    {
        return [
            Action::make('preview')
                ->label('Preview Storefront')
                ->icon(Heroicon::OutlinedEye)
                ->color('gray')
                ->url(route('home'), shouldOpenInNewTab: true),

            Action::make('resetToSaved')
                ->label('Reset to saved')
                ->icon(Heroicon::OutlinedArrowPath)
                ->color('gray')
                ->requiresConfirmation()
                ->modalHeading('Discard unsaved changes?')
                ->modalDescription('Reverts every field on this page to the last saved version. Anything you typed since the last save will be lost.')
                ->modalSubmitActionLabel('Discard')
                ->action(function (): void {
                    $this->loadFromSettings();

                    Notification::make()
                        ->title('Reverted to last saved version')
                        ->success()
                        ->send();
                }),

            Action::make('save')
                ->label('Save')
                ->icon(Heroicon::OutlinedCheck)
                ->color('primary')
                ->action('save'),
        ];
    }

    public function content(Schema $schema): Schema
    {
        return $schema
            ->schema([
                Tabs::make('pages')
                    ->schema([
                        MenuTabSchema::make(),
                        OrderTabSchema::make(),
                        AboutTabSchema::make(),
                        ContactTabSchema::make(),
                        ReviewsTabSchema::make(),
                        GalleryTabSchema::make(),
                        CateringTabSchema::make(),
                        GiftCardsTabSchema::make(),
                        LoyaltyTabSchema::make(),
                        OrderTrackingTabSchema::make(),
                        OrderConfirmationTabSchema::make(),
                        SubmitReviewTabSchema::make(),
                        SurveyTabSchema::make(),
                    ])
                    ->columnSpanFull(),
            ]);
    }

    public function save(): void
    {
        try {
            resolve(SettingsManager::class)->set('page_content', json_encode($this->pageContent));

            Notification::make()
                ->title('Page content saved successfully!')
                ->success()
                ->send();
        } catch (\Exception) {
            Notification::make()
                ->title('Error saving page content')
                ->body('There was an error saving. Please try again.')
                ->danger()
                ->send();
        }
    }
}
