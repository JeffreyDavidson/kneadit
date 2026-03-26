<?php

namespace App\Actions;

use App\Models\CustomerFavorite;

class ToggleCustomerFavorite
{
    public function __invoke(string $email, int $productId): bool
    {
        $favorite = CustomerFavorite::query()
            ->where('customer_email', $email)
            ->where('product_id', $productId)
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
