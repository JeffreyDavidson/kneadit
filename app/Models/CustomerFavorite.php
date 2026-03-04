<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class CustomerFavorite extends Model
{
    protected $fillable = [
        'customer_email', 
        'product_id'
    ];

    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }

    public function scopeForCustomer($query, string $email)
    {
        return $query->where('customer_email', $email);
    }

    public static function toggle(string $email, int $productId): bool
    {
        $favorite = static::where('customer_email', $email)
            ->where('product_id', $productId)
            ->first();

        if ($favorite) {
            $favorite->delete();
            return false; // removed
        } else {
            static::create([
                'customer_email' => $email,
                'product_id' => $productId
            ]);
            return true; // added
        }
    }

    public static function isFavorite(string $email, int $productId): bool
    {
        return static::where('customer_email', $email)
            ->where('product_id', $productId)
            ->exists();
    }
}