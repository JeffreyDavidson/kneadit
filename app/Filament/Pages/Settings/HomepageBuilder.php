<?php

namespace App\Filament\Pages\Settings;

use App\Filament\Concerns\RequiresManagerRole;
use BackedEnum;
use Filament\Notifications\Notification;
use Filament\Pages\Page;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;

class HomepageBuilder extends Page
{
    use RequiresManagerRole;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedHome;

    protected static string|\UnitEnum|null $navigationGroup = 'Settings';

    protected static ?string $navigationLabel = 'Homepage';

    protected static ?int $navigationSort = 10;

    protected static ?string $title = 'Homepage Builder';

    protected string $view = 'filament.pages.settings.homepage-builder';

    /** @var array<string, mixed> */
    public array $sections = [];

    /** @var array<string, mixed> */
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
    }

    /** @return array<string, mixed> */
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
        $saved = json_decode(settings('homepage_sections', '{}'), true);
        $defaults = $this->getDefaults();

        // Merge saved with defaults to ensure all sections exist
        $this->sections = [];
        foreach ($defaults as $key => $default) {
            $this->sections[$key] = array_merge($default, $saved[$key] ?? []);
        }
    }

    public function content(Schema $schema): Schema
    {
        return $schema->schema([]);
    }

    public function toggleVisibility(string $key): void
    {
        $this->sections[$key]['visible'] = ! ($this->sections[$key]['visible'] ?? true);
    }

    public function moveUp(string $key): void
    {
        $sorted = collect($this->sections)->sortBy('order')->keys()->values()->all();
        $index = array_search($key, $sorted);
        if ($index > 0) {
            $swapKey = $sorted[$index - 1];
            $tempOrder = $this->sections[$key]['order'];
            $this->sections[$key]['order'] = $this->sections[$swapKey]['order'];
            $this->sections[$swapKey]['order'] = $tempOrder;
        }
    }

    public function moveDown(string $key): void
    {
        $sorted = collect($this->sections)->sortBy('order')->keys()->values()->all();
        $index = array_search($key, $sorted);
        if ($index < count($sorted) - 1) {
            $swapKey = $sorted[$index + 1];
            $tempOrder = $this->sections[$key]['order'];
            $this->sections[$key]['order'] = $this->sections[$swapKey]['order'];
            $this->sections[$swapKey]['order'] = $tempOrder;
        }
    }

    public function updateSectionField(string $key, string $field, mixed $value): void
    {
        $this->sections[$key][$field] = $value;
    }

    public function save(): void
    {
        try {
            settings(['homepage_sections' => json_encode($this->sections)]);

            Notification::make()
                ->title('Homepage sections saved!')
                ->success()
                ->send();
        } catch (\Exception $e) {
            Notification::make()
                ->title('Error saving homepage sections')
                ->body($e->getMessage())
                ->danger()
                ->send();
        }
    }

    public function resetToDefaults(): void
    {
        $this->sections = $this->getDefaults();
        settings(['homepage_sections' => json_encode($this->sections)]);

        Notification::make()
            ->title('Homepage reset to defaults')
            ->info()
            ->send();
    }

    /** @return array<string, mixed> */
    public function getSortedSections(): array
    {
        return collect($this->sections)
            ->sortBy('order')
            ->toArray();
    }

    /** @return array<string, mixed> */
    public function getSectionMeta(string $key): array
    {
        return $this->sectionMeta[$key] ?? ['label' => ucfirst($key), 'description' => ''];
    }
}
