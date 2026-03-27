<?php

declare(strict_types=1);

$resourceMethods = ['index', 'create', 'store', 'show', 'edit', 'update', 'destroy'];

$controllerFiles = collect(glob(__DIR__ . '/../../app/Http/Controllers/**/*.php'))
    ->merge(glob(__DIR__ . '/../../app/Http/Controllers/*.php'))
    ->map(function ($file) {
        $relative = str_replace(__DIR__ . '/../../app/', '', $file);

        return str_replace(['/', '.php'], ['\\', ''], 'App\\' . $relative);
    })
    ->reject(fn ($class) => $class === 'App\\Http\\Controllers\\Controller')
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
            'Must be invokable or resourceful. Found: ' . implode(', ', $publicMethods),
        );
    });
}

arch('controllers should be classes')
    ->expect('App\Http\Controllers')
    ->toBeClasses();

arch('controllers should not use DB facade directly')
    ->expect('Illuminate\Support\Facades\DB')
    ->not->toBeUsedIn('App\Http\Controllers')
    ->ignoring([
        'App\Http\Controllers\ImpersonateController',
        'App\Http\Controllers\ConsumeImpersonationController',
    ]);
