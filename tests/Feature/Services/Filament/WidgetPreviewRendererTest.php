<?php

use App\Filament\Widgets\RecentOrdersWidget;
use App\Models\Platform\Tenant;
use App\Models\Staff\User;
use App\Services\Filament\BladeComponentStateSnapshot;
use App\Services\Filament\WidgetPreviewRenderer;
use Illuminate\Contracts\View\Factory as ViewFactory;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\HtmlString;
use ReflectionClass;
use RuntimeException;
use Tests\Support\Filament\FailingWidgetPreview;

use function Pest\Laravel\actingAs;

beforeEach(function () {
    setUpCentralTest();

    // Persistent local 'tenantdemo' SQLite (kept across test runs for the
    // central WidgetCatalog page) would conflict with the factory's
    // CreateDatabase job — wipe it before each test that recreates it.
    @unlink(database_path('tenantdemo'));
});

test('returns placeholder when demo tenant has not been provisioned', function () {
    $html = (new WidgetPreviewRenderer)->render(RecentOrdersWidget::class);

    expect($html)->toBeInstanceOf(HtmlString::class)
        ->and((string) $html)->toContain('Demo tenant not provisioned');
});

test('renders a real tenant widget against the demo tenant DB', function () {
    Tenant::factory()->onboarded()->create(['id' => Tenant::DEMO_ID]);

    $html = (new WidgetPreviewRenderer)->render(RecentOrdersWidget::class);

    expect($html)->toBeInstanceOf(HtmlString::class)
        ->and((string) $html)->not->toBeEmpty()
        ->and((string) $html)->not->toContain('Demo tenant not provisioned')
        ->and((string) $html)->not->toContain('Widget render failed');
});

test('central auth context survives a preview render', function () {
    $user = User::factory()->platformAdmin()->create();
    actingAs($user);
    Tenant::factory()->onboarded()->create(['id' => Tenant::DEMO_ID]);

    (new WidgetPreviewRenderer)->render(RecentOrdersWidget::class);

    expect(Auth::user()?->id)->toBe($user->id);
});

test('does not expose widget exceptions in the placeholder and logs server-side context', function () {
    Tenant::factory()->onboarded()->create(['id' => Tenant::DEMO_ID]);
    Log::shouldReceive('warning')
        ->once()
        ->withArgs(fn (string $message, array $context): bool => $message === 'WidgetPreviewRenderer failed'
            && $context['widget'] === FailingWidgetPreview::class
            && $context['exception'] instanceof RuntimeException
            && $context['exception']->getMessage() === 'database password leaked');

    $html = (new WidgetPreviewRenderer)->render(FailingWidgetPreview::class);

    expect((string) $html)
        ->toContain('Widget preview is unavailable.')
        ->not->toContain('database password leaked');

});

test('restores blade component state after a preview render', function () {
    $factory = app(ViewFactory::class);
    $reflection = new ReflectionClass($factory);
    $original = [];

    foreach (['componentStack', 'slotStack', 'componentData'] as $property) {
        $original[$property] = $reflection->getProperty($property)->getValue($factory);
    }

    $parentState = [
        'componentStack' => ['parent-component'],
        'slotStack' => ['parent-slot'],
        'componentData' => ['parent-data'],
    ];

    try {
        foreach ($parentState as $property => $value) {
            $reflection->getProperty($property)->setValue($factory, $value);
        }

        $snapshot = BladeComponentStateSnapshot::capture($factory);

        foreach (array_keys($parentState) as $property) {
            $reflection->getProperty($property)->setValue($factory, ['leaked state']);
        }

        $snapshot->restore();

        foreach ($parentState as $property => $value) {
            expect($reflection->getProperty($property)->getValue($factory))->toBe($value);
        }
    } finally {
        foreach ($original as $property => $value) {
            $reflection->getProperty($property)->setValue($factory, $value);
        }
    }
});
