<?php

use App\Enums\Marketing\EmailCampaignSegment;

test('EmailCampaignSegment has expected cases', function () {
    expect(EmailCampaignSegment::cases())->toHaveCount(6)
        ->and(EmailCampaignSegment::All->value)->toBe('all')
        ->and(EmailCampaignSegment::Trial->value)->toBe('trial');
});
