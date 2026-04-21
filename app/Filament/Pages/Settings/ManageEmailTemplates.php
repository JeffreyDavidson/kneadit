<?php

namespace App\Filament\Pages\Settings;

use App\Enums\Marketing\EmailTemplateType;
use App\Enums\Platform\SubscriptionTier;
use App\Filament\Concerns\RequiresManagerRole;
use App\Filament\Concerns\ShowsUpgradeBadge;
use App\Models\Marketing\EmailTemplate;
use BackedEnum;
use Filament\Actions\Action;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Notifications\Notification;
use Filament\Pages\Page;
use Filament\Schemas\Components\Section;
use Filament\Support\Icons\Heroicon;
use Laravel\Pennant\Feature;

class ManageEmailTemplates extends Page
{
    use RequiresManagerRole;
    use ShowsUpgradeBadge;

    public static function canAccess(): bool
    {
        return static::hasManagerAccess() && Feature::active('pro-features');
    }

    protected static function requiredTier(): SubscriptionTier
    {
        return SubscriptionTier::Pro;
    }

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedEnvelope;

    protected static string|\UnitEnum|null $navigationGroup = 'Settings';

    protected static ?string $navigationLabel = 'Email Templates';

    protected static ?int $navigationSort = 6;

    protected string $view = 'filament.pages.settings.manage-email-templates';

    protected static ?string $title = 'Email Templates';

    /**
     * @return array<int, array{type: string, label: string, description: string, status: string, placeholders: array<int, string>}>
     */
    public function getTemplateData(): array
    {
        $customized = EmailTemplate::query()
            ->pluck('email_type')
            ->map(fn (EmailTemplateType $type) => $type->value)
            ->toArray();

        return collect(EmailTemplateType::cases())
            ->map(fn (EmailTemplateType $type) => [
                'type' => $type->value,
                'label' => $type->getLabel(),
                'description' => $type->description(),
                'status' => in_array($type->value, $customized) ? 'Customized' : 'Default',
                'placeholders' => $type->availablePlaceholders(),
            ])
            ->values()
            ->toArray();
    }

    public function editTemplate(string $typeValue): void
    {
        $this->mountAction('editEmailTemplate', [
            'email_type' => $typeValue,
        ]);
    }

    public function editEmailTemplateAction(): Action
    {
        return Action::make('editEmailTemplate')
            ->label('Edit Template')
            ->slideOver()
            ->schema(function (array $arguments): array {
                $type = EmailTemplateType::from($arguments['email_type']);
                $placeholderList = implode(', ', $type->availablePlaceholders());

                return [
                    Section::make($type->getLabel())
                        ->description($type->description())
                        ->schema([
                            TextInput::make('subject')
                                ->label('Subject Line')
                                ->required()
                                ->helperText("Available placeholders: {$placeholderList}"),
                            Textarea::make('body')
                                ->label('Email Body (HTML)')
                                ->rows(12)
                                ->helperText("Leave empty to use the default email template. Available placeholders: {$placeholderList}"),
                        ])
                        ->columnSpanFull(),
                ];
            })
            ->fillForm(function (array $arguments): array {
                $type = EmailTemplateType::from($arguments['email_type']);
                $existing = EmailTemplate::query()->where('email_type', $type)->first();

                return [
                    'subject' => $existing->subject ?? $type->defaultSubject(),
                    'body' => $existing->body ?? '',
                ];
            })
            ->action(function (array $data, array $arguments): void {
                $type = EmailTemplateType::from($arguments['email_type']);

                EmailTemplate::query()->updateOrCreate(
                    ['email_type' => $type],
                    [
                        'subject' => $data['subject'],
                        'body' => $data['body'] ?? '',
                    ],
                );

                Notification::make()
                    ->title('Email template saved')
                    ->success()
                    ->send();
            });
    }

    public function resetTemplate(string $typeValue): void
    {
        $type = EmailTemplateType::from($typeValue);

        EmailTemplate::query()->where('email_type', $type)->delete();

        Notification::make()
            ->title('Template reset to default')
            ->success()
            ->send();
    }
}
