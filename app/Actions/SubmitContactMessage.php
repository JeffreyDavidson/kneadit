<?php

namespace App\Actions;

use App\Models\ContactMessage;

class SubmitContactMessage
{
    /** @param array<string, mixed> $data */
    public function __invoke(array $data): ContactMessage
    {
        return ContactMessage::query()->create([
            'name' => strip_tags($data['name'] ?? ''),
            'email' => $data['email'] ?? '',
            'subject' => isset($data['subject']) ? strip_tags($data['subject']) : null,
            'message' => strip_tags($data['message'] ?? ''),
            'phone' => $data['phone'] ?? null,
        ]);
    }
}
