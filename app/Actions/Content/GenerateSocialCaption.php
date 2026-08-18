<?php

namespace App\Actions\Content;

use App\Models\Inventory\Product;
use App\Models\Platform\Tenant;
use Illuminate\Support\Arr;
use Illuminate\Support\Str;

class GenerateSocialCaption
{
    private const array TEMPLATES = [
        'Fresh from the oven! Our {product} is made with love and the finest ingredients. Order yours today! 🍞✨ #{store_hashtag}',
        "Have you tried our {product}? It's one of our favorites! DM us to place your order 💛 #{store_hashtag}",
        'Weekend treat alert! 🎉 Our {product} ({price}) is calling your name. Link in bio to order! #{store_hashtag}',
    ];

    public function __invoke(Product $product): string
    {
        $tenant = tenant();

        if (! $tenant instanceof Tenant) {
            throw new \UnexpectedValueException('A tenant is required to generate a social caption.');
        }

        $storeName = $tenant->store_name ?? $tenant->name;
        $storeHashtag = Str::replace(' ', '', ucwords($storeName));

        $template = Arr::random(self::TEMPLATES);

        if (! is_string($template)) {
            throw new \UnexpectedValueException('The social caption template must be a string.');
        }

        return Str::replace(
            ['{product}', '{price}', '{store_hashtag}'],
            [$product->name, $product->price?->formatted() ?? '', $storeHashtag],
            $template,
        );
    }
}
