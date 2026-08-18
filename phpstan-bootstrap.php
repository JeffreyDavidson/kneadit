<?php

use App\Models\Platform\Tenant;

// PHPStan type overrides for global helpers

if (false) {
    /**
     * @return Tenant|null
     */
    function tenant(?string $key = null) {}
}
