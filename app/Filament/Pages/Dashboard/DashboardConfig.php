<?php

namespace App\Filament\Pages\Dashboard;

use App\Enums\Filament\WidgetSize;
use App\Filament\Concerns\RequiresManagerRole;
use App\Filament\Shared\Dashboard\WidgetDefinition;
use App\Filament\Shared\Dashboard\WidgetMeta;
use App\Services\Settings\SettingsManager;
use Filament\Notifications\Notification;
use Filament\Pages\Page;
use Filament\Support\Icons\Heroicon;

/**
 * @phpstan-type SavedWidgetSettings array{visible: bool, order: int, size?: string, span?: int}
 * @phpstan-type SavedDashboardConfig array<string, SavedWidgetSettings>
 * @phpstan-type ConfigurableWidget array{key: string, visible: bool, size: string, name: string, description: string, icon: \BackedEnum|string}
 */
class DashboardConfig extends Page
{
    use RequiresManagerRole;

    protected static \BackedEnum|string|null $navigationIcon = Heroicon::OutlinedCog6Tooth;

    protected static ?string $navigationLabel = 'Dashboard Configuration';

    protected static ?string $title = 'Customize Dashboard';

    protected static ?string $slug = 'dashboard-config';

    protected static \UnitEnum|string|null $navigationGroup = 'Settings';

    protected static ?int $navigationSort = 7;

    protected string $view = 'filament.pages.dashboard.dashboard-config';

    /** @var list<ConfigurableWidget> */
    public array $widgets = [];

    public bool $showPreview = false;

    public function mount(): void
    {
        $this->loadWidgets();
    }

    protected function loadWidgets(): void
    {
        $widgetMeta = WidgetMeta::all();
        $config = $this->getSavedDashboardConfig();

        if ($config === null || $config === []) {
            $config = $this->getDefaults();
        }

        uasort($config, fn (array $a, array $b) => $a['order'] <=> $b['order']);

        $this->widgets = [];
        foreach ($config as $key => $settings) {
            $meta = $widgetMeta[$key] ?? null;

            if ($meta === null) {
                continue;
            }

            $this->widgets[] = [
                'key' => $key,
                'visible' => $settings['visible'],
                'size' => $this->resolveSize($key, $settings, $meta),
                'name' => $meta->name,
                'description' => $meta->description,
                'icon' => $meta->icon,
            ];
        }

        // Add any missing widgets
        foreach ($widgetMeta as $key => $meta) {
            if (! collect($this->widgets)->contains('key', $key)) {
                $this->widgets[] = [
                    'key' => $key,
                    'visible' => true,
                    'size' => $meta->defaultSize->value,
                    'name' => $meta->name,
                    'description' => $meta->description,
                    'icon' => $meta->icon,
                ];
            }
        }
    }

    /**
     * Resolve the size for a widget from its saved settings, with a
     * fall-back chain that handles legacy integer span values from
     * older saved configs AND clamps disallowed sizes to the widget's
     * default (a saved 'xl' for welcome_banner, e.g., gets clamped
     * to 'sm' since allowedSizes doesn't include xl).
     *
     * @param SavedWidgetSettings $settings
     */
    private function resolveSize(string $key, array $settings, WidgetDefinition $meta): string
    {
        $resolved = match (true) {
            isset($settings['size']) && WidgetSize::tryFrom($settings['size']) !== null => WidgetSize::tryFrom($settings['size']),

            isset($settings['span']) => WidgetSize::fromLegacySpan($settings['span']),

            default => $meta->defaultSize,
        };

        $allowed = WidgetMeta::allowedSizesFor($key);

        if (! in_array($resolved, $allowed, true)) {
            $resolved = $meta->defaultSize;

            if (! in_array($resolved, $allowed, true)) {
                $resolved = $allowed[0] ?? WidgetSize::Small;
            }
        }

        return $resolved->value;
    }

    public function reorder(int $oldIndex, int $newIndex): void
    {
        $item = $this->widgets[$oldIndex];
        array_splice($this->widgets, $oldIndex, 1);
        array_splice($this->widgets, $newIndex, 0, [$item]);
        // widgets is already a list
    }

    public function togglePreview(): void
    {
        $this->showPreview = ! $this->showPreview;
    }

    public function setSize(int $index, string $size): void
    {
        $widget = $this->widgets[$index] ?? null;
        if ($widget === null) {
            return;
        }

        $resolved = WidgetSize::tryFrom($size) ?? WidgetSize::Small;
        $allowed = WidgetMeta::allowedSizesFor($widget['key']);

        if (! in_array($resolved, $allowed, true)) {
            return; // Disallowed for this widget — keep existing size.
        }

        $this->widgets[$index]['size'] = $resolved->value;
    }

    public function toggleWidget(int $index): void
    {
        $this->widgets[$index]['visible'] = ! $this->widgets[$index]['visible'];
    }

    public function save(): void
    {
        $config = [];
        foreach ($this->widgets as $i => $widget) {
            $config[$widget['key']] = [
                'visible' => $widget['visible'],
                'order' => $i + 1,
                'size' => $widget['size'],
            ];
        }

        resolve(SettingsManager::class)->set('dashboard_widgets', json_encode($config));

        Notification::make()
            ->title('Dashboard layout saved!')
            ->body('Your dashboard will reflect these changes immediately.')
            ->success()
            ->send();
    }

    public function resetDefaults(): void
    {
        resolve(SettingsManager::class)->setMany([
            'dashboard_widgets' => json_encode($this->getDefaults()),
            'dashboard_grid_layout' => null,
        ]);
        $this->loadWidgets();

        Notification::make()
            ->title('Dashboard reset to defaults')
            ->success()
            ->send();
    }

    /** @return SavedDashboardConfig */
    protected function getDefaults(): array
    {
        $defaults = [];
        $i = 1;
        foreach (WidgetMeta::all() as $key => $meta) {
            $defaults[$key] = ['visible' => ! WidgetMeta::isDefaultHidden($key), 'order' => $i++];
        }

        return $defaults;
    }

    /** @return SavedDashboardConfig|null */
    private function getSavedDashboardConfig(): ?array
    {
        $saved = resolve(SettingsManager::class)->get('dashboard_widgets');

        if (! is_string($saved) || $saved === '') {
            return null;
        }

        $decoded = json_decode($saved, true);

        if (! is_array($decoded)) {
            return null;
        }

        $config = [];

        foreach ($decoded as $key => $settings) {
            if (! is_string($key)) {
                continue;
            }
            if (! is_array($settings)) {
                continue;
            }
            $normalized = [
                'visible' => is_bool($settings['visible'] ?? null) ? $settings['visible'] : true,
                'order' => is_int($settings['order'] ?? null) ? $settings['order'] : 99,
            ];

            if (is_string($settings['size'] ?? null)) {
                $normalized['size'] = $settings['size'];
            }

            if (is_int($settings['span'] ?? null)) {
                $normalized['span'] = $settings['span'];
            }

            $config[$key] = $normalized;
        }

        return $config;
    }
}
