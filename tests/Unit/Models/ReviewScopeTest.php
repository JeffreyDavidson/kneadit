<?php

use App\Models\Engagement\Review;

it('has an approved scope that filters by is_approved', function () {
    $query = Review::query()->approved()->toRawSql();

    expect($query)->toContain('"is_approved"');
});
