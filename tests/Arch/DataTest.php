<?php

declare(strict_types=1);

arch('data transfer objects and value objects should be readonly')
    ->expect(['App\DataTransferObjects', 'App\ValueObjects'])
    ->toBeReadonly();
