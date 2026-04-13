<?php

use App\Enums\Marketing\SocialPostStatus;

test('SocialPostStatus has a color for every case', function () {
    foreach (SocialPostStatus::cases() as $case) {
        expect($case->getColor())->toBeString();
    }
});

test('SocialPostStatus has an icon for every case', function () {
    foreach (SocialPostStatus::cases() as $case) {
        expect($case->getIcon())->toBeInstanceOf(Filament\Support\Icons\Heroicon::class);
    }
});
