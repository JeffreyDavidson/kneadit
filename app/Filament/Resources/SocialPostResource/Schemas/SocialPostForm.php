<?php

namespace App\Filament\Resources\SocialPostResource\Schemas;

use App\Actions\Content\GenerateSocialCaption;
use App\Enums\Marketing\SocialPlatform;
use App\Enums\Marketing\SocialPostStatus;
use App\Models\Inventory\Product;
use Filament\Actions\Action;
use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Notifications\Notification;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Components\Utilities\Get;
use Filament\Schemas\Components\Utilities\Set;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Illuminate\Support\Number;

class SocialPostForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Post Details')
                    ->columnSpanFull()
                    ->components([
                        Grid::make(2)
                            ->components([
                                Select::make('platform')
                                    ->options(SocialPlatform::options())
                                    ->required()
                                    ->live()
                                    ->default('instagram'),

                                Select::make('product_id')
                                    ->label('Product')
                                    ->options(Product::query()->active()->pluck('name', 'id'))
                                    ->searchable()
                                    ->nullable()
                                    ->live(),
                            ]),

                        Textarea::make('caption')
                            ->required()
                            ->rows(4)
                            ->maxLength(fn (Get $get): int => (SocialPlatform::tryFrom($get('platform') ?? 'instagram')?->maxChars() ?? 2200))
                            ->helperText(fn (Get $get): string => 'Max ' . Number::format((SocialPlatform::tryFrom($get('platform') ?? 'instagram')?->maxChars() ?? 2200)) . ' characters for ' . ((SocialPlatform::tryFrom($get('platform') ?? 'instagram')?->getLabel() ?? 'Instagram')))
                            ->hintAction(
                                Action::make('generateCaption')
                                    ->label('Generate Caption')
                                    ->icon(Heroicon::OutlinedSparkles)
                                    ->action(function (Get $get, Set $set) {
                                        $productId = $get('product_id');

                                        if (! $productId) {
                                            Notification::make()
                                                ->title('Select a product first')
                                                ->body('Choose a product to generate a caption from.')
                                                ->warning()
                                                ->send();

                                            return;
                                        }

                                        /** @var Product|null $product */
                                        $product = Product::query()->find($productId);
                                        if (! $product) {
                                            return;
                                        }

                                        $set('caption', resolve(GenerateSocialCaption::class)($product));

                                        Notification::make()
                                            ->title('Caption generated!')
                                            ->success()
                                            ->send();
                                    }),
                            ),

                        FileUpload::make('image_path')
                            ->label('Image')
                            ->image()
                            ->maxSize(5120)
                            ->directory('social-posts')
                            ->nullable(),
                    ]),

                Section::make('Scheduling')
                    ->columnSpanFull()
                    ->components([
                        Grid::make(2)
                            ->components([
                                DateTimePicker::make('scheduled_for')
                                    ->nullable(),

                                Select::make('status')
                                    ->options(SocialPostStatus::class)
                                    ->required()
                                    ->default(SocialPostStatus::Draft),
                            ]),

                        Textarea::make('notes')
                            ->rows(2)
                            ->nullable(),
                    ]),
            ]);
    }
}
