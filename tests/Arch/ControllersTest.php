<?php

declare(strict_types=1);
use App\Http\Controllers\Central\ConsumeImpersonationController;
use App\Http\Controllers\Central\ImpersonateController;
use App\Http\Controllers\Controller;

$resourceMethods = ['index', 'create', 'store', 'show', 'edit', 'update', 'destroy'];

$controllerFiles = collect(iterator_to_array(new RecursiveIteratorIterator(
    new RecursiveDirectoryIterator(__DIR__.'/../../app/Http/Controllers', FilesystemIterator::SKIP_DOTS),
)))
    ->filter(fn (SplFileInfo $file): bool => $file->getExtension() === 'php')
    ->map(fn (SplFileInfo $file): string => $file->getPathname())
    ->map(function (string $file): string {
        $relative = str_replace(__DIR__.'/../../app/', '', $file);

        return str_replace(['/', '.php'], ['\\', ''], 'App\\'.$relative);
    })
    ->reject(fn ($class) => $class === Controller::class)
    ->filter(fn ($class) => class_exists($class))
    ->reject(fn ($class): bool => (new ReflectionClass($class))->isAbstract())
    ->values();

foreach ($controllerFiles as $controllerClass) {
    $shortName = str_replace('App\\Http\\Controllers\\', '', $controllerClass);

    test("{$shortName} is invokable or resourceful", function () use ($controllerClass, $resourceMethods) {
        $reflection = new ReflectionClass($controllerClass);

        $publicMethods = collect($reflection->getMethods(ReflectionMethod::IS_PUBLIC))
            ->reject(fn (ReflectionMethod $method) => $method->class !== $reflection->getName())
            ->reject(fn (ReflectionMethod $method) => $method->isStatic())
            ->reject(fn (ReflectionMethod $method) => str_starts_with($method->getName(), '__') && $method->getName() !== '__invoke')
            ->map(fn (ReflectionMethod $method) => $method->getName())
            ->values()
            ->all();

        $isInvokable = $publicMethods === ['__invoke'];
        $isResourceful = empty(array_diff($publicMethods, $resourceMethods));

        expect($isInvokable || $isResourceful)->toBeTrue(
            'Must be invokable or resourceful. Found: '.implode(', ', $publicMethods),
        );
    });
}

arch('controllers should be classes')
    ->expect('App\Http\Controllers')
    ->toBeClasses()
    ->ignoring('App\Http\Controllers\Stripe\Concerns');

arch('controllers should not use DB facade directly')
    ->expect('Illuminate\Support\Facades\DB')
    ->not->toBeUsedIn('App\Http\Controllers')
    ->ignoring([
        ImpersonateController::class,
        ConsumeImpersonationController::class,
    ]);

arch('controllers should not invoke tenancy middleware directly')
    ->expect('Stancl\Tenancy\Middleware\InitializeTenancyByDomainOrSubdomain')
    ->not->toBeUsedIn('App\Http\Controllers');

foreach ($controllerFiles as $controllerClass) {
    $shortName = str_replace('App\\Http\\Controllers\\', '', $controllerClass);

    test("{$shortName} has resource methods in standard order", function () use ($controllerClass, $resourceMethods) {
        $reflection = new ReflectionClass($controllerClass);

        $publicMethods = collect($reflection->getMethods(ReflectionMethod::IS_PUBLIC))
            ->reject(fn (ReflectionMethod $method) => $method->class !== $reflection->getName())
            ->reject(fn (ReflectionMethod $method) => $method->isStatic())
            ->reject(fn (ReflectionMethod $method) => str_starts_with($method->getName(), '__'))
            ->map(fn (ReflectionMethod $method) => $method->getName())
            ->values()
            ->all();

        if (count($publicMethods) < 2) {
            expect(true)->toBeTrue();

            return;
        }

        $presentResourceMethods = array_values(array_intersect($resourceMethods, $publicMethods));
        $actualOrder = array_values(array_intersect($publicMethods, $resourceMethods));

        expect($actualOrder)->toBe(
            $presentResourceMethods,
            'Resource methods must follow standard order (index, create, store, show, edit, update, destroy). Found: '.implode(', ', $actualOrder),
        );
    });
}
