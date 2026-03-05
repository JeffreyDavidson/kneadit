<?php

namespace App\Filament\Pages;

use App\Models\Category;
use App\Models\Product;
use App\Models\Setting;
use BackedEnum;
use Filament\Actions\Action;
use Filament\Forms\Components\ColorPicker;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Notifications\Notification;
use Filament\Pages\Page;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Components\Wizard;
use Filament\Schemas\Components\Wizard\Step;
use Filament\Schemas\Schema;
use Illuminate\Support\Str;
use UnitEnum;

class Onboarding extends Page
{
    protected static bool $shouldRegisterNavigation = false;

    protected static string|BackedEnum|null $navigationIcon = null;

    protected string $view = 'filament.pages.onboarding';

    protected static ?string $title = 'Welcome to KneadIt';

    protected static ?string $slug = 'onboarding';

    // Step 1: Welcome
    public ?string $bakery_name = '';
    public ?string $owner_name = '';

    // Step 2: Contact Info
    public ?string $contact_email = '';
    public ?string $contact_phone = '';
    public ?string $contact_address = '';

    // Step 3: Branding
    public ?string $brand_color_primary = '#6b4c3b';
    public ?string $brand_color_secondary = '#d4a574';
    public ?array $store_logo = [];

    // Step 4: First Product
    public ?string $product_name = '';
    public ?string $product_description = '';
    public ?string $product_price = '';
    public ?string $product_category_id = '';

    public function mount(): void
    {
        // If onboarding is already complete, redirect to dashboard
        if (Setting::get('onboarding_completed_at')) {
            $this->redirect(url('/admin'));
            return;
        }

        // Pre-fill from tenant data if available
        $tenant = tenant();
        if ($tenant) {
            $this->bakery_name = $tenant->store_name ?? $tenant->name ?? '';
            $this->owner_name = $tenant->name ?? '';
            $this->contact_email = $tenant->email ?? '';
            $this->brand_color_primary = $tenant->brand_color_primary ?? '#6b4c3b';
            $this->brand_color_secondary = $tenant->brand_color_secondary ?? '#d4a574';
        }

        // Pre-fill from settings if available
        $this->bakery_name = Setting::get('store_name', $this->bakery_name);
        $this->contact_email = Setting::get('store_email', $this->contact_email);
        $this->contact_phone = Setting::get('store_phone', '');
        $this->contact_address = Setting::get('store_address', '');
    }

    public function content(Schema $schema): Schema
    {
        return $schema->components([
            Wizard::make([
                Step::make('Welcome')
                    ->icon('heroicon-o-hand-raised')
                    ->description('Tell us about your bakery')
                    ->schema([
                        Section::make('Welcome to KneadIt!')
                            ->description('Let\'s get your bakery set up. This will only take a few minutes.')
                            ->schema([
                                TextInput::make('bakery_name')
                                    ->label('Bakery Name')
                                    ->required()
                                    ->placeholder('e.g. Sweet Sunrise Bakery')
                                    ->maxLength(255),
                                TextInput::make('owner_name')
                                    ->label('Your Name')
                                    ->required()
                                    ->placeholder('e.g. Jane Baker')
                                    ->maxLength(255),
                            ]),
                    ])
                    ->afterValidation(function () {
                        $this->saveWelcomeStep();
                    }),

                Step::make('Contact Info')
                    ->icon('heroicon-o-envelope')
                    ->description('How customers can reach you')
                    ->schema([
                        Section::make('Contact Information')
                            ->description('This information will be displayed on your storefront.')
                            ->schema([
                                Grid::make(2)->schema([
                                    TextInput::make('contact_email')
                                        ->label('Email Address')
                                        ->email()
                                        ->required()
                                        ->placeholder('hello@yourbakery.com'),
                                    TextInput::make('contact_phone')
                                        ->label('Phone Number')
                                        ->tel()
                                        ->placeholder('+1 (555) 123-4567'),
                                ]),
                                TextInput::make('contact_address')
                                    ->label('Address')
                                    ->placeholder('123 Baker Street, City, State 12345')
                                    ->columnSpanFull(),
                            ]),
                    ])
                    ->afterValidation(function () {
                        $this->saveContactStep();
                    }),

                Step::make('Branding')
                    ->icon('heroicon-o-paint-brush')
                    ->description('Make it yours')
                    ->schema([
                        Section::make('Brand Your Bakery')
                            ->description('Choose colors and upload your logo to personalize your storefront.')
                            ->schema([
                                Grid::make(2)->schema([
                                    ColorPicker::make('brand_color_primary')
                                        ->label('Primary Color')
                                        ->required(),
                                    ColorPicker::make('brand_color_secondary')
                                        ->label('Secondary Color')
                                        ->required(),
                                ]),
                                FileUpload::make('store_logo')
                                    ->label('Bakery Logo')
                                    ->image()
                                    ->directory('logos')
                                    ->maxSize(2048)
                                    ->helperText('Upload your bakery logo (max 2MB). PNG or JPG recommended.')
                                    ->columnSpanFull(),
                            ]),
                    ])
                    ->afterValidation(function () {
                        $this->saveBrandingStep();
                    }),

                Step::make('First Product')
                    ->icon('heroicon-o-cake')
                    ->description('Add something delicious')
                    ->schema([
                        Section::make('Create Your First Product')
                            ->description('Add your first product to get your shop started. You can always add more later.')
                            ->schema([
                                TextInput::make('product_name')
                                    ->label('Product Name')
                                    ->required()
                                    ->placeholder('e.g. Classic Sourdough Loaf')
                                    ->maxLength(255),
                                Textarea::make('product_description')
                                    ->label('Description')
                                    ->placeholder('Describe your product...')
                                    ->rows(3),
                                Grid::make(2)->schema([
                                    TextInput::make('product_price')
                                        ->label('Price')
                                        ->required()
                                        ->numeric()
                                        ->prefix('$')
                                        ->placeholder('12.00'),
                                    Select::make('product_category_id')
                                        ->label('Category')
                                        ->options(fn () => Category::pluck('name', 'id')->toArray())
                                        ->required()
                                        ->createOptionForm([
                                            TextInput::make('name')
                                                ->label('Category Name')
                                                ->required()
                                                ->maxLength(255),
                                            Textarea::make('description')
                                                ->label('Description')
                                                ->rows(2),
                                        ])
                                        ->createOptionUsing(function (array $data): int {
                                            $category = Category::create([
                                                'name' => $data['name'],
                                                'slug' => Str::slug($data['name']),
                                                'description' => $data['description'] ?? null,
                                                'is_active' => true,
                                            ]);
                                            return $category->id;
                                        }),
                                ]),
                            ]),
                    ])
                    ->afterValidation(function () {
                        $this->saveProductStep();
                    }),

                Step::make('Complete')
                    ->icon('heroicon-o-check-circle')
                    ->description('You\'re all set!')
                    ->schema([
                        \Filament\Schemas\Components\View::make('filament.pages.onboarding-complete'),
                    ]),
            ])
                ->submitAction(view('filament.pages.onboarding-submit'))
                ->contained(false),
        ]);
    }

    protected function saveWelcomeStep(): void
    {
        Setting::set('store_name', $this->bakery_name);

        $tenant = tenant();
        if ($tenant) {
            $tenant->name = $this->owner_name;
            $tenant->store_name = $this->bakery_name;
            $tenant->save();
        }
    }

    protected function saveContactStep(): void
    {
        Setting::set('store_email', $this->contact_email);
        Setting::set('store_phone', $this->contact_phone);
        Setting::set('store_address', $this->contact_address);
    }

    protected function saveBrandingStep(): void
    {
        Setting::set('brand_color_primary', $this->brand_color_primary);
        Setting::set('brand_color_secondary', $this->brand_color_secondary);

        $tenant = tenant();
        if ($tenant) {
            $tenant->brand_color_primary = $this->brand_color_primary;
            $tenant->brand_color_secondary = $this->brand_color_secondary;
            $tenant->save();
        }

        if (!empty($this->store_logo)) {
            $logoPath = is_array($this->store_logo) ? collect($this->store_logo)->first() : $this->store_logo;
            if ($logoPath) {
                Setting::set('store_logo', $logoPath);
                if ($tenant) {
                    $tenant->store_logo = $logoPath;
                    $tenant->save();
                }
            }
        }
    }

    protected function saveProductStep(): void
    {
        Product::create([
            'name' => $this->product_name,
            'slug' => Str::slug($this->product_name),
            'description' => $this->product_description,
            'price' => $this->product_price,
            'category_id' => $this->product_category_id,
            'is_active' => true,
        ]);
    }

    public function completeOnboarding(): void
    {
        Setting::set('onboarding_completed_at', now()->toISOString());

        Notification::make()
            ->title('Welcome aboard!')
            ->body('Your bakery is all set up. Time to start baking!')
            ->success()
            ->send();

        $this->redirect(url('/admin'));
    }
}
