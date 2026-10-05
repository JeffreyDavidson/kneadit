<?php

use Illuminate\Support\Facades\Event;

test('no listener class is registered more than once for the same event', function () {
    $duplicates = collect(Event::getRawListeners())
        ->map(fn (array $listeners): array => collect($listeners)
            ->filter(fn (mixed $listener): bool => is_string($listener))
            ->map(fn (string $listener): string => str($listener)->before('@')->toString())
            ->countBy()
            ->filter(fn (int $count): bool => $count > 1)
            ->keys()
            ->all())
        ->filter()
        ->map(fn (array $listeners, string $event): string => "{$event}: ".implode(', ', $listeners))
        ->values()
        ->all();

    expect($duplicates)->toBeEmpty();
});
