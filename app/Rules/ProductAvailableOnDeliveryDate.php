<?php

namespace App\Rules;

use App\Models\Inventory\Product;
use Carbon\CarbonInterface;
use Closure;
use Illuminate\Contracts\Validation\DataAwareRule;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Date;
use Throwable;

/**
 * Applied to `items.*.product_id`: a seasonal product can only be ordered for a
 * delivery date inside one of its seasonal windows. Every ordered product is
 * loaded once, on the first item checked, however many items the order has.
 */
class ProductAvailableOnDeliveryDate implements DataAwareRule, ValidationRule
{
    /** @var array<string, mixed> */
    protected array $data = [];

    /** @var Collection<int, Product>|null */
    private ?Collection $products = null;

    /** @var Collection<int, Product> */
    private Collection $availableProducts;

    /**
     * @param  array<string, mixed>  $data
     */
    public function setData(array $data): static
    {
        $this->data = $data;

        return $this;
    }

    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        $date = $this->deliveryDate();

        if (! $date instanceof CarbonInterface || ! is_numeric($value)) {
            return;
        }

        $this->loadProducts($date);

        $product = $this->products?->get((int) $value);

        if ($product === null || $this->availableProducts->has($product->id)) {
            return;
        }

        $fail("{$product->name} isn't available for {$date->format('M j, Y')}.");
    }

    private function deliveryDate(): ?CarbonInterface
    {
        $value = $this->data['delivery_date'] ?? null;

        if (! is_string($value)) {
            return null;
        }

        try {
            return Date::parse($value);
        } catch (Throwable) {
            return null;
        }
    }

    private function loadProducts(CarbonInterface $date): void
    {
        if ($this->products instanceof Collection) {
            return;
        }

        $items = $this->data['items'] ?? [];
        $ids = collect(is_array($items) ? $items : [])
            ->map(fn (mixed $item): mixed => is_array($item) ? ($item['product_id'] ?? null) : null)
            ->filter(fn (mixed $id): bool => is_numeric($id))
            ->map(fn (mixed $id): int => (int) $id)
            ->unique()
            ->values();

        $this->products = Product::query()->select(['id', 'name'])->whereKey($ids)->get()->keyBy('id');
        $this->availableProducts = Product::query()->select('id')->availableOn($date)->whereKey($ids)->get()->keyBy('id');
    }
}
