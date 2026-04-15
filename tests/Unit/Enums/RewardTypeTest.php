<?php

use App\Enums\Engagement\RewardType;

test('each reward type has a label', function (RewardType $type) {
    expect($type->getLabel())->toBeString()->not->toBeEmpty();
})->with(RewardType::cases());

test('each reward type has a color', function (RewardType $type) {
    expect($type->getColor())->toBeString()->not->toBeEmpty();
})->with(RewardType::cases());
