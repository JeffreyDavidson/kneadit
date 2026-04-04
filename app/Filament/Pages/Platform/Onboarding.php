<?php

namespace App\Filament\Pages\Platform;

use App\Enums\Staff\UserRole;
use App\Models\Inventory\Category;
use App\Services\Tenants\SaveOnboardingStep;
use BackedEnum;
use Filament\Forms\Components\Checkbox;
use Filament\Forms\Components\CheckboxList;
use Filament\Forms\Components\ColorPicker;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\TimePicker;
use Filament\Forms\Components\Toggle;
use Filament\Notifications\Notification;
use Filament\Pages\Page;
use Filament\Schemas\Components\Component;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Components\Utilities\Get;
use Filament\Schemas\Components\View;
use Filament\Schemas\Components\Wizard;
use Filament\Schemas\Components\Wizard\Step;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

class Onboarding extends Page
{
    public static function canAccess(): bool
    {
        $user = Auth::user();

        if (! $user || ! $user->hasMinRole(UserRole::Manager)) {
            return false;
        }

        return true;
    }

    protected static bool $shouldRegisterNavigation = false;

    protected static ?string $navigationLabel = null;

    public static function shouldRegisterNavigation(): bool
    {
        return false;
    }

    protected static string|BackedEnum|null $navigationIcon = null;

    protected string $view = 'filament.pages.platform.onboarding';

    protected static ?string $title = 'Welcome to KneadIt';

    protected static ?string $slug = 'onboarding';

    // Step 1: Welcome
    public ?string $subdomain = '';

    public ?string $bakery_name = '';

    public ?string $owner_name = '';

    // Step 2: Contact Info
    public ?string $contact_email = '';

    public ?string $contact_phone = '';

    public ?string $contact_address = '';

    // Step 3: Branding
    public ?string $brand_color_primary = '#6b4c3b';

    public ?string $brand_color_secondary = '#d4a574';

    /** @var array<string, mixed> */
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
    /** @var array<int, string>|null */
    public ?array $payment_methods = ['cash'];

    public ?string $paypal_client_id = '';

    public ?string $paypal_client_secret = '';

    public bool $paypal_sandbox = true;

    public function mount(): void
    {
        // If onboarding is already complete, redirect to dashboard
        if (settings('onboarding_completed_at')) {
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
        $this->bakery_name = settings('store_name', $this->bakery_name);
        $this->contact_email = settings('store_email', $this->contact_email);
        $this->contact_phone = settings('store_phone', '');
        $this->contact_address = settings('store_address', '');

        // Load existing allergy disclaimer if set, otherwise leave blank
        $this->allergy_disclaimer = settings('allergy_disclaimer', '');

        // Pre-fill operating hours from settings if available
        $existingHours = settings('operating_hours');
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
                    ->icon(Heroicon::OutlinedHandRaised)
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
                    ->icon(Heroicon::OutlinedEnvelope)
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
                    ->icon(Heroicon::OutlinedPaintBrush)
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
                                    ->acceptedFileTypes(['image/jpeg', 'image/png', 'image/webp'])
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
                    ->icon(Heroicon::OutlinedCake)
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
                                        ->options(fn () => Category::query()->pluck('name', 'id')->toArray())
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
                                            $category = Category::query()->create([
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
                    ->icon(Heroicon::OutlinedClock)
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
                    ->icon(Heroicon::OutlinedShieldCheck)
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
                    ->icon(Heroicon::OutlinedTruck)
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
                                    ->required(fn (Get $get) => $get('pickup_enabled'))
                                    ->visible(fn (Get $get) => $get('pickup_enabled'))
                                    ->columnSpanFull(),
                            ]),
                    ])
                    ->afterValidation(function () {
                        if (! $this->delivery_enabled && ! $this->pickup_enabled) {
                            throw ValidationException::withMessages([
                                'pickup_enabled' => 'You must enable at least one option: pickup or delivery.',
                            ]);
                        }
                        $this->saveDeliveryStep();
                    }),

                Step::make('Payments')
                    ->icon(Heroicon::OutlinedCreditCard)
                    ->description('How you get paid')
                    ->schema([
                        Section::make('Payment Collection')
                            ->description('Choose how you want to collect payments from customers.')
                            ->schema([
                                CheckboxList::make('payment_methods')
                                    ->label('Payment Methods')
                                    ->helperText('Select all that apply — offer your customers multiple ways to pay.')
                                    ->options([
                                        'stripe' => 'Stripe — Credit cards, Apple Pay, Google Pay',
                                        'paypal' => 'PayPal — Accept payments through PayPal Business',
                                        'cash' => 'Cash / Manual — In person (cash, Venmo, Zelle, etc.)',
                                    ])
                                    ->descriptions([
                                        'stripe' => 'Connect your own Stripe account. Payments go directly to you.',
                                        'paypal' => 'Invoices sent automatically. Requires a PayPal Business account.',
                                        'cash' => 'You handle payment collection outside the platform.',
                                    ])
                                    ->required()
                                    ->live()
                                    ->columnSpanFull(),

                                Section::make('Stripe Connection')
                                    ->description('Connect your own Stripe account — payments go directly to you, not us.')
                                    ->schema([
                                        View::make('filament.pages.shared.stripe-connect-status'),
                                    ])
                                    ->visible(fn (Get $get) => in_array('stripe', $get('payment_methods') ?? [])),

                                Section::make('PayPal Connection')
                                    ->description('Connect your PayPal Business account.')
                                    ->schema([
                                        TextInput::make('paypal_client_id')
                                            ->label('PayPal Client ID')
                                            ->placeholder('Your PayPal Client ID')
                                            ->maxLength(255)
                                            ->helperText('Find this in your PayPal Developer Dashboard under Apps & Credentials.')
                                            ->required(fn (Get $get) => in_array('paypal', $get('payment_methods') ?? [])),
                                        TextInput::make('paypal_client_secret')
                                            ->label('PayPal Client Secret')
                                            ->password()
                                            ->placeholder('Your PayPal Client Secret')
                                            ->maxLength(255)
                                            ->required(fn (Get $get) => in_array('paypal', $get('payment_methods') ?? [])),
                                        Toggle::make('paypal_sandbox')
                                            ->label('Sandbox Mode (Testing)')
                                            ->helperText('Enable this to test payments without real money. Disable when you\'re ready to go live.')
                                            ->default(true),
                                    ])
                                    ->visible(fn (Get $get) => in_array('paypal', $get('payment_methods') ?? [])),
                            ])
                            ->footerActions([])
                            ->footerActionsAlignment(null),
                    ])
                    ->afterValidation(function () {
                        $this->savePaymentStep();
                    }),

                Step::make('Preview')
                    ->icon(Heroicon::OutlinedEye)
                    ->description('Review your storefront')
                    ->schema([
                        View::make('filament.pages.platform.onboarding-preview')
                            ->viewData([
                                'page' => $this,
                            ]),
                    ]),

                Step::make('Complete')
                    ->icon(Heroicon::OutlinedCheckCircle)
                    ->description('You\'re all set!')
                    ->schema([
                        View::make('filament.pages.platform.onboarding-complete'),
                    ]),
            ])
                ->submitAction(view('filament.pages.platform.onboarding-submit'))
                ->contained(false),
        ]);
    }

    /** @return array<int, Component> */
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
        resolve(SaveOnboardingStep::class)->welcome($this->bakery_name, $this->owner_name);
    }

    protected function saveContactStep(): void
    {
        resolve(SaveOnboardingStep::class)->contact($this->contact_email, $this->contact_phone, $this->contact_address);
    }

    protected function saveBrandingStep(): void
    {
        $logoPath = ! empty($this->store_logo) ? collect($this->store_logo)->first() : null;

        resolve(SaveOnboardingStep::class)->branding($this->brand_color_primary, $this->brand_color_secondary, $logoPath);
    }

    protected function saveProductStep(): void
    {
        resolve(SaveOnboardingStep::class)->product($this->product_name, $this->product_description, $this->product_price, $this->product_category_id);
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

        resolve(SaveOnboardingStep::class)->businessHours($hours);
    }

    protected function saveComplianceStep(): void
    {
        resolve(SaveOnboardingStep::class)->compliance(
            $this->cottage_food_state,
            $this->revenue_cap,
            $this->license_number,
            $this->allergy_disclaimer,
            $this->compliance_acknowledged,
        );
    }

    protected function saveDeliveryStep(): void
    {
        resolve(SaveOnboardingStep::class)->delivery(
            $this->delivery_enabled,
            $this->delivery_radius,
            $this->delivery_fee,
            $this->free_delivery_over ? $this->free_delivery_threshold : null,
            $this->delivery_minimum_order,
            $this->pickup_enabled,
            $this->pickup_instructions,
        );
    }

    protected function savePaymentStep(): void
    {
        resolve(SaveOnboardingStep::class)->payments(
            $this->payment_methods ?? ['cash'],
            $this->paypal_client_id,
            $this->paypal_client_secret,
            $this->paypal_sandbox,
        );
    }

    public function completeOnboarding(): void
    {
        resolve(SaveOnboardingStep::class)->complete();

        Notification::make()
            ->title('Welcome aboard!')
            ->body('Your bakery is all set up. Time to start baking!')
            ->success()
            ->send();

        $this->redirect(url('/admin'));
    }

    /** @return array<string, mixed> */
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
