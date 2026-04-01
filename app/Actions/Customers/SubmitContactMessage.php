<?php

namespace App\Actions\Customers;

use App\Models\Customers\ContactMessage;

class SubmitContactMessage
{
    /** @param array<string, mixed> $data */
    public function __invoke(array $data): ContactMessage
    {
        return ContactMessage::query()->create([
            'name' => $data['name'] ?? '',
            'email' => $data['email'] ?? '',
            'subject' => $data['subject'] ?? null,
            'message' => $data['message'] ?? '',
            'phone' => $data['phone'] ?? null,
        ]);
    }
}
