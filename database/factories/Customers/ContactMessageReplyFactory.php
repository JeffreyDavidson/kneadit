<?php

namespace Database\Factories\Customers;

use App\Models\Customers\ContactMessage;
use App\Models\Customers\ContactMessageReply;
use App\Models\Staff\User;
use Illuminate\Database\Eloquent\Factories\Attributes\UseModel;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<ContactMessageReply> */
#[UseModel(ContactMessageReply::class)]
class ContactMessageReplyFactory extends Factory
{
    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'contact_message_id' => ContactMessage::factory(),
            'user_id' => User::factory(),
            'subject' => 'Re: ' . fake()->sentence(),
            'body' => fake()->paragraphs(2, true),
            'sent_at' => now(),
        ];
    }
}
