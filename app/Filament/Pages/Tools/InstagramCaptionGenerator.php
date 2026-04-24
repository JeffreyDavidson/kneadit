<?php

namespace App\Filament\Pages\Tools;

use App\Enums\Content\CaptionStyle;
use App\Enums\Platform\SubscriptionTier;
use App\Filament\Concerns\RequiresManagerRole;
use App\Filament\Concerns\ShowsUpgradeBadge;
use App\Models\Inventory\Product;
use App\Services\Content\CaptionGeneratorService;
use BackedEnum;
use Filament\Actions\Action;
use Filament\Forms\Components\Select;
use Filament\Pages\Page;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Illuminate\Validation\Rules\Enum;
use Laravel\Pennant\Feature;

/**
 * @property-read Schema $form
 */
class InstagramCaptionGenerator extends Page
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

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedCamera;

    protected static string|\UnitEnum|null $navigationGroup = 'Tools';

    protected static ?int $navigationSort = 11;

    protected static ?string $navigationLabel = 'Instagram Captions';

    protected static ?string $title = 'Instagram Caption Generator';

    protected string $view = 'filament.pages.tools.instagram-caption-generator';

    /** @var array<string, mixed> */
    public ?array $data = [];

    /** @var array<int, array<string, mixed>> */
    public array $captions = [];

    public function mount(): void
    {
        $this->form->fill();
    }

    public function form(Schema $form): Schema
    {
        return $form
            ->schema([
                Select::make('product_id')
                    ->label('Select Product')
                    ->placeholder('Choose a product...')
                    ->options(Product::query()->active()->pluck('name', 'id'))
                    ->searchable()
                    ->required(),

                Select::make('style')
                    ->label('Caption Style')
                    ->options([
                        'playful' => 'Playful',
                        'professional' => 'Professional',
                        'seasonal' => 'Seasonal',
                        'storytelling' => 'Storytelling',
                    ])
                    ->required(),

                Select::make('tone')
                    ->label('Tone')
                    ->options([
                        'warm' => 'Warm',
                        'excited' => 'Excited',
                        'casual' => 'Casual',
                        'elegant' => 'Elegant',
                    ])
                    ->required(),
            ])
            ->statePath('data');
    }

    /** @return array<int, Action> */
    protected function getFormActions(): array
    {
        return [
            Action::make('generate')
                ->label('Generate Captions')
                ->action('generateCaptions')
                ->icon(Heroicon::OutlinedSparkles)
                ->color('primary')
                ->size('lg'),
        ];
    }

    public function generateCaptions(): void
    {
        $this->validate([
            'data.product_id' => ['required', 'exists:products,id'],
            'data.style' => ['required', new Enum(CaptionStyle::class)],
            'data.tone' => ['required', 'in:warm,excited,casual,elegant'],
        ]);

        $formData = $this->data ?? [];
        $product = Product::with('category')->where('id', $formData['product_id'] ?? null)->first();

        if (! $product) {
            return;
        }

        $this->captions = resolve(CaptionGeneratorService::class)->generate(
            $product,
            CaptionStyle::tryFrom($formData['style'] ?? '') ?? CaptionStyle::Playful,
            $formData['tone'] ?? 'warm',
        );
    }
}
