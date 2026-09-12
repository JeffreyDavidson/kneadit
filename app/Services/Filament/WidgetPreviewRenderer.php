<?php

namespace App\Services\Filament;

use App\Models\Platform\Tenant;
use Illuminate\Contracts\View\Factory as ViewFactory;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\HtmlString;
use Livewire\Livewire;
use ReflectionClass;
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
        $demo = Tenant::query()->find(Tenant::DEMO_ID);

        if (! $demo) {
            return $this->placeholder(
                'Demo tenant not provisioned. Run <code>php artisan tenants:seed-demo</code>.',
            );
        }

        // Snapshot the View factory's component/slot stacks. A widget's blade
        // can leak partial state (an unbalanced startComponent without
        // endComponent, e.g.), and the leak only surfaces when the *parent*
        // template later hits @endforeach / @endcomponent and tries to pop
        // an empty slot — far from the original culprit. Restoring the
        // stacks after each render keeps the side effects contained.
        $factory = app(ViewFactory::class);
        $snapshot = [
            'componentStack' => $this->snapshot($factory, 'componentStack'),
            'slotStack' => $this->snapshot($factory, 'slotStack'),
            'componentData' => $this->snapshot($factory, 'componentData'),
        ];

        try {
            $html = $demo->run(fn (): string => Livewire::mount($widgetClass));

            if (! is_string($html)) {
                return $this->placeholder('Widget render returned an invalid response.');
            }

            return new HtmlString($html);
        } catch (Throwable $e) {
            Log::warning('WidgetPreviewRenderer failed', [
                'widget' => $widgetClass,
                'error' => $e->getMessage(),
            ]);

            return $this->placeholder('Widget preview is unavailable.');
        } finally {
            foreach ($snapshot as $property => $value) {
                $this->restore($factory, $property, $value);
            }
        }
    }

    private function snapshot(object $factory, string $property): mixed
    {
        $ref = new ReflectionClass($factory);
        if (! $ref->hasProperty($property)) {
            return null;
        }

        $prop = $ref->getProperty($property);
        $prop->setAccessible(true);

        return $prop->getValue($factory);
    }

    private function restore(object $factory, string $property, mixed $value): void
    {
        $ref = new ReflectionClass($factory);
        if (! $ref->hasProperty($property)) {
            return;
        }

        $prop = $ref->getProperty($property);
        $prop->setAccessible(true);
        $prop->setValue($factory, $value);
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
