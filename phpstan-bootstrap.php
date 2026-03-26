<?php

use App\Models\Tenant;

// PHPStan type overrides for global helpers that return mixed

if (false) {
    /**
     * Override Stancl's tenant() to return our concrete Tenant model.
     *
     * @return Tenant|null
     */
    function tenant(?string $key = null): ?Tenant
    {
    }
}
