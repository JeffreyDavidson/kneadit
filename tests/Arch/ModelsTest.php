<?php

declare(strict_types=1);

arch('enums should be string backed')
    ->expect('App\Enums')
    ->toBeEnums()
    ->toBeStringBackedEnums();
