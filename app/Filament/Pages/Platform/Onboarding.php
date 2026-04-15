<?php

namespace App\Filament\Pages\Platform;

use App\Filament\Concerns\RequiresManagerRole;
use App\Filament\Pages\Platform\OnboardingSteps\OnboardingStepRegistry;
use App\Services\Settings\TenantSettings;
use BackedEnum;
use Filament\Notifications\Notification;
use Filament\Pages\Page;
use Filament\Schemas\Components\Wizard;
use Filament\Schemas\Schema;

class Onboarding extends Page
{
    use RequiresManagerRole;

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

    /** @var array<string, mixed> */
    public array $welcome = [];

    /** @var array<string, mixed> */
    public array $contact = [];

    /** @var array<string, mixed> */
    public array $branding = [];

    /** @var array<string, mixed> */
    public array $product = [];

    /** @var array<string, mixed> */
    public array $hours = [];

    /** @var array<string, mixed> */
    public array $compliance = [];

    /** @var array<string, mixed> */
    public array $delivery = [];

    /** @var array<string, mixed> */
    public array $payments = [];

    public function mount(): void
    {
        if (settings('onboarding_completed_at')) {
            $this->redirect(url('/admin'));

            return;
        }

        $defaults = OnboardingStepRegistry::defaults(app(TenantSettings::class));

        foreach ($defaults as $key => $values) {
            if (property_exists($this, $key)) {
                $this->{$key} = $values;
            }
        }
    }

    public function content(Schema $schema): Schema
    {
        return $schema->components([
            Wizard::make(OnboardingStepRegistry::steps($this))
                ->submitAction(view('filament.pages.platform.onboarding-submit'))
                ->contained(false),
        ]);
    }

    public function completeOnboarding(): void
    {
        settings(['onboarding_completed_at' => now()->toISOString()]);

        Notification::make()
            ->title('Welcome aboard!')
            ->body('Your bakery is all set up. Time to start baking!')
            ->success()
            ->send();

        $this->redirect(url('/admin'));
    }
}
