<?php

use App\Enums\Marketing\EmailCampaignStatus;

test('EmailCampaignStatus has scheduled case for campaign scheduling', function () {
    $scheduled = EmailCampaignStatus::from('scheduled');

    expect($scheduled)->toBe(EmailCampaignStatus::Scheduled)
        ->and($scheduled->getLabel())->toBe('Scheduled');
});
