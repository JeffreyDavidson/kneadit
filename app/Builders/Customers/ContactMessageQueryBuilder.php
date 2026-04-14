<?php

namespace App\Builders\Customers;

use App\Models\Customers\ContactMessage;
use Illuminate\Database\Eloquent\Builder;

/** @extends Builder<ContactMessage> */
class ContactMessageQueryBuilder extends Builder
{
    public function read(): static
    {
        $this->where('is_read', true);

        return $this;
    }

    public function unread(): static
    {
        $this->where('is_read', false);

        return $this;
    }
}
