<?php

namespace App\Filament\Central\Resources\MessageResource\Pages;

use App\Enums\Platform\PlatformSenderType;
use App\Filament\Central\Resources\MessageResource;
use App\Models\Platform\PlatformMessage;
use App\Models\Platform\Tenant;
use Filament\Actions;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Resources\Pages\ListRecords;
use Filament\Support\Icons\Heroicon;

class ListMessages extends ListRecords
{
    protected static string $resource = MessageResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\Action::make('compose')
                ->label('Compose Message')
                ->icon(Heroicon::OutlinedPencilSquare)
                ->slideOver()
                ->schema([
                    Select::make('tenant_id')
                        ->label('Bakery')
                        ->options(fn () => Tenant::query()->get()->mapWithKeys(fn (Tenant $t) => [$t->id => $t->store_name ?: $t->name])->all())
                        ->searchable()
                        ->required(),
                    TextInput::make('subject')
                        ->required()
                        ->maxLength(255),
                    Textarea::make('body')
                        ->required()
                        ->rows(6),
                ])
                ->action(function (array $data) {
                    PlatformMessage::query()->create([
                        'tenant_id' => $data['tenant_id'],
                        'sender_type' => PlatformSenderType::Admin,
                        'subject' => $data['subject'],
                        'body' => $data['body'],
                    ]);

                    $this->sendSuccessNotification();
                })
                ->successNotificationTitle('Message sent'),
        ];
    }
}
