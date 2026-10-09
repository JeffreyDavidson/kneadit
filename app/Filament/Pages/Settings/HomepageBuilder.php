<?php

namespace App\Filament\Pages\Settings;

use App\DataTransferObjects\Settings\SettingValue;
use App\Enums\Storefront\HeroStyle;
use App\Enums\Storefront\StorefrontHeroImage;
use App\Filament\Concerns\RequiresManagerRole;
use App\Filament\Support\AllowedFileTypes;
use App\Services\Settings\SettingsManager;
use App\Services\Settings\TenantSettings;
use BackedEnum;
use Filament\Actions\Action;
use Filament\Notifications\Notification;
use Filament\Pages\Page;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Illuminate\Support\Facades\Exceptions;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rule;
use Livewire\Features\SupportFileUploads\TemporaryUploadedFile;

/**
 * @phpstan-type HomepageSection array{visible: bool, order: int, ...<string, mixed>}
 * @phpstan-type SectionMeta array{label: string, description: string}
 */
class HomepageBuilder extends Page
{
    use RequiresManagerRole;

    #[\Override]
    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedHome;

    #[\Override]
    protected static string|\UnitEnum|null $navigationGroup = 'Settings';

    #[\Override]
    protected static ?string $navigationLabel = 'Homepage';

    #[\Override]
    protected static ?int $navigationSort = 10;

    #[\Override]
    protected static ?string $title = 'Homepage Builder';

    #[\Override]
    protected string $view = 'filament.pages.settings.homepage-builder';

    /** @var array<string, HomepageSection> */
    public array $sections = [];

    public ?string $hero_tagline = null;

    public string $hero_primary_cta_text = '';

    public string $hero_secondary_cta_text = '';

    public string $hero_style = 'split';

    /**
     * Pending hero image uploads, keyed by the setting they will replace.
     *
     * @var array<string, TemporaryUploadedFile>
     */
    public array $heroUploads = [];

    private const int MAX_HERO_IMAGE_KILOBYTES = 5120;

    /** @var array<string, SectionMeta> */
    protected array $sectionMeta = [
        'hero' => ['label' => 'Hero Banner', 'description' => 'Full-screen welcome banner with store name and tagline'],
        'about' => ['label' => 'About Section', 'description' => 'Brief about text for your bakery'],
        'featured_products' => ['label' => 'Featured Products', 'description' => 'Grid of your top products'],
        'categories' => ['label' => 'Categories', 'description' => 'Browse by product category'],
        'reviews' => ['label' => 'Customer Reviews', 'description' => 'Testimonials from happy customers'],
        'gallery' => ['label' => 'Customer Gallery', 'description' => 'Photos shared by your community'],
        'blog' => ['label' => 'Blog Posts', 'description' => 'Latest updates from your kitchen'],
        'cta' => ['label' => 'Call to Action', 'description' => 'Prompt visitors to place an order'],
        'social' => ['label' => 'Social Links', 'description' => 'Social media follow buttons'],
    ];

    public function mount(): void
    {
        $this->loadSections();
        $this->loadHeroContent();
    }

    protected function loadHeroContent(): void
    {
        $branding = resolve(TenantSettings::class)->branding;
        $this->hero_tagline = $branding->heroTagline;
        $this->hero_primary_cta_text = $branding->heroPrimaryCtaText;
        $this->hero_secondary_cta_text = $branding->heroSecondaryCtaText;
        $this->hero_style = (HeroStyle::tryFrom($branding->heroStyle) ?? HeroStyle::Split)->value;
    }

    /** @return array<string, HomepageSection> */
    protected function getDefaults(): array
    {
        return [
            'hero' => ['visible' => true, 'order' => 1],
            'about' => ['visible' => true, 'order' => 2],
            'featured_products' => ['visible' => true, 'order' => 3, 'count' => 6, 'title' => 'Our Favorites', 'subtitle' => 'Freshly made'],
            'categories' => ['visible' => true, 'order' => 4, 'title' => 'What We Bake', 'subtitle' => 'Something for everyone'],
            'reviews' => ['visible' => true, 'order' => 5, 'count' => 3, 'title' => 'Kind Words', 'subtitle' => 'What our customers say'],
            'gallery' => ['visible' => true, 'order' => 6, 'count' => 4, 'title' => 'Customer Gallery', 'subtitle' => 'Shared by our community'],
            'blog' => ['visible' => true, 'order' => 7, 'count' => 3, 'title' => 'Latest Updates', 'subtitle' => 'From our kitchen'],
            'cta' => ['visible' => true, 'order' => 8, 'heading' => 'Treat Yourself Today', 'button_text' => 'Start Your Order'],
            'social' => ['visible' => true, 'order' => 9],
        ];
    }

    protected function loadSections(): void
    {
        $saved = resolve(TenantSettings::class)->homepage->sections;
        $defaults = $this->getDefaults();

        // Merge saved with defaults to ensure all sections exist
        $this->sections = [];
        foreach ($defaults as $key => $default) {
            $section = array_merge($default, $saved[$key] ?? []);
            $section['visible'] = is_bool($section['visible'] ?? null) ? $section['visible'] : $default['visible'];
            $section['order'] = is_int($section['order'] ?? null) ? $section['order'] : $default['order'];
            $this->sections[$key] = $section;
        }
    }

    #[\Override]
    public function content(Schema $schema): Schema
    {
        return $schema->schema([]);
    }

    public function toggleVisibility(string $key): void
    {
        if (! isset($this->sections[$key])) {
            return;
        }

        $this->sections[$key]['visible'] = ! $this->sections[$key]['visible'];
    }

    public function moveUp(string $key): void
    {
        $sorted = array_keys($this->getSortedSections());
        $index = array_search($key, $sorted);

        if (is_int($index) && $index > 0) {
            $swapKey = $sorted[$index - 1];
            $this->swapSectionOrder($key, $swapKey);
        }
    }

    public function moveDown(string $key): void
    {
        $sorted = array_keys($this->getSortedSections());
        $index = array_search($key, $sorted);

        if (is_int($index) && $index < count($sorted) - 1) {
            $swapKey = $sorted[$index + 1];
            $this->swapSectionOrder($key, $swapKey);
        }
    }

    public function updateSectionField(string $key, string $field, mixed $value): void
    {
        if (! isset($this->sections[$key])) {
            return;
        }

        if ($field === 'count') {
            $count = filter_var($value, FILTER_VALIDATE_INT);

            if (is_int($count)) {
                $this->sections[$key]['count'] = $count;
            }

            return;
        }

        if (! is_string($value)) {
            return;
        }

        match ($field) {
            'title' => $this->sections[$key]['title'] = $value,
            'subtitle' => $this->sections[$key]['subtitle'] = $value,
            'heading' => $this->sections[$key]['heading'] = $value,
            'subtext' => $this->sections[$key]['subtext'] = $value,
            'button_text' => $this->sections[$key]['button_text'] = $value,
            'button_link' => $this->sections[$key]['button_link'] = $value,
            default => null,
        };
    }

    public function save(): void
    {
        $this->validate($this->heroRules());

        try {
            $replacedPaths = [];
            $heroImages = [];

            foreach (StorefrontHeroImage::cases() as $image) {
                $upload = $this->heroUploads[$image->value] ?? null;

                if (! $upload instanceof TemporaryUploadedFile) {
                    continue;
                }

                $path = $upload->store($this->heroDirectory(), 'public');

                throw_if($path === false, \RuntimeException::class, 'The hero image could not be stored.');

                $replacedPaths[] = SettingValue::nullableString(settings($image->value));
                $heroImages[$image->value] = $path;
            }

            resolve(SettingsManager::class)->setMany([
                'homepage_sections' => json_encode($this->sections),
                'hero_tagline' => $this->hero_tagline,
                'hero_primary_cta_text' => $this->hero_primary_cta_text,
                'hero_secondary_cta_text' => $this->hero_secondary_cta_text,
                'hero_style' => $this->hero_style,
                ...$heroImages,
            ]);

            array_map($this->deleteStoredHeroImage(...), $replacedPaths);
            $this->heroUploads = [];

            Notification::make()
                ->title('Homepage sections saved!')
                ->success()
                ->send();
        } catch (\Exception $e) {
            Exceptions::report($e);

            Notification::make()
                ->title('Error saving homepage sections')
                ->body('Please try again. If the problem continues, contact support.')
                ->danger()
                ->send();
        }
    }

    public function removeHeroImage(string $setting): void
    {
        $image = StorefrontHeroImage::tryFrom($setting);

        if ($image === null) {
            return;
        }

        $previousPath = SettingValue::nullableString(settings($image->value));

        resolve(SettingsManager::class)->set($image->value, null);
        unset($this->heroUploads[$image->value]);
        $this->deleteStoredHeroImage($previousPath);

        Notification::make()
            ->title("{$image->getLabel()} removed")
            ->success()
            ->send();
    }

    public function heroImagePreviewUrl(StorefrontHeroImage $image): ?string
    {
        $upload = $this->heroUploads[$image->value] ?? null;

        if ($upload instanceof TemporaryUploadedFile && $upload->isPreviewable()) {
            return SettingValue::nullableString($upload->temporaryUrl());
        }

        $path = SettingValue::nullableString(settings($image->value));

        return $path === null ? null : Storage::disk('public')->url($path);
    }

    public function hasStoredHeroImage(StorefrontHeroImage $image): bool
    {
        return SettingValue::nullableString(settings($image->value)) !== null;
    }

    public function resetToDefaultsAction(): Action
    {
        return Action::make('resetToDefaults')
            ->label('Reset to Defaults')
            ->icon(Heroicon::OutlinedArrowPath)
            ->color('gray')
            ->requiresConfirmation()
            ->modalHeading('Reset homepage to defaults?')
            ->modalDescription('This restores every section to its default visibility, order, and copy. Any customizations you have saved will be overwritten immediately.')
            ->modalSubmitActionLabel('Reset')
            ->action(fn () => $this->resetToDefaults());
    }

    public function resetToDefaults(): void
    {
        $this->sections = $this->getDefaults();
        $this->hero_tagline = 'Where every bite tells a story';
        $this->hero_primary_cta_text = 'Order Now';
        $this->hero_secondary_cta_text = 'Browse Menu';

        resolve(SettingsManager::class)->setMany([
            'homepage_sections' => json_encode($this->sections),
            'hero_tagline' => $this->hero_tagline,
            'hero_primary_cta_text' => $this->hero_primary_cta_text,
            'hero_secondary_cta_text' => $this->hero_secondary_cta_text,
        ]);

        Notification::make()
            ->title('Homepage reset to defaults')
            ->info()
            ->send();
    }

    /** @return array<string, HomepageSection> */
    public function getSortedSections(): array
    {
        $sections = $this->sections;
        uasort($sections, fn (array $first, array $second): int => $first['order'] <=> $second['order']);

        return $sections;
    }

    /** @return SectionMeta */
    public function getSectionMeta(string $key): array
    {
        return $this->sectionMeta[$key] ?? ['label' => ucfirst($key), 'description' => ''];
    }

    /** @return array<string, array<int, mixed>> */
    private function heroRules(): array
    {
        $allowedKeys = implode(',', array_column(StorefrontHeroImage::cases(), 'value'));
        $mimeTypes = implode(',', AllowedFileTypes::IMAGES);
        $maxKilobytes = self::MAX_HERO_IMAGE_KILOBYTES;

        return [
            'hero_style' => ['required', Rule::enum(HeroStyle::class)],
            'heroUploads' => ['array', "array:{$allowedKeys}"],
            'heroUploads.*' => ['file', 'image', "mimetypes:{$mimeTypes}", "max:{$maxKilobytes}"],
        ];
    }

    private function heroDirectory(): string
    {
        $tenantId = tenant('id');

        return is_string($tenantId)
            ? "tenants/{$tenantId}/storefront-heroes"
            : 'storefront-heroes';
    }

    /**
     * Only files this page uploaded are ever deleted, so a stored path that points
     * elsewhere (legacy imports, other uploads) is left alone.
     */
    private function deleteStoredHeroImage(?string $path): void
    {
        if ($path === null || str_contains($path, '..')) {
            return;
        }

        if (! str_starts_with($path, "{$this->heroDirectory()}/")) {
            return;
        }

        Storage::disk('public')->delete($path);
    }

    private function swapSectionOrder(string $firstKey, string $secondKey): void
    {
        $first = $this->sections[$firstKey] ?? null;
        $second = $this->sections[$secondKey] ?? null;

        if ($first === null || $second === null) {
            return;
        }

        $firstOrder = $first['order'];
        $first['order'] = $second['order'];
        $second['order'] = $firstOrder;

        $this->sections[$firstKey] = $first;
        $this->sections[$secondKey] = $second;
    }
}
