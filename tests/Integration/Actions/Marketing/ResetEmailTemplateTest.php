<?php

use App\Actions\Marketing\ResetEmailTemplate;
use App\Enums\Marketing\EmailTemplateType;
use App\Models\Marketing\EmailTemplate;
use Illuminate\Foundation\Testing\RefreshDatabase;

pest()->use(RefreshDatabase::class);

beforeEach(fn () => setUpTenantTest());

test('resets a customized email template', function () {
    EmailTemplate::factory()->create([
        'email_type' => EmailTemplateType::OrderPlaced,
        'subject' => 'Custom',
        'body' => 'Custom body',
    ]);

    resolve(ResetEmailTemplate::class)(EmailTemplateType::OrderPlaced);

    expect(EmailTemplate::query()->where('email_type', EmailTemplateType::OrderPlaced)->exists())->toBeFalse();
});
