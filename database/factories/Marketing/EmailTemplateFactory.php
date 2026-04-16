<?php

namespace Database\Factories\Marketing;

use App\Enums\Marketing\EmailTemplateType;
use App\Models\Marketing\EmailTemplate;
use Illuminate\Database\Eloquent\Factories\Attributes\UseModel;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<EmailTemplate>
 */
#[UseModel(EmailTemplate::class)]
class EmailTemplateFactory extends Factory
{
    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'email_type' => fake()->unique()->randomElement(EmailTemplateType::cases()),
            'subject' => fake()->sentence(),
            'body' => fake()->paragraphs(2, true),
        ];
    }
}
