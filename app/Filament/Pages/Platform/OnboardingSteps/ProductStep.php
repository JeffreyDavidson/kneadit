<?php

namespace App\Filament\Pages\Platform\OnboardingSteps;

use App\Filament\Pages\Platform\Onboarding;
use App\Models\Inventory\Category;
use App\Models\Inventory\Product;
use App\Services\Settings\TenantSettings;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Components\Wizard\Step;
use Filament\Support\Icons\Heroicon;
use Illuminate\Support\Str;

final class ProductStep extends OnboardingStep
{
    public static function key(): string
    {
        return 'product';
    }

    public static function defaults(TenantSettings $settings): array
    {
        $productId = settings('onboarding_product_id');
        $product = $productId ? Product::query()->find((int) $productId) : null;

        if ($product) {
            return [
                'name' => $product->name,
                'description' => $product->description ?? '',
                'price' => (string) $product->price,
                'category_id' => (string) $product->category_id,
            ];
        }

        return [
            'name' => '',
            'description' => '',
            'price' => '',
            'category_id' => '',
        ];
    }

    public static function make(Onboarding $page): Step
    {
        return Step::make('First Product')
            ->icon(Heroicon::OutlinedCake)
            ->description('Add something delicious')
            ->schema([
                Section::make('Create Your First Product')
                    ->description('Add your first product to get your shop started. You can always add more later.')
                    ->schema([
                        TextInput::make('product.name')
                            ->label('Product Name')
                            ->required()
                            ->placeholder('e.g. Classic Sourdough Loaf')
                            ->maxLength(255),
                        Textarea::make('product.description')
                            ->label('Description')
                            ->placeholder('Describe your product...')
                            ->rows(3),
                        Grid::make(2)->schema([
                            TextInput::make('product.price')
                                ->label('Price')
                                ->required()
                                ->numeric()
                                ->prefix('$')
                                ->placeholder('12.00'),
                            Select::make('product.category_id')
                                ->label('Category')
                                ->options(fn () => Category::query()->pluck('name', 'id')->toArray())
                                ->required()
                                ->createOptionForm([
                                    TextInput::make('name')
                                        ->label('Category Name')
                                        ->required()
                                        ->maxLength(255),
                                    Textarea::make('description')
                                        ->label('Description')
                                        ->rows(2),
                                ])
                                ->createOptionUsing(function (array $data): int {
                                    $category = Category::query()->create([
                                        'name' => $data['name'],
                                        'slug' => Str::slug($data['name']),
                                        'description' => $data['description'] ?? null,
                                        'is_active' => true,
                                    ]);

                                    return $category->id;
                                }),
                        ]),
                    ]),
            ])
            ->afterValidation(fn () => self::save($page->product));
    }

    public static function save(array $data): void
    {
        $existingId = settings('onboarding_product_id');

        $product = Product::query()->updateOrCreate(
            $existingId ? ['id' => $existingId] : ['slug' => Str::slug($data['name'])],
            [
                'name' => $data['name'],
                'slug' => Str::slug($data['name']),
                'description' => $data['description'],
                'price' => $data['price'],
                'category_id' => $data['category_id'],
                'is_active' => true,
            ],
        );

        settings(['onboarding_product_id' => $product->id]);
    }
}
