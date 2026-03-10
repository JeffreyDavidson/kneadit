<?php

namespace App\Filament\Central\Resources\MessageResource\Pages;

use App\Filament\Central\Resources\MessageResource;
use App\Models\PlatformMessage;
use App\Models\Tenant;
use Filament\Resources\Pages\Page;
use Filament\Schemas\Components\Select;
use Filament\Schemas\Components\Textarea;
use Filament\Schemas\Components\TextInput;
use Filament\Schemas\Schema;
use Filament\Actions;

class ComposeMessage extends Page
{
    protected static string $resource = MessageResource::class;

    protected string $view = 'filament.central.pages.compose-message';

    public ?string $tenant_id = null;
    public ?string $subject = null;
    public ?string $body = null;

    public function getTitle(): string
    {
        return 'Compose Message';
    }

    public function form(Schema $form): Schema
    {
        return $form->schema([
            Select::make('tenant_id')
                ->label('Bakery')
                ->options(fn () => Tenant::pluck('name', 'id')->toArray())
                ->searchable()
                ->required(),
            TextInput::make('subject')
                ->required()
                ->maxLength(255),
            Textarea::make('body')
                ->required()
                ->rows(6),
        ]);
    }

    public function send(): void
    {
        $this->validate([
            'tenant_id' => 'required|exists:tenants,id',
            'subject' => 'required|string|max:255',
            'body' => 'required|string',
        ]);

        PlatformMessage::create([
            'tenant_id' => $this->tenant_id,
            'sender_type' => 'admin',
            'subject' => $this->subject,
            'body' => $this->body,
        ]);

        $this->redirect(MessageResource::getUrl('index'));
    }
}
