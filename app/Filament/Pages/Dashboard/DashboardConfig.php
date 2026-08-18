<?php

namespace App\Filament\Pages\Dashboard;

use App\Enums\Filament\WidgetSize;
use App\Filament\Concerns\RequiresManagerRole;
use App\Filament\Shared\Dashboard\WidgetMeta;
use App\Services\Settings\SettingsManager;
use Filament\Notifications\Notification;
use Filament\Pages\Page;
use Filament\Support\Icons\Heroicon;

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

    /** @var array<int, mixed> */
    public array $widgets = [];

    public bool $showPreview = false;

    public function mount(): void
    {
        $this->loadWidgets();
    }

    protected function loadWidgets(): void
    {
        $widgetMeta = WidgetMeta::all();
        $saved = resolve(SettingsManager::class)->get('dashboard_widgets');
        $config = $saved ? json_decode($saved, true) : null;

        if (! $config) {
            $config = $this->getDefaults();
        }

        uasort($config, fn (array $a, array $b) => ($a['order'] ?? 99) <=> ($b['order'] ?? 99));

        $this->widgets = [];
        foreach ($config as $key => $settings) {
            if (! isset($widgetMeta[$key])) {
                continue;
            }
            $this->widgets[] = [
                'key' => $key,
                'visible' => $settings['visible'] ?? true,
                'size' => $this->resolveSize($key, $settings, $widgetMeta[$key]),
                'name' => $widgetMeta[$key]['name'],
                'description' => $widgetMeta[$key]['description'],
                'icon' => $widgetMeta[$key]['icon'],
            ];
        }

        // Add any missing widgets
        foreach ($widgetMeta as $key => $meta) {
            if (! collect($this->widgets)->where('key', $key)->count()) {
                $this->widgets[] = [
                    'key' => $key,
                    'visible' => true,
                    'size' => ($meta['defaultSize'] ?? WidgetSize::Small)->value,
                    'name' => $meta['name'],
                    'description' => $meta['description'],
                    'icon' => $meta['icon'],
                ];
            }
        }
    }

    /**
     * Resolve the size for a widget from its saved settings, with a
     * fall-back chain that handles legacy integer span values from
     * older saved configs AND clamps disallowed sizes to the widget's
     * default (a saved 'sm' for welcome_banner, e.g., gets clamped
     * to 'lg' since allowedSizes won't include sm anymore).
     *
     * @param array<string, mixed> $settings
     * @param array<string, mixed> $meta
     */
    private function resolveSize(string $key, array $settings, array $meta): string
    {
        $resolved = match (true) {
            isset($settings['size']) && WidgetSize::tryFrom((string) $settings['size']) !== null => WidgetSize::tryFrom((string) $settings['size']),

            isset($settings['span']) => WidgetSize::fromLegacySpan((int) $settings['span']),

            default => $meta['defaultSize'] ?? WidgetSize::Small,
        };

        $allowed = WidgetMeta::allowedSizesFor($key);

        if (! in_array($resolved, $allowed, true)) {
            $resolved = $meta['defaultSize'] ?? $allowed[0] ?? WidgetSize::Small;
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
                'size' => $widget['size'] ?? WidgetSize::Small->value,
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

    /** @return array<string, mixed> */
    protected function getDefaults(): array
    {
        $defaults = [];
        $i = 1;
        foreach (WidgetMeta::all() as $key => $meta) {
            $defaults[$key] = ['visible' => true, 'order' => $i++];
        }

        return $defaults;
    }
}
