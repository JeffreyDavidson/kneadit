<?php

use App\Models\Review;

it('has an approved scope that filters by is_approved', function () {
    $query = Review::query()->approved()->toRawSql();

    expect($query)->toContain('"is_approved"');
});
