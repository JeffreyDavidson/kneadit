<?php

namespace App\Services\Content;

use App\Enums\Content\CaptionStyle;
use App\Models\Inventory\Product;
use Illuminate\Support\Arr;
use Illuminate\Support\Str;

class CaptionGeneratorService
{
    /**
     * @return array<int, array{text: string, variation: int}>
     */
    public function generate(Product $product, CaptionStyle $style, string $tone, int $count = 3): array
    {
        $hooks = $this->hooksByStyle($style);
        $hashtags = $this->hashtagsByCategory($product->category->name ?? 'bakery');

        $captions = [];

        for ($i = 0; $i < $count; $i++) {
            $hook = Arr::random($hooks);
            $body = $this->buildBody($product, $tone);
            $selectedHashtags = Arr::random($hashtags, min(random_int(8, 15), count($hashtags)));

            $captions[] = [
                'text' => $hook . "\n\n" . $body . "\n\n" . implode(' ', $selectedHashtags),
                'variation' => $i + 1,
            ];
        }

        return $captions;
    }

    /** @return array<int, string> */
    private function hooksByStyle(CaptionStyle $style): array
    {
        $hooks = [
            'playful' => [
                'Fresh out of the oven 🍞',
                'Weekend treat alert 🎉',
                "Who said you can't have cake for breakfast? 🧁",
                'Plot twist: this tastes even better than it looks! 😍',
                "Current mood: surrounded by carbs and couldn't be happier 🥐",
                'Breaking news: local bakery makes dreams come true ✨',
                'Fun fact: this is what happiness looks like 🌈',
                'Warning: may cause uncontrollable smiling 😊',
                'Just dropped: the cure for Monday blues 💙',
                "Pro tip: life's too short for mediocre pastries 🥯",
                'Spoiler alert: your taste buds are in for a treat! 👅',
                'Reality check: yes, it tastes as good as it looks 🤤',
            ],
            'professional' => [
                'Crafted with precision and passion.',
                'Where tradition meets innovation.',
                'Artisan quality, locally made.',
                'Experience the difference quality makes.',
                'Handcrafted using time-honored techniques.',
                'Excellence in every bite.',
                'Proudly serving our community for years.',
                'Made fresh daily with premium ingredients.',
                'The art of baking, perfected.',
                'Quality you can taste, service you can trust.',
                'From our kitchen to your table.',
                'Dedication to craft meets modern flavor.',
            ],
            'seasonal' => [
                'Embracing the flavors of the season 🍂',
                'Spring has sprung, and so have our fresh flavors! 🌸',
                'Summer vibes and sweet treats ☀️',
                'Fall favorites are here to warm your heart 🍁',
                'Winter comfort in every bite ❄️',
                'Celebrating the season with every creation 🎊',
                'Fresh seasonal ingredients at their peak 🌿',
                'Limited time seasonal magic ⏰',
                "Nature's finest, baked to perfection 🌺",
                'Seasonal specialties worth the wait 🍃',
                'Harvest-inspired and happiness-guaranteed 🥧',
                'The taste of the season, captured perfectly 🎭',
            ],
            'storytelling' => [
                "There's something special about...",
                'Every morning, our bakers arrive before dawn to...',
                'The recipe for this began three generations ago...',
                'Behind every perfect bite is a story of...',
                'What started as a family tradition has become...',
                'In our kitchen, magic happens when...',
                'The secret ingredient? Love, patience, and...',
                'From flour to finished product, every step matters...',
                'Years of perfecting this recipe have led to...',
                'When passion meets flour, beautiful things happen...',
                "This isn't just food, it's a memory in the making...",
                'The journey from simple ingredients to extraordinary taste...',
            ],
        ];

        return $hooks[$style->value];
    }

    private function buildBody(Product $product, string $tone): string
    {
        $toneWords = [
            'warm' => ['cozy', 'comforting', 'heartwarming', 'inviting', 'welcoming'],
            'excited' => ['amazing', 'incredible', 'fantastic', 'spectacular', 'outstanding'],
            'casual' => ['awesome', 'great', 'nice', 'cool', 'perfect'],
            'elegant' => ['exquisite', 'refined', 'sophisticated', 'delicate', 'premium'],
        ];

        $templates = [
            'Our {adjective} {product} is made with {ingredient_focus} and baked to perfection. {call_to_action}',
            "{product} that's {adjective} and crafted with care. Perfect for {occasion}. {call_to_action}",
            'Experience the {adjective} taste of our {product}. Made fresh daily with premium ingredients. {call_to_action}',
            'Nothing beats the {adjective} flavor of our signature {product}. {call_to_action}',
            'Treat yourself to our {adjective} {product} - your taste buds will thank you! {call_to_action}',
        ];

        $callToActions = [
            'Visit us today!',
            'Order yours now!',
            'Available now in-store!',
            'Perfect for sharing... or not! 😉',
            'Made with love, enjoyed with joy.',
            'Because you deserve the best.',
        ];

        $ingredients = [
            'the finest ingredients',
            'locally sourced materials',
            'premium quality flour',
            'farm-fresh ingredients',
            'artisan-selected components',
        ];

        $occasions = [
            'breakfast treats',
            'afternoon indulgence',
            'special celebrations',
            'everyday moments',
            'weekend relaxation',
        ];

        return (string) Str::replace(
            ['{adjective}', '{product}', '{ingredient_focus}', '{call_to_action}', '{occasion}'],
            [
                Arr::random($toneWords[$tone] ?? $toneWords['warm']),
                Str::lower($product->name),
                Arr::random($ingredients),
                Arr::random($callToActions),
                Arr::random($occasions),
            ],
            (string) Arr::random($templates),
        );
    }

    /** @return array<int, string> */
    private function hashtagsByCategory(string $category): array
    {
        $base = [
            '#KneadItBakery', '#FreshBaked', '#LocalBakery', '#HandCrafted',
            '#BakedWithLove', '#ArtisanBaked', '#FreshDaily',
        ];

        $categoryMap = [
            'bread' => ['#FreshBread', '#ArtisanBread', '#DailyBread', '#SourdoughLife', '#BreadLovers'],
            'cake' => ['#CustomCakes', '#CakeLove', '#BirthdayCake', '#CelebrationCakes', '#CakeArt'],
            'pastry' => ['#Pastries', '#FlakyGood', '#MorningTreats', '#PastryPerfection', '#ButteryCrust'],
            'cookie' => ['#FreshCookies', '#CookieJar', '#HomemadeCookies', '#SweetTreats', '#CookieLove'],
            'muffin' => ['#MuffinMonday', '#FreshMuffins', '#BreakfastTreats', '#MorningFuel', '#MuffinTime'],
            'dessert' => ['#SweetTooth', '#DessertTime', '#Indulgence', '#SweetTreats', '#DessertLover'],
        ];

        $general = [
            '#Bakery', '#BakedGoods', '#ComfortFood', '#TreatYourself', '#QualityFirst',
            '#MadeToOrder', '#LocallyMade', '#BakingPassion', '#FlavorFirst', '#SweetLife',
            '#BakingTradition', '#QualityCrafted', '#BakingExcellence', '#FlourPower',
            '#CommunityBakery', '#BakeryCrafted', '#GoodnessBaked', '#FlavorTown',
        ];

        return array_merge($base, $categoryMap[Str::lower($category)] ?? [], $general);
    }
}
