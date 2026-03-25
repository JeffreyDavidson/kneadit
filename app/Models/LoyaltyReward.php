<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Scope;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class LoyaltyReward extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'description',
        'points_required',
        'reward_type',
        'reward_value',
        'product_id',
        'is_active',
    ];

    protected $casts = [
        'reward_value' => 'decimal:2',
        'is_active' => 'boolean',
    ];

    /**
     * @return BelongsTo<Product, $this>
     */
    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }

    #[Scope]
    protected function active(Builder $query): void
    {
        $query->where('is_active', true);
    }

    protected function getRewardTypeLabelAttribute(): string
    {
        return match ($this->reward_type) {
            'percentage_discount' => $this->reward_value.'% Off',
            'fixed_discount' => '$'.number_format((float) $this->reward_value, 2).' Off',
            'free_product' => 'Free '.($this->product?->name ?? 'Product'),
            default => $this->reward_type,
        };
    }
}
