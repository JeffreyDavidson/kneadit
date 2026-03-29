<?php

use App\Enums\RewardType;

test('each reward type has a label', function () {
    foreach (RewardType::cases() as $type) {
        expect($type->getLabel())->toBeString()->not->toBeEmpty();
    }
});

test('each reward type has a color', function () {
    foreach (RewardType::cases() as $type) {
        expect($type->color())->toBeString()->not->toBeEmpty();
    }
});
