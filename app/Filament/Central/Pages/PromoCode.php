<?php

namespace App\Filament\Central\Pages;

use App\Actions\Stripe\CreateStripePromotionCode;
use App\DataTransferObjects\Stripe\StripePromotionCodeResult;
use App\Models\Platform\PlatformPromoCode;
use App\Models\Platform\Tenant;
use BackedEnum;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Forms\Contracts\HasForms;
use Filament\Notifications\Notification;
use Filament\Pages\Page;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Components\Utilities\Get;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Illuminate\Support\Arr;
use Illuminate\Support\Collection;
use InvalidArgumentException;
use Stripe\Exception\ApiErrorException;
use UnitEnum;

/**
 * @property-read Schema $form
 */
class PromoCode extends Page implements HasForms
{
    use InteractsWithForms;

    #[\Override]
    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedTicket;

    #[\Override]
    protected static string|UnitEnum|null $navigationGroup = 'Settings';

    #[\Override]
    protected static ?int $navigationSort = 5;

    #[\Override]
    protected static ?string $title = 'Promo Codes';

    #[\Override]
    protected static ?string $navigationLabel = 'Promo Codes';

    #[\Override]
    protected string $view = 'filament.central.pages.promo-code';

    /** @var array<string, mixed> */
    public array $data = [];

    public ?StripePromotionCodeResult $result = null;

    public function mount(): void
    {
        $this->form->fill([
            'discount_type' => 'percent',
            'duration' => 'once',
            'max_redemptions' => 1,
        ]);
    }

    public function form(Schema $schema): Schema
    {
        return $schema
            ->statePath('data')
            ->components([
                Section::make('Discount')
                    ->description('How much off and for how long')
                    ->icon(Heroicon::OutlinedReceiptPercent)
                    ->columnSpanFull()
                    ->schema([
                        Grid::make(2)->schema([
                            Select::make('discount_type')
                                ->label('Discount Type')
                                ->options([
                                    'percent' => 'Percent off',
                                    'amount' => 'Fixed amount off ($)',
                                ])
                                ->required()
                                ->live()
                                ->default('percent'),
                            TextInput::make('discount_value')
                                ->label(fn (Get $get): string => $get('discount_type') === 'amount' ? 'Amount Off (USD)' : 'Percent Off')
                                ->numeric()
                                ->required()
                                ->minValue(1)
                                ->maxValue(fn (Get $get): ?int => $get('discount_type') === 'percent' ? 100 : null)
                                ->suffix(fn (Get $get): string => $get('discount_type') === 'amount' ? 'USD' : '%')
                                ->helperText(fn (Get $get): string => $get('discount_type') === 'percent'
                                    ? 'Whole number 1-100.'
                                    : 'Whole dollars (e.g. 25 = $25 off).'),
                        ]),
                        Grid::make(2)->schema([
                            Select::make('duration')
                                ->options([
                                    'once' => 'Once (single billing cycle)',
                                    'repeating' => 'Repeating (N months)',
                                    'forever' => 'Forever',
                                ])
                                ->required()
                                ->live()
                                ->default('once'),
                            TextInput::make('duration_in_months')
                                ->label('Duration in Months')
                                ->numeric()
                                ->minValue(1)
                                ->required(fn (Get $get): bool => $get('duration') === 'repeating')
                                ->visible(fn (Get $get): bool => $get('duration') === 'repeating')
                                ->helperText('How many monthly invoices the discount applies to.'),
                        ]),
                    ]),

                Section::make('Code & Limits')
                    ->description('What customers type at checkout')
                    ->icon(Heroicon::OutlinedTag)
                    ->columnSpanFull()
                    ->schema([
                        Grid::make(2)->schema([
                            TextInput::make('code')
                                ->label('Promo Code')
                                ->placeholder('SUMMER25')
                                ->helperText('Leave blank for a Stripe-generated code.')
                                ->maxLength(64),
                            TextInput::make('name')
                                ->label('Internal Name')
                                ->placeholder('Summer 2026 Promo')
                                ->helperText('Shown in the Stripe dashboard. Optional.')
                                ->maxLength(120),
                        ]),
                        Grid::make(2)->schema([
                            TextInput::make('max_redemptions')
                                ->label('Max Redemptions')
                                ->numeric()
                                ->minValue(1)
                                ->required()
                                ->default(1)
                                ->helperText('How many customers can use this code.'),
                            TextInput::make('expires_in_days')
                                ->label('Expires In (days)')
                                ->numeric()
                                ->minValue(1)
                                ->placeholder('30')
                                ->helperText('Leave blank for no expiry.'),
                        ]),
                    ]),

                Section::make('Tagging')
                    ->description('Optional reconciliation metadata')
                    ->icon(Heroicon::OutlinedBuildingStorefront)
                    ->columnSpanFull()
                    ->schema([
                        Select::make('tenant_id')
                            ->label('Tenant')
                            ->placeholder('— None —')
                            ->options(fn () => Tenant::query()->orderBy('store_name')
                                ->get()
                                ->mapWithKeys(fn (Tenant $t): array => [$t->id => ($t->store_name ?: $t->name).' ('.$t->id.')'])
                                ->all())
                            ->searchable()
                            ->helperText('Stamps tenant_id into the Stripe coupon metadata for later reconciliation.'),
                    ]),
            ]);
    }

    public function generate(CreateStripePromotionCode $action): void
    {
        $state = $this->form->getState();

        $this->result = null;

        try {
            $isPercent = ($state['discount_type'] ?? 'percent') === 'percent';
            $value = $this->optionalInteger($state, 'discount_value');
            $duration = Arr::string($state, 'duration', 'once');
            $durationInMonths = $this->optionalInteger($state, 'duration_in_months');
            $maxRedemptions = $this->optionalInteger($state, 'max_redemptions') ?? 1;
            $expiresInDays = $this->optionalInteger($state, 'expires_in_days');
            $code = $this->optionalString($state, 'code');
            $tenantId = $this->optionalString($state, 'tenant_id');
            $name = $this->optionalString($state, 'name');

            $this->result = $action(
                percentOff: $isPercent ? $value : null,
                amountOffCents: ! $isPercent && $value !== null ? $value * 100 : null,
                duration: $duration,
                durationInMonths: $durationInMonths,
                code: $code,
                maxRedemptions: $maxRedemptions,
                expiresInDays: $expiresInDays,
                tenantId: $tenantId,
                name: $name,
            );

            PlatformPromoCode::query()->create([
                'code' => $this->result->code,
                'coupon_id' => $this->result->couponId,
                'promotion_code_id' => $this->result->promotionCodeId,
                'percent_off' => $isPercent ? $value : null,
                'amount_off_cents' => ! $isPercent && $value !== null ? $value * 100 : null,
                'duration' => $duration,
                'duration_in_months' => $duration === 'repeating' ? $durationInMonths : null,
                'max_redemptions' => $maxRedemptions,
                'expires_at' => $expiresInDays !== null ? now()->addDays($expiresInDays) : null,
                'tenant_id' => $tenantId,
                'name' => $name,
                'created_by_user_id' => auth()->id(),
            ]);

            Notification::make()
                ->title('Promo code created')
                ->body("Code: {$this->result->code}")
                ->success()
                ->send();
        } catch (InvalidArgumentException $e) {
            Notification::make()
                ->title('Invalid input')
                ->body($e->getMessage())
                ->danger()
                ->send();
        } catch (ApiErrorException $e) {
            Notification::make()
                ->title('Stripe rejected the request')
                ->body($e->getMessage())
                ->danger()
                ->send();
        }
    }

    /** @param array<string, mixed> $state */
    private function optionalString(array $state, string $key): ?string
    {
        return filled($state[$key] ?? null) ? Arr::string($state, $key) : null;
    }

    /** @param array<string, mixed> $state */
    private function optionalInteger(array $state, string $key): ?int
    {
        $value = $state[$key] ?? null;

        // Numeric inputs arrive as ints, floats or numeric strings; blanks are null or ''.
        return is_numeric($value) ? (int) $value : null;
    }

    /** @return Collection<int, PlatformPromoCode> */
    public function getRecentCodes(): Collection
    {
        return PlatformPromoCode::query()
            ->with(['createdBy', 'tenant'])
            ->latest()
            ->limit(20)
            ->get();
    }
}
