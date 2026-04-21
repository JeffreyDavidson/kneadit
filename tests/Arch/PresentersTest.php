<?php

declare(strict_types=1);

arch('presenters should be classes')
    ->expect('App\Presenters')
    ->toBeClasses();

arch('presenters should expose a static for() factory')
    ->expect('App\Presenters')
    ->toHaveMethod('for');
