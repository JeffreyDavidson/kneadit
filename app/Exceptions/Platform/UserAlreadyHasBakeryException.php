<?php

declare(strict_types=1);

namespace App\Exceptions\Platform;

use Illuminate\Contracts\Debug\ShouldntReport;
use RuntimeException;

class UserAlreadyHasBakeryException extends RuntimeException implements ShouldntReport
{
    public function __construct()
    {
        parent::__construct('Your account already has a bakery.');
    }
}
