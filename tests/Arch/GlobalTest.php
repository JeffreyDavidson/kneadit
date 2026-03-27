<?php

declare(strict_types=1);

arch('no debugging statements in app code')
    ->expect(['dd', 'dump', 'ray', 'var_dump', 'print_r'])
    ->not->toBeUsedIn('App');

arch('no env calls outside config files')
    ->expect('env')
    ->not->toBeUsedIn('App');
