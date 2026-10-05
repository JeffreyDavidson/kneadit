<?php

declare(strict_types=1);

namespace App\Filament\Forms\Components;

use App\Models\Inventory\Category;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Illuminate\Support\Str;

/**
 * Picks a product category and lets the baker create one on the spot, because a
 * new bakery has none and could not otherwise save its first product.
 */
class CategorySelect extends Select
{
    #[\Override]
    public static function make(?string $name = null): static
    {
        $static = parent::make($name);

        $static->label('Category');
        $static->options(fn (): array => Category::query()->pluck('name', 'id')->all());
        $static->helperText(fn (): ?string => Category::query()->doesntExist() ? 'You have no categories yet. Use the + button to create one.' : null);
        $static->createOptionForm([
            TextInput::make('name')
                ->label('Category Name')
                ->required()
                ->maxLength(255),
            Textarea::make('description')
                ->label('Description')
                ->rows(2),
        ]);
        $static->createOptionUsing(function (array $data): int {
            $name = $data['name'] ?? null;

            if (! is_string($name)) {
                throw new \InvalidArgumentException('A category name is required.');
            }

            return Category::query()->create([
                'name' => $name,
                'slug' => Str::slug($name),
                'description' => $data['description'] ?? null,
                'is_active' => true,
            ])->id;
        });

        return $static;
    }
}
