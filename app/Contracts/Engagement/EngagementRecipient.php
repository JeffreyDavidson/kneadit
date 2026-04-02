<?php

namespace App\Contracts\Engagement;

use Illuminate\Database\Eloquent\Model;

final readonly class EngagementRecipient
{
    /**
     * @param array<string, mixed> $context
     */
    public function __construct(
        public string $email,
        public string $name,
        public Model $model,
        public array $context = [],
    ) {}
}
