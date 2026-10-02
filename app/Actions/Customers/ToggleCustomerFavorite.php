<?php

namespace App\Actions\Customers;

use App\Models\Customers\CustomerFavorite;

class ToggleCustomerFavorite
{
    public function __invoke(string $email, int $productId): bool
    {
        $favorite = CustomerFavorite::query()
            ->forCustomer($email)
            ->forProduct($productId)
            ->first();

        if ($favorite) {
            $favorite->delete();

            return false;
        }

        CustomerFavorite::query()->create([
            'customer_email' => $email,
            'product_id' => $productId,
        ]);

        return true;
    }
}
