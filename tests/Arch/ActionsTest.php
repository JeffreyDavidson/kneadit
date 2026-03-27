<?php

declare(strict_types=1);

arch('actions should be invokable classes')
    ->expect('App\Actions')
    ->toBeClasses()
    ->toHaveMethod('__invoke');
