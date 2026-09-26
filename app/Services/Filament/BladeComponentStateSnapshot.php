<?php

namespace App\Services\Filament;

use Illuminate\Contracts\View\Factory as ViewFactory;
use ReflectionClass;

/**
 * Captures the Blade component stacks that Laravel does not expose publicly.
 * Keep this compatibility boundary narrow: flushState() also clears state
 * belonging to the parent view render.
 */
final class BladeComponentStateSnapshot
{
    /** @var array<string, mixed> */
    private array $state = [];

    private function __construct(private readonly object $factory) {}

    public static function capture(ViewFactory $factory): self
    {
        $snapshot = new self($factory);
        $reflection = new ReflectionClass($factory);

        foreach (['componentStack', 'slotStack', 'componentData'] as $property) {
            if (! $reflection->hasProperty($property)) {
                continue;
            }

            $snapshot->state[$property] = $reflection->getProperty($property)->getValue($factory);
        }

        return $snapshot;
    }

    public function restore(): void
    {
        $reflection = new ReflectionClass($this->factory);

        foreach ($this->state as $property => $value) {
            if (! $reflection->hasProperty($property)) {
                continue;
            }

            $reflection->getProperty($property)->setValue($this->factory, $value);
        }
    }
}
