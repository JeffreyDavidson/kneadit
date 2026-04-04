<?php

namespace App\Actions\Content;

use App\Models\Inventory\Product;
use Illuminate\Support\Arr;
use Illuminate\Support\Number;
use Illuminate\Support\Str;

class GenerateSocialCaption
{
    private const TEMPLATES = [
        'Fresh from the oven! Our {product} is made with love and the finest ingredients. Order yours today! 🍞✨ #{store_hashtag}',
        "Have you tried our {product}? It's one of our favorites! DM us to place your order 💛 #{store_hashtag}",
        'Weekend treat alert! 🎉 Our {product} ({price}) is calling your name. Link in bio to order! #{store_hashtag}',
    ];

    public function __invoke(Product $product): string
    {
        $storeName = tenant()->store_name ?? tenant()->name ?? 'Our Bakery';
        $storeHashtag = Str::replace(' ', '', ucwords($storeName));

        $template = Arr::random(self::TEMPLATES);

        return Str::replace(
            ['{product}', '{price}', '{store_hashtag}'],
            [$product->name, (string) Number::currency($product->price), $storeHashtag],
            $template,
        );
    }
}
