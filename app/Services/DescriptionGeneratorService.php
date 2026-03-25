<?php

namespace App\Services;

class DescriptionGeneratorService
{
    protected array $templates = [
        'professional' => [
            'Our {product} is crafted with precision and the finest ingredients, delivering a {adjective} experience that sets the standard for artisan {category}.',
            'Presenting our {product} — a testament to bakery excellence. Each {adjective} creation reflects our unwavering commitment to quality {category}.',
            'Experience the {adjective} perfection of our {product}. Meticulously prepared using time-honored techniques, this {category} exemplifies culinary craftsmanship.',
            'Our {product} represents the pinnacle of {adjective} {category}. Sourced from premium ingredients and prepared with expert precision.',
            'Discover our distinguished {product}, where {adjective} flavors meet artisanal tradition. A refined addition to our {category} collection.',
            'The {product} from our bakery stands as a benchmark in {adjective} {category}, crafted to exceed the expectations of the most discerning palate.',
        ],
        'casual' => [
            "You're gonna love our {product}! It's {adjective}, fresh, and honestly one of our favorites.",
            "Our {product}? Oh, it's amazing. {adjective} and totally worth every bite. Trust us on this one!",
            "If you haven't tried our {product} yet, what are you waiting for? It's {adjective} and seriously good {category}.",
            "Real talk: our {product} is the {adjective} {category} you've been looking for. Come grab one!",
            "Hey, just a heads up — our {product} is ridiculously {adjective}. Don't say we didn't warn you!",
            'Our {product} is basically a hug in {category} form. {adjective}, comforting, and always a crowd-pleaser.',
        ],
        'playful' => [
            'Warning: Our {product} has been known to cause spontaneous happy dances. Proceed with delicious caution!',
            "Our {product} is so {adjective}, it should probably come with its own fan club. Just sayin'.",
            "Legend has it that one bite of our {product} can turn any Monday into a Friday. It's that {adjective}.",
            'Plot twist: our {product} is the main character of your snack story. {adjective}, dramatic, unforgettable {category}!',
            "Scientists confirm: our {product} is 100% {adjective} and 200% delightful. Yes, we broke math. You're welcome.",
            'Breaking news: {adjective} {product} spotted in our bakery! Witnesses report extreme satisfaction and zero regrets.',
        ],
        'luxurious' => [
            'Indulge in our exquisite {product}, a masterwork of culinary artistry that transforms the finest {category} into an unforgettable sensory experience.',
            'Savor the opulence of our {product} — a {adjective} creation that elevates {category} to an art form worthy of the most refined palate.',
            'Our {product} is an expression of pure luxury. Each {adjective} layer speaks to an uncompromising dedication to gastronomic excellence.',
            'Behold our {product}: a symphony of {adjective} flavors composed with the rarest ingredients and the most exacting standards in {category}.',
            'Allow yourself the pleasure of our {product}, where {adjective} indulgence meets unparalleled sophistication in every sumptuous bite.',
            'The {product} — our crown jewel of {category}. A {adjective}, transcendent experience reserved for those who accept nothing less than extraordinary.',
        ],
        'homey' => [
            "Our {product} is made just like grandma used to make — with love, care, and a little extra butter. Because life's too short for boring {category}.",
            "There's something special about our {product}. Maybe it's the {adjective} taste, or maybe it's the feeling of home in every bite.",
            'Our {product} is the kind of {adjective} {category} that makes the whole kitchen smell like a warm hug. Come taste the comfort!',
            "We bake our {product} the old-fashioned way — slow, {adjective}, and full of heart. It's comfort {category} at its finest.",
            'Nothing says home like a {adjective} {product} fresh from the oven. Pull up a chair, pour some coffee, and enjoy.',
            'Our {product} recipe has been passed down with love. {adjective}, warm, and made to bring people together over great {category}.',
        ],
    ];

    protected array $adjectives = [
        'breads' => ['crusty', 'golden', 'aromatic', 'hearty', 'rustic'],
        'bread' => ['crusty', 'golden', 'aromatic', 'hearty', 'rustic'],
        'cakes' => ['decadent', 'moist', 'layered', 'heavenly', 'rich'],
        'cake' => ['decadent', 'moist', 'layered', 'heavenly', 'rich'],
        'cookies' => ['crispy', 'chewy', 'golden-brown', 'irresistible', 'buttery'],
        'cookie' => ['crispy', 'chewy', 'golden-brown', 'irresistible', 'buttery'],
        'pastries' => ['flaky', 'delicate', 'buttery', 'glazed', 'ethereal'],
        'pastry' => ['flaky', 'delicate', 'buttery', 'glazed', 'ethereal'],
        'pies' => ['seasonal', 'fruity', 'golden-crusted', 'homestyle', 'warming'],
        'pie' => ['seasonal', 'fruity', 'golden-crusted', 'homestyle', 'warming'],
    ];

    protected array $defaultAdjectives = ['fresh', 'handcrafted', 'artisan', 'delicious', 'premium'];

    /** @return array<int, string> */
    public function generate(string $product, string $tone, string $length, ?string $category = null, ?float $price = null, int $count = 3): array
    {
        $tone = strtolower($tone);
        $templates = $this->templates[$tone] ?? $this->templates['professional'];
        $categoryKey = strtolower($category ?? 'default');
        $adjectivePool = $this->adjectives[$categoryKey] ?? $this->defaultAdjectives;

        // Pick $count random templates
        $selectedKeys = array_rand($templates, min($count, count($templates)));
        if (! is_array($selectedKeys)) {
            $selectedKeys = [$selectedKeys];
        }

        $descriptions = [];
        foreach ($selectedKeys as $key) {
            $adjective = $adjectivePool[array_rand($adjectivePool)];
            $text = str_replace(
                ['{product}', '{category}', '{adjective}', '{price}'],
                [$product, $category ?: 'baked goods', $adjective, $price ? '$'.number_format($price, 2) : ''],
                $templates[$key]
            );
            $descriptions[] = $this->adjustLength($text, $length, $product, $category ?: 'baked goods', $adjectivePool);
        }

        return $descriptions;
    }

    protected function adjustLength(string $base, string $length, string $product, string $category, array $adjectives): string
    {
        return match (strtolower($length)) {
            'short' => $this->toShort($base),
            'long' => $this->toLong($base, $product, $category, $adjectives),
            default => $base, // medium = single template as-is
        };
    }

    protected function toShort(string $text): string
    {
        // Take first sentence only
        $pos = strpos($text, '. ');
        if ($pos !== false) {
            return substr($text, 0, $pos + 1);
        }
        $pos = strpos($text, '! ');
        if ($pos !== false) {
            return substr($text, 0, $pos + 1);
        }

        return $text;
    }

    protected function toLong(string $base, string $product, string $category, array $adjectives): string
    {
        $extras = [
            'Made fresh daily in our bakery, every {product} is baked with care using only the finest ingredients.',
            "Whether you're treating yourself or sharing with loved ones, our {product} is the perfect choice.",
            'Stop by and experience why our {category} keeps customers coming back for more.',
            'Each batch is crafted in small quantities to ensure the highest quality and flavor.',
            'Pair it with your favorite coffee or tea for the ultimate {category} experience.',
        ];

        $adj = $adjectives[array_rand($adjectives)];
        $extra1 = $extras[array_rand($extras)];
        $extra2 = $extras[array_rand(array_diff_key($extras, [$extra1]))];

        $replace = fn (string $s) => str_replace(['{product}', '{category}', '{adjective}'], [$product, $category, $adj], $s);

        return $base.' '.$replace($extra1).' '.$replace($extra2);
    }
}
