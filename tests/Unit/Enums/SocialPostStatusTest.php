<?php

use App\Enums\Marketing\SocialPostStatus;

test('SocialPostStatus has a color for every case', function (SocialPostStatus $case) {
    expect($case->getColor())->toBeString();
})->with(SocialPostStatus::cases());

test('SocialPostStatus has an icon for every case', function (SocialPostStatus $case) {
    expect($case->getIcon())->toBeInstanceOf(Filament\Support\Icons\Heroicon::class);
})->with(SocialPostStatus::cases());
