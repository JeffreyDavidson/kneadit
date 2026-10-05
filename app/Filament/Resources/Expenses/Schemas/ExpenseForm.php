<?php

declare(strict_types=1);

namespace App\Filament\Resources\Expenses\Schemas;

use App\Enums\Financial\ExpenseCategory;
use App\Filament\Forms\Components\MoneyInput;
use App\Filament\Forms\Components\PercentageInput;
use App\Filament\Support\AllowedFileTypes;
use App\Models\Financial\Expense;
use App\Services\Scheduling\BakeryClock;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Symfony\Component\Mime\MimeTypes;

class ExpenseForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Expense Details')
                    ->columnSpanFull()
                    ->components([
                        TextInput::make('description')
                            ->required()
                            ->maxLength(255),

                        Grid::make(2)
                            ->components([
                                MoneyInput::make('amount')
                                    ->required(),

                                Select::make('category')
                                    ->options(ExpenseCategory::class)
                                    ->required(),
                            ]),

                        DatePicker::make('date')
                            ->required()
                            ->default(resolve(BakeryClock::class)->today()),

                        FileUpload::make('receipt_image')
                            ->label('Receipt Image')
                            ->image()
                            ->acceptedFileTypes(AllowedFileTypes::IMAGES)
                            ->disk('receipts')
                            ->visibility('private')
                            // Receipts saved before the private disk existed are on the public disk,
                            // so don't drop a path just because it isn't on this one.
                            ->fetchFileInformation(false)
                            ->getUploadedFileUsing(fn (string $file, ?Expense $record): ?array => self::receiptPreview($file, $record))
                            ->maxSize(5120) // 5MB
                            ->preventFilePathTampering(),

                        Textarea::make('notes')
                            ->rows(3),

                        Grid::make(2)
                            ->components([
                                PercentageInput::make('business_percentage')
                                    ->label('Business Percentage')
                                    ->required()
                                    ->default(100),

                                MoneyInput::make('deductible_amount')
                                    ->label('Deductible Amount')
                                    ->disabled()
                                    ->dehydrated(false),
                            ]),
                    ]),
            ]);
    }

    /**
     * Describes a stored receipt for the upload field, pointing it at the authorised
     * receipt route because the private disk has no public URL.
     *
     * @return array{name: string, size: int, type: ?string, url: string}|null
     */
    private static function receiptPreview(string $file, ?Expense $record): ?array
    {
        if (! $record instanceof Expense) {
            return null;
        }

        return [
            'name' => basename($file),
            'size' => 0,
            'type' => MimeTypes::getDefault()->getMimeTypes(pathinfo($file, PATHINFO_EXTENSION))[0] ?? null,
            'url' => route('admin.expenses.receipt', $record),
        ];
    }
}
