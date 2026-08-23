<?php

use App\Models\Platform\Tenant;

it('marks factory-created demo tenants', function () {
    expect(Tenant::factory()->demo()->make()->is_demo)->toBeTrue();
});
