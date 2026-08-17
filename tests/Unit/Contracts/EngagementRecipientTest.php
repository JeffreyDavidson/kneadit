<?php

use App\Contracts\Engagement\EngagementRecipient;
use Illuminate\Database\Eloquent\Model;

test('EngagementRecipient stores all properties', function () {
    $model = new class extends Model {};

    $recipient = new EngagementRecipient(
        email: 'jane@example.com',
        name: 'Jane Doe',
        model: $model,
        context: ['order_id' => 42],
    );

    expect($recipient)
        ->email->toBe('jane@example.com')
        ->name->toBe('Jane Doe')
        ->model->toBe($model)
        ->context->toBe(['order_id' => 42]);
});

test('EngagementRecipient context defaults to empty array', function () {
    $model = new class extends Model {};

    $recipient = new EngagementRecipient(
        email: 'test@example.com',
        name: 'Test',
        model: $model,
    );

    expect($recipient->context)->toBeEmpty();
});
