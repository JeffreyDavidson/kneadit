<?php

namespace App\Services\Filament;

use App\Console\Commands\Tenants\SeedDemoTenantCommand;
use App\Models\Platform\Tenant;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\HtmlString;
use Livewire\Livewire;
use Throwable;

/**
 * Renders tenant Livewire widgets against the curated demo tenant DB,
 * for design preview on the central panel without impersonation.
 *
 * Wraps Livewire::mount in tenancy()->run() so the widget's queries
 * hit the demo tenant's data; central session/auth survive untouched.
 */
class WidgetPreviewRenderer
{
    /**
     * Render the widget identified by its FQN. Returns rendered HTML,
     * or a placeholder if the demo tenant is missing or the widget throws.
     *
     * @param class-string $widgetClass
     */
    public function render(string $widgetClass): HtmlString
    {
        $demo = Tenant::query()->find(SeedDemoTenantCommand::DEMO_ID);

        if (! $demo) {
            return $this->placeholder(
                'Demo tenant not provisioned. Run <code>php artisan tenants:seed-demo</code>.',
            );
        }

        try {
            $html = $demo->run(fn (): string => Livewire::mount($widgetClass));

            return new HtmlString($html);
        } catch (Throwable $e) {
            Log::warning('WidgetPreviewRenderer failed', [
                'widget' => $widgetClass,
                'error' => $e->getMessage(),
            ]);

            return $this->placeholder('Widget render failed: ' . e($e->getMessage()));
        }
    }

    private function placeholder(string $message): HtmlString
    {
        return new HtmlString(
            '<div class="rounded-lg border border-amber-300 bg-amber-50 p-4 text-sm text-amber-900">'
            . $message
            . '</div>',
        );
    }
}
