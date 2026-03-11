<?php

namespace App\Filament\Pages;

use App\Filament\Traits\RequiresRole;
use App\Models\Category;
use App\Models\Product;
use App\Models\Setting;
use App\Traits\HasPlanGating;
use BackedEnum;
use Filament\Forms\Components\Checkbox;
use Filament\Forms\Components\ColorPicker;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\TimePicker;
use Filament\Forms\Components\Toggle;
use Filament\Notifications\Notification;
use Filament\Pages\Page;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Components\Utilities\Get;
use Filament\Schemas\Components\View;
use Filament\Schemas\Components\Wizard;
use Filament\Schemas\Components\Wizard\Step;
use Filament\Schemas\Schema;
use Illuminate\Support\Str;

class Onboarding extends Page
{
    use HasPlanGating, RequiresRole;

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

    // Step 5: Business Hours
    public bool $hours_monday = true;

    public ?string $hours_monday_open = '07:00';

    public ?string $hours_monday_close = '18:00';

    public bool $hours_tuesday = true;

    public ?string $hours_tuesday_open = '07:00';

    public ?string $hours_tuesday_close = '18:00';

    public bool $hours_wednesday = true;

    public ?string $hours_wednesday_open = '07:00';

    public ?string $hours_wednesday_close = '18:00';

    public bool $hours_thursday = true;

    public ?string $hours_thursday_open = '07:00';

    public ?string $hours_thursday_close = '18:00';

    public bool $hours_friday = true;

    public ?string $hours_friday_open = '07:00';

    public ?string $hours_friday_close = '18:00';

    public bool $hours_saturday = false;

    public ?string $hours_saturday_open = '08:00';

    public ?string $hours_saturday_close = '17:00';

    public bool $hours_sunday = false;

    public ?string $hours_sunday_open = '08:00';

    public ?string $hours_sunday_close = '17:00';

    // Step 6: Cottage Food Compliance
    public ?string $cottage_food_state = '';

    public ?string $revenue_cap = '';

    public ?string $license_number = '';

    public ?string $allergy_disclaimer = '';

    public bool $compliance_acknowledged = false;

    // Step 7: Delivery Settings
    public bool $delivery_enabled = false;

    public ?string $delivery_radius = '';

    public ?string $delivery_fee = '';

    public bool $free_delivery_over = false;

    public ?string $free_delivery_threshold = '';

    public ?string $delivery_minimum_order = '';

    public bool $pickup_enabled = true;

    public ?string $pickup_instructions = '';

    // Step 8: PayPal Connection
    public ?string $paypal_client_id = '';

    public ?string $paypal_client_secret = '';

    public bool $paypal_sandbox = true;

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

        // Load existing allergy disclaimer if set, otherwise leave blank
        $this->allergy_disclaimer = Setting::get('allergy_disclaimer', '');

        // Pre-fill operating hours from settings if available
        $existingHours = Setting::get('operating_hours');
        if ($existingHours) {
            $hours = json_decode($existingHours, true);
            if (is_array($hours)) {
                foreach ($hours as $day => $times) {
                    $prop = "hours_{$day}";
                    $this->{$prop} = true;
                    $this->{"hours_{$day}_open"} = $times['open'] ?? '07:00';
                    $this->{"hours_{$day}_close"} = $times['close'] ?? '18:00';
                }
            }
        }
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

                Step::make('Business Hours')
                    ->icon('heroicon-o-clock')
                    ->description('When are you open?')
                    ->schema([
                        Section::make('Set Your Business Hours')
                            ->description('Toggle each day on or off and set your opening and closing times.')
                            ->schema([
                                ...$this->buildDayFields('monday', 'Monday'),
                                ...$this->buildDayFields('tuesday', 'Tuesday'),
                                ...$this->buildDayFields('wednesday', 'Wednesday'),
                                ...$this->buildDayFields('thursday', 'Thursday'),
                                ...$this->buildDayFields('friday', 'Friday'),
                                ...$this->buildDayFields('saturday', 'Saturday'),
                                ...$this->buildDayFields('sunday', 'Sunday'),
                            ]),
                    ])
                    ->afterValidation(function () {
                        $this->saveBusinessHoursStep();
                    }),

                Step::make('Compliance')
                    ->icon('heroicon-o-shield-check')
                    ->description('Cottage food compliance')
                    ->schema([
                        Section::make('Cottage Food Compliance')
                            ->description('Enter your state and compliance details. This helps ensure your bakery meets local regulations.')
                            ->schema([
                                Grid::make(2)->schema([
                                    Select::make('cottage_food_state')
                                        ->label('State')
                                        ->required()
                                        ->searchable()
                                        ->options(self::usStates()),
                                    TextInput::make('revenue_cap')
                                        ->label('Annual Revenue Cap')
                                        ->numeric()
                                        ->prefix('$')
                                        ->required()
                                        ->helperText('Maximum annual revenue allowed under your state\'s cottage food law.'),
                                ]),
                                TextInput::make('license_number')
                                    ->label('License / Permit Number')
                                    ->placeholder('Optional')
                                    ->maxLength(255),
                                Textarea::make('allergy_disclaimer')
                                    ->label('Allergy Disclaimer')
                                    ->required()
                                    ->rows(4)
                                    ->helperText('This disclaimer will be shown to customers on your storefront.'),
                                Checkbox::make('compliance_acknowledged')
                                    ->label('I understand the cottage food laws in my state and confirm that my bakery complies with all applicable regulations.')
                                    ->required()
                                    ->accepted(),
                            ]),
                    ])
                    ->afterValidation(function () {
                        $this->saveComplianceStep();
                    }),

                Step::make('Delivery')
                    ->icon('heroicon-o-truck')
                    ->description('Delivery & pickup options')
                    ->schema([
                        Section::make('Delivery Settings')
                            ->description('Configure how customers receive their orders.')
                            ->schema([
                                Toggle::make('delivery_enabled')
                                    ->label('Do you offer delivery?')
                                    ->live()
                                    ->columnSpanFull(),
                                Grid::make(2)->schema([
                                    TextInput::make('delivery_radius')
                                        ->label('Delivery Radius (miles)')
                                        ->numeric()
                                        ->placeholder('15')
                                        ->visible(fn (Get $get) => $get('delivery_enabled')),
                                    TextInput::make('delivery_fee')
                                        ->label('Flat Delivery Fee')
                                        ->numeric()
                                        ->prefix('$')
                                        ->placeholder('5.00')
                                        ->visible(fn (Get $get) => $get('delivery_enabled')),
                                ]),
                                Grid::make(2)->schema([
                                    Toggle::make('free_delivery_over')
                                        ->label('Free delivery over a certain amount?')
                                        ->live()
                                        ->visible(fn (Get $get) => $get('delivery_enabled')),
                                    TextInput::make('free_delivery_threshold')
                                        ->label('Free Delivery Threshold')
                                        ->numeric()
                                        ->prefix('$')
                                        ->placeholder('50.00')
                                        ->visible(fn (Get $get) => $get('delivery_enabled') && $get('free_delivery_over')),
                                ]),
                                TextInput::make('delivery_minimum_order')
                                    ->label('Minimum Order for Delivery')
                                    ->numeric()
                                    ->prefix('$')
                                    ->placeholder('20.00')
                                    ->visible(fn (Get $get) => $get('delivery_enabled')),

                                Toggle::make('pickup_enabled')
                                    ->label('Do you offer pickup?')
                                    ->live()
                                    ->columnSpanFull(),
                                Textarea::make('pickup_instructions')
                                    ->label('Pickup Instructions')
                                    ->placeholder('e.g. Pick up at the side door, ring the bell...')
                                    ->rows(3)
                                    ->visible(fn (Get $get) => $get('pickup_enabled'))
                                    ->columnSpanFull(),
                            ]),
                    ])
                    ->afterValidation(function () {
                        if (! $this->delivery_enabled && ! $this->pickup_enabled) {
                            throw \Illuminate\Validation\ValidationException::withMessages([
                                'pickup_enabled' => 'You must enable at least one option: pickup or delivery.',
                            ]);
                        }
                        $this->saveDeliveryStep();
                    }),

                Step::make('PayPal')
                    ->icon('heroicon-o-credit-card')
                    ->description('Connect payments')
                    ->schema([
                        Section::make('PayPal Connection')
                            ->description('Connect your PayPal Business account to accept online payments. You can skip this step and set it up later.')
                            ->schema([
                                TextInput::make('paypal_client_id')
                                    ->label('PayPal Client ID')
                                    ->placeholder('Your PayPal Client ID')
                                    ->maxLength(255)
                                    ->helperText('Find this in your PayPal Developer Dashboard under Apps & Credentials.'),
                                TextInput::make('paypal_client_secret')
                                    ->label('PayPal Client Secret')
                                    ->password()
                                    ->placeholder('Your PayPal Client Secret')
                                    ->maxLength(255),
                                Toggle::make('paypal_sandbox')
                                    ->label('Sandbox Mode (Testing)')
                                    ->helperText('Enable this to test payments without real money. Disable when you\'re ready to go live.')
                                    ->default(true),
                            ])
                            ->footerActions([])
                            ->footerActionsAlignment(null),
                    ])
                    ->afterValidation(function () {
                        $this->savePayPalStep();
                    }),

                Step::make('Preview')
                    ->icon('heroicon-o-eye')
                    ->description('Review your storefront')
                    ->schema([
                        View::make('filament.pages.onboarding-preview')
                            ->viewData([
                                'page' => $this,
                            ]),
                    ]),

                Step::make('Complete')
                    ->icon('heroicon-o-check-circle')
                    ->description('You\'re all set!')
                    ->schema([
                        View::make('filament.pages.onboarding-complete'),
                    ]),
            ])
                ->submitAction(view('filament.pages.onboarding-submit'))
                ->contained(false),
        ]);
    }

    protected function buildDayFields(string $day, string $label): array
    {
        return [
            Grid::make(3)->schema([
                Toggle::make("hours_{$day}")
                    ->label($label)
                    ->live(),
                TimePicker::make("hours_{$day}_open")
                    ->label('Open')
                    ->seconds(false)
                    ->visible(fn (Get $get) => $get("hours_{$day}")),
                TimePicker::make("hours_{$day}_close")
                    ->label('Close')
                    ->seconds(false)
                    ->visible(fn (Get $get) => $get("hours_{$day}")),
            ]),
        ];
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

        if (! empty($this->store_logo)) {
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
        $slug = Str::slug($this->product_name);

        Product::updateOrCreate(
            ['slug' => $slug],
            [
                'name' => $this->product_name,
                'description' => $this->product_description,
                'price' => $this->product_price,
                'category_id' => $this->product_category_id,
                'is_active' => true,
            ]
        );
    }

    protected function saveBusinessHoursStep(): void
    {
        $days = ['monday', 'tuesday', 'wednesday', 'thursday', 'friday', 'saturday', 'sunday'];
        $hours = [];

        foreach ($days as $day) {
            if ($this->{"hours_{$day}"}) {
                $hours[$day] = [
                    'open' => $this->{"hours_{$day}_open"} ?? '07:00',
                    'close' => $this->{"hours_{$day}_close"} ?? '18:00',
                ];
            }
        }

        Setting::set('operating_hours', json_encode($hours));
    }

    protected function saveComplianceStep(): void
    {
        Setting::set('cottage_food_state', $this->cottage_food_state);
        Setting::set('revenue_cap', $this->revenue_cap);
        Setting::set('license_number', $this->license_number);
        Setting::set('allergy_disclaimer', $this->allergy_disclaimer);
        Setting::set('compliance_acknowledged', $this->compliance_acknowledged ? '1' : '0');
    }

    protected function saveDeliveryStep(): void
    {
        Setting::set('delivery_enabled', $this->delivery_enabled ? '1' : '0');
        Setting::set('delivery_radius', $this->delivery_radius);
        Setting::set('delivery_fee', $this->delivery_fee);
        Setting::set('free_delivery_threshold', $this->free_delivery_over ? $this->free_delivery_threshold : null);
        Setting::set('delivery_minimum_order', $this->delivery_minimum_order);
        Setting::set('pickup_enabled', $this->pickup_enabled ? '1' : '0');
        Setting::set('pickup_instructions', $this->pickup_instructions);
    }

    protected function savePayPalStep(): void
    {
        Setting::set('paypal_client_id', $this->paypal_client_id);
        Setting::set('paypal_client_secret', $this->paypal_client_secret);
        Setting::set('paypal_sandbox', $this->paypal_sandbox ? '1' : '0');

        $tenant = tenant();
        if ($tenant) {
            if ($this->paypal_client_id) {
                $tenant->paypal_client_id = $this->paypal_client_id;
            }
            if ($this->paypal_client_secret) {
                $tenant->paypal_client_secret = $this->paypal_client_secret;
            }
            $tenant->save();
        }
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

    public static function usStates(): array
    {
        return [
            'AL' => 'Alabama', 'AK' => 'Alaska', 'AZ' => 'Arizona', 'AR' => 'Arkansas',
            'CA' => 'California', 'CO' => 'Colorado', 'CT' => 'Connecticut', 'DE' => 'Delaware',
            'FL' => 'Florida', 'GA' => 'Georgia', 'HI' => 'Hawaii', 'ID' => 'Idaho',
            'IL' => 'Illinois', 'IN' => 'Indiana', 'IA' => 'Iowa', 'KS' => 'Kansas',
            'KY' => 'Kentucky', 'LA' => 'Louisiana', 'ME' => 'Maine', 'MD' => 'Maryland',
            'MA' => 'Massachusetts', 'MI' => 'Michigan', 'MN' => 'Minnesota', 'MS' => 'Mississippi',
            'MO' => 'Missouri', 'MT' => 'Montana', 'NE' => 'Nebraska', 'NV' => 'Nevada',
            'NH' => 'New Hampshire', 'NJ' => 'New Jersey', 'NM' => 'New Mexico', 'NY' => 'New York',
            'NC' => 'North Carolina', 'ND' => 'North Dakota', 'OH' => 'Ohio', 'OK' => 'Oklahoma',
            'OR' => 'Oregon', 'PA' => 'Pennsylvania', 'RI' => 'Rhode Island', 'SC' => 'South Carolina',
            'SD' => 'South Dakota', 'TN' => 'Tennessee', 'TX' => 'Texas', 'UT' => 'Utah',
            'VT' => 'Vermont', 'VA' => 'Virginia', 'WA' => 'Washington', 'WV' => 'West Virginia',
            'WI' => 'Wisconsin', 'WY' => 'Wyoming',
        ];
    }
}
