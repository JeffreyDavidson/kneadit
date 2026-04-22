<?php

namespace App\Builders\Customers;

use App\Models\Customers\CustomerFavorite;
use Illuminate\Database\Eloquent\Builder;

/** @extends Builder<CustomerFavorite> */
class CustomerFavoriteQueryBuilder extends Builder
{
    public function forCustomer(string $email): static
    {
        $this->where('customer_email', $email);

        return $this;
    }

    public function forProduct(int $productId): static
    {
        $this->where('product_id', $productId);

        return $this;
    }
}
