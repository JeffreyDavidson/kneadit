<?php

use App\Models\Tenant;

// PHPStan type overrides for global helpers

if (false) {
    /**
     * @return Tenant|null
     */
    function tenant(?string $key = null) {}
}
