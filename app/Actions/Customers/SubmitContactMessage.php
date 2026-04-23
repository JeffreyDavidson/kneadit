<?php

namespace App\Actions\Customers;

use App\Events\Customers\ContactMessageReceived;
use App\Models\Customers\ContactMessage;

class SubmitContactMessage
{
    /** @param array<string, mixed> $data */
    public function __invoke(array $data): ContactMessage
    {
        $message = ContactMessage::query()->create([
            'name' => $data['name'] ?? '',
            'email' => $data['email'] ?? '',
            'subject' => $data['subject'] ?? null,
            'message' => $data['message'] ?? '',
            'phone' => $data['phone'] ?? null,
        ]);

        event(new ContactMessageReceived($message));

        return $message;
    }
}
