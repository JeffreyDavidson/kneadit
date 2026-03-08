<?php

namespace App\Filament\Resources\SocialPosts\Schemas;

use App\Models\Product;
use App\Models\SocialPost;
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

class SocialPostForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Post Details')
                    ->components([
                        Grid::make(2)
                            ->components([
                                Select::make('platform')
                                    ->options(SocialPost::PLATFORMS)
                                    ->required()
                                    ->live()
                                    ->default('instagram'),

                                Select::make('product_id')
                                    ->label('Product')
                                    ->options(Product::query()->where('is_active', true)->pluck('name', 'id'))
                                    ->searchable()
                                    ->nullable()
                                    ->live(),
                            ]),

                        Textarea::make('caption')
                            ->required()
                            ->rows(4)
                            ->maxLength(fn (Get $get): int => SocialPost::PLATFORM_MAX_CHARS[$get('platform') ?? 'instagram'] ?? 2200)
                            ->helperText(fn (Get $get): string => 'Max ' . number_format(SocialPost::PLATFORM_MAX_CHARS[$get('platform') ?? 'instagram'] ?? 2200) . ' characters for ' . SocialPost::PLATFORMS[$get('platform') ?? 'instagram'] ?? 'Instagram')
                            ->hintAction(
                                \Filament\Forms\Components\Actions\Action::make('generateCaption')
                                    ->label('Generate Caption')
                                    ->icon('heroicon-o-sparkles')
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

                                        $product = Product::find($productId);
                                        if (! $product) {
                                            return;
                                        }

                                        $storeName = tenant()->store_name ?? tenant()->name ?? 'Our Bakery';
                                        $storeHashtag = str_replace(' ', '', ucwords($storeName));

                                        $templates = [
                                            "Fresh from the oven! Our {product} is made with love and the finest ingredients. Order yours today! 🍞✨ #{store_hashtag}",
                                            "Have you tried our {product}? It's one of our favorites! DM us to place your order 💛 #{store_hashtag}",
                                            "Weekend treat alert! 🎉 Our {product} ({price}) is calling your name. Link in bio to order! #{store_hashtag}",
                                        ];

                                        $template = $templates[array_rand($templates)];

                                        $caption = str_replace(
                                            ['{product}', '{price}', '{store_hashtag}'],
                                            [$product->name, '$' . number_format($product->price, 2), $storeHashtag],
                                            $template
                                        );

                                        $set('caption', $caption);

                                        Notification::make()
                                            ->title('Caption generated!')
                                            ->success()
                                            ->send();
                                    })
                            ),

                        FileUpload::make('image_path')
                            ->label('Image')
                            ->image()
                            ->directory('social-posts')
                            ->nullable(),
                    ]),

                Section::make('Scheduling')
                    ->components([
                        Grid::make(2)
                            ->components([
                                DateTimePicker::make('scheduled_for')
                                    ->nullable(),

                                Select::make('status')
                                    ->options(SocialPost::STATUSES)
                                    ->required()
                                    ->default('draft'),
                            ]),

                        Textarea::make('notes')
                            ->rows(2)
                            ->nullable(),
                    ]),
            ]);
    }
}
