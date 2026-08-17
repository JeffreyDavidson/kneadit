<?php

namespace Tests\Support;

use Mockery;
use Mockery\MockInterface;
use RuntimeException;

final class TypedMock
{
    /**
     * @template TMock of object
     *
     * @param class-string<TMock> $class
     * @return TMock&MockInterface
     */
    public static function make(string $class): object
    {
        $mock = Mockery::mock($class);

        if (! $mock instanceof $class) {
            throw new RuntimeException("Mockery did not create an instance of {$class}.");
        }

        return $mock;
    }
}
