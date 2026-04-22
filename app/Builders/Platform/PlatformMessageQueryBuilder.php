<?php

namespace App\Builders\Platform;

use App\Enums\Platform\PlatformSenderType;
use App\Models\Platform\PlatformMessage;
use Illuminate\Database\Eloquent\Builder;

/** @extends Builder<PlatformMessage> */
class PlatformMessageQueryBuilder extends Builder
{
    public function unread(): static
    {
        $this->where('is_read', false);

        return $this;
    }

    public function fromAdmin(): static
    {
        $this->where('sender_type', PlatformSenderType::Admin);

        return $this;
    }

    public function fromTenant(): static
    {
        $this->where('sender_type', PlatformSenderType::Tenant);

        return $this;
    }

    public function topLevel(): static
    {
        $this->whereNull('parent_id');

        return $this;
    }
}
