<?php

namespace App\Filament\Pages;

use App\Models\Product;
use BackedEnum;
use Filament\Actions\Action;
use Filament\Forms\Components\Select;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Forms\Form;
use Filament\Pages\Page;
use Filament\Forms\Contracts\HasForms;
use Filament\Support\Icons\Heroicon;
use UnitEnum;

class InstagramCaptionGenerator extends Page implements HasForms
{
    use InteractsWithForms;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedCamera;
    protected static string|UnitEnum|null $navigationGroup = 'Marketing';
    protected static ?string $navigationLabel = 'Instagram Captions';
    protected static ?string $title = 'Instagram Caption Generator';

    protected string $view = 'filament.pages.instagram-caption-generator';

    public ?array $data = [];
    public array $captions = [];

    public function mount(): void
    {
        $this->form->fill();
    }

    public function form(Form $form): Form
    {
        return $form
            ->schema([
                Select::make('product_id')
                    ->label('Select Product')
                    ->placeholder('Choose a product...')
                    ->options(Product::where('is_active', true)->pluck('name', 'id'))
                    ->searchable()
                    ->required(),

                Select::make('style')
                    ->label('Caption Style')
                    ->options([
                        'playful' => 'Playful',
                        'professional' => 'Professional',
                        'seasonal' => 'Seasonal',
                        'storytelling' => 'Storytelling',
                    ])
                    ->required(),

                Select::make('tone')
                    ->label('Tone')
                    ->options([
                        'warm' => 'Warm',
                        'excited' => 'Excited',
                        'casual' => 'Casual',
                        'elegant' => 'Elegant',
                    ])
                    ->required(),
            ])
            ->statePath('data');
    }

    protected function getFormActions(): array
    {
        return [
            Action::make('generate')
                ->label('Generate Captions')
                ->action('generateCaptions')
                ->icon(Heroicon::OutlinedSparkles)
                ->color('primary')
                ->size('lg'),
        ];
    }

    public function generateCaptions(): void
    {
        $this->validate([
            'data.product_id' => 'required|exists:products,id',
            'data.style' => 'required|in:playful,professional,seasonal,storytelling',
            'data.tone' => 'required|in:warm,excited,casual,elegant',
        ]);

        $product = Product::with('category')->find($this->data['product_id']);
        $style = $this->data['style'];
        $tone = $this->data['tone'];

        $this->captions = $this->generateCaptionVariations($product, $style, $tone);
    }

    private function generateCaptionVariations(Product $product, string $style, string $tone): array
    {
        $hooks = $this->getHooksByStyle($style);
        $hashtags = $this->getHashtagsByCategory($product->category->name ?? 'bakery');
        
        $captions = [];
        
        // Generate 3 different caption variations
        for ($i = 0; $i < 3; $i++) {
            $hook = $hooks[array_rand($hooks)];
            $body = $this->generateCaptionBody($product, $style, $tone);
            $selectedHashtags = $this->selectRandomHashtags($hashtags, rand(8, 15));
            
            $captions[] = [
                'text' => $hook . "\n\n" . $body . "\n\n" . implode(' ', $selectedHashtags),
                'variation' => $i + 1,
            ];
        }
        
        return $captions;
    }

    private function getHooksByStyle(string $style): array
    {
        $hooks = [
            'playful' => [
                "Fresh out of the oven 🍞",
                "Weekend treat alert 🎉",
                "Who said you can't have cake for breakfast? 🧁",
                "Plot twist: this tastes even better than it looks! 😍",
                "Current mood: surrounded by carbs and couldn't be happier 🥐",
                "Breaking news: local bakery makes dreams come true ✨",
                "Fun fact: this is what happiness looks like 🌈",
                "Warning: may cause uncontrollable smiling 😊",
                "Just dropped: the cure for Monday blues 💙",
                "Pro tip: life's too short for mediocre pastries 🥯",
                "Spoiler alert: your taste buds are in for a treat! 👅",
                "Reality check: yes, it tastes as good as it looks 🤤",
            ],
            'professional' => [
                "Crafted with precision and passion.",
                "Where tradition meets innovation.",
                "Artisan quality, locally made.",
                "Experience the difference quality makes.",
                "Handcrafted using time-honored techniques.",
                "Excellence in every bite.",
                "Proudly serving our community for years.",
                "Made fresh daily with premium ingredients.",
                "The art of baking, perfected.",
                "Quality you can taste, service you can trust.",
                "From our kitchen to your table.",
                "Dedication to craft meets modern flavor.",
            ],
            'seasonal' => [
                "Embracing the flavors of the season 🍂",
                "Spring has sprung, and so have our fresh flavors! 🌸",
                "Summer vibes and sweet treats ☀️",
                "Fall favorites are here to warm your heart 🍁",
                "Winter comfort in every bite ❄️",
                "Celebrating the season with every creation 🎊",
                "Fresh seasonal ingredients at their peak 🌿",
                "Limited time seasonal magic ⏰",
                "Nature's finest, baked to perfection 🌺",
                "Seasonal specialties worth the wait 🍃",
                "Harvest-inspired and happiness-guaranteed 🥧",
                "The taste of the season, captured perfectly 🎭",
            ],
            'storytelling' => [
                "There's something special about...",
                "Every morning, our bakers arrive before dawn to...",
                "The recipe for this began three generations ago...",
                "Behind every perfect bite is a story of...",
                "What started as a family tradition has become...",
                "In our kitchen, magic happens when...",
                "The secret ingredient? Love, patience, and...",
                "From flour to finished product, every step matters...",
                "Years of perfecting this recipe have led to...",
                "When passion meets flour, beautiful things happen...",
                "This isn't just food, it's a memory in the making...",
                "The journey from simple ingredients to extraordinary taste...",
            ],
        ];
        
        return $hooks[$style] ?? $hooks['playful'];
    }

    private function generateCaptionBody(Product $product, string $style, string $tone): string
    {
        $toneWords = [
            'warm' => ['cozy', 'comforting', 'heartwarming', 'inviting', 'welcoming'],
            'excited' => ['amazing', 'incredible', 'fantastic', 'spectacular', 'outstanding'],
            'casual' => ['awesome', 'great', 'nice', 'cool', 'perfect'],
            'elegant' => ['exquisite', 'refined', 'sophisticated', 'delicate', 'premium'],
        ];
        
        $bodyTemplates = [
            "Our {adjective} {product} is made with {ingredient_focus} and baked to perfection. {call_to_action}",
            "{product} that's {adjective} and crafted with care. Perfect for {occasion}. {call_to_action}",
            "Experience the {adjective} taste of our {product}. Made fresh daily with premium ingredients. {call_to_action}",
            "Nothing beats the {adjective} flavor of our signature {product}. {call_to_action}",
            "Treat yourself to our {adjective} {product} - your taste buds will thank you! {call_to_action}",
        ];
        
        $callToActions = [
            "Visit us today!",
            "Order yours now!",
            "Available now in-store!",
            "Perfect for sharing... or not! 😉",
            "Made with love, enjoyed with joy.",
            "Because you deserve the best.",
        ];
        
        $ingredients = [
            "the finest ingredients",
            "locally sourced materials",
            "premium quality flour",
            "farm-fresh ingredients",
            "artisan-selected components",
        ];
        
        $occasions = [
            "breakfast treats",
            "afternoon indulgence", 
            "special celebrations",
            "everyday moments",
            "weekend relaxation",
        ];
        
        $template = $bodyTemplates[array_rand($bodyTemplates)];
        $adjective = $toneWords[$tone][array_rand($toneWords[$tone])];
        
        return str_replace([
            '{adjective}',
            '{product}',
            '{ingredient_focus}',
            '{call_to_action}',
            '{occasion}',
        ], [
            $adjective,
            strtolower($product->name),
            $ingredients[array_rand($ingredients)],
            $callToActions[array_rand($callToActions)],
            $occasions[array_rand($occasions)],
        ], $template);
    }

    private function getHashtagsByCategory(string $category): array
    {
        $baseHashtags = [
            '#KneadItBakery',
            '#FreshBaked',
            '#LocalBakery',
            '#HandCrafted',
            '#BakedWithLove',
            '#ArtisanBaked',
            '#FreshDaily',
        ];
        
        $categoryHashtags = [
            'bread' => ['#FreshBread', '#ArtisanBread', '#DailyBread', '#SourdoughLife', '#BreadLovers'],
            'cake' => ['#CustomCakes', '#CakeLove', '#BirthdayCake', '#CelebrationCakes', '#CakeArt'],
            'pastry' => ['#Pastries', '#FlakyGood', '#MorningTreats', '#PastryPerfection', '#ButteryCrust'],
            'cookie' => ['#FreshCookies', '#CookieJar', '#HomemadeCookies', '#SweetTreats', '#CookieLove'],
            'muffin' => ['#MuffinMonday', '#FreshMuffins', '#BreakfastTreats', '#MorningFuel', '#MuffinTime'],
            'dessert' => ['#SweetTooth', '#DessertTime', '#Indulgence', '#SweetTreats', '#DessertLover'],
        ];
        
        $generalBakeryHashtags = [
            '#Bakery', '#BakedGoods', '#ComfortFood', '#TreatYourself', '#QualityFirst',
            '#MadeToOrder', '#LocallyMade', '#BakingPassion', '#FlavorFirst', '#SweetLife',
            '#BakingTradition', '#QualityCrafted', '#BakingExcellence', '#FlourPower',
            '#CommunityBakery', '#BakeryCrafted', '#GoodnessBaked', '#FlavorTown',
        ];
        
        $categorySpecific = $categoryHashtags[strtolower($category)] ?? [];
        
        return array_merge($baseHashtags, $categorySpecific, $generalBakeryHashtags);
    }

    private function selectRandomHashtags(array $hashtags, int $count): array
    {
        shuffle($hashtags);
        return array_slice($hashtags, 0, min($count, count($hashtags)));
    }
}