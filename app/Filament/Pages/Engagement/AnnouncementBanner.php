<?php

namespace App\Filament\Pages\Engagement;

use App\Filament\Concerns\RequiresManagerRole;
use BackedEnum;
use Filament\Actions\Action;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Notifications\Notification;
use Filament\Pages\Concerns\InteractsWithFormActions;
use Filament\Pages\Page;
use Filament\Schemas\Components\Actions;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Components\View;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;

class AnnouncementBanner extends Page
{
    use InteractsWithFormActions;
    use RequiresManagerRole;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedMegaphone;

    protected static string|\UnitEnum|null $navigationGroup = 'Settings';

    protected static ?string $navigationLabel = 'Announcement Banner';

    protected static ?int $navigationSort = 7;

    protected string $view = 'filament.pages.engagement.announcement-banner';

    protected static ?string $title = 'Announcement Banner';

    public bool $announcement_enabled = false;

    public ?string $announcement_text = '';

    public ?string $announcement_type = 'info';

    public function mount(): void
    {
        $this->announcement_enabled = settings('announcement_enabled', '0') === '1';
        $this->announcement_text = settings('announcement_text', '');
        $this->announcement_type = settings('announcement_type', 'info');
    }

    public function content(Schema $schema): Schema
    {
        return $schema
            ->schema([
                Section::make('Announcement Banner')
                    ->description('Display a dismissable announcement banner on your storefront. Customers can close it, and changing the message will show it again.')
                    ->schema([
                        Toggle::make('announcement_enabled')
                            ->label('Enable Banner')
                            ->helperText('Show the announcement banner on your storefront')
                            ->live(),

                        TextInput::make('announcement_text')
                            ->label('Announcement Message')
                            ->placeholder('e.g. Holiday hours: Closed Dec 25th. Order early!')
                            ->maxLength(500)
                            ->columnSpanFull(),

                        Select::make('announcement_type')
                            ->label('Banner Style')
                            ->options([
                                'info' => '📢 Info (Gold)',
                                'warning' => '⚠️ Warning (Orange)',
                                'success' => '✅ Success (Green)',
                                'holiday' => '🎄 Holiday (Festive Red/Green)',
                            ])
                            ->default('info'),
                    ]),

                Section::make('Preview')
                    ->description('This is how the banner will appear on your storefront')
                    ->schema([
                        View::make('filament.pages.engagement.announcement-banner-preview'),
                    ]),

                Actions::make([
                    Action::make('save')
                        ->label('Save Banner Settings')
                        ->color('primary')
                        ->action('save'),
                ])
                    ->alignEnd()
                    ->columnSpanFull(),
            ]);
    }

    public function save(): void
    {
        settings(['announcement_enabled' => $this->announcement_enabled ? '1' : '0']);
        settings(['announcement_text' => $this->announcement_text ?? '']);
        settings(['announcement_type' => $this->announcement_type ?? 'info']);

        Notification::make()
            ->title('Announcement banner settings saved!')
            ->success()
            ->send();
    }
}
