<?php

namespace App\Filament\Pages\Platform;

use App\Filament\Concerns\RequiresManagerRole;
use App\Filament\Pages\Platform\OnboardingSteps\OnboardingStepRegistry;
use App\Services\Settings\SettingsManager;
use App\Services\Settings\TenantSettings;
use BackedEnum;
use Filament\Actions\Action;
use Filament\Notifications\Notification;
use Filament\Pages\Page;
use Filament\Schemas\Components\Wizard;
use Filament\Schemas\Schema;
use Filament\Support\Enums\IconPosition;
use Filament\Support\Icons\Heroicon;

class Onboarding extends Page
{
    use RequiresManagerRole;

    #[\Override]
    protected static bool $shouldRegisterNavigation = false;

    #[\Override]
    protected static ?string $navigationLabel = null;

    #[\Override]
    public static function shouldRegisterNavigation(): bool
    {
        return false;
    }

    #[\Override]
    protected static string|BackedEnum|null $navigationIcon = null;

    /** A focused page: the base HTML shell only, without the admin sidebar and topbar. */
    #[\Override]
    protected static string $layout = 'filament-panels::components.layout.base';

    #[\Override]
    protected string $view = 'filament.pages.platform.onboarding';

    #[\Override]
    protected static ?string $title = 'Set up your bakery';

    #[\Override]
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
        if (resolve(SettingsManager::class)->get('onboarding_completed_at')) {
            $this->redirect(url('/admin'));

            return;
        }

        $defaults = OnboardingStepRegistry::defaults(resolve(TenantSettings::class));

        foreach ($defaults as $key => $values) {
            if (property_exists($this, $key)) {
                $this->{$key} = $values;
            }
        }
    }

    #[\Override]
    public function content(Schema $schema): Schema
    {
        return $schema->components([
            Wizard::make(OnboardingStepRegistry::steps($this))
                ->submitAction(view('filament.pages.platform.onboarding-submit'))
                ->previousAction(fn (Action $action): Action => $action->link())
                ->nextAction(fn (Action $action): Action => $action
                    ->label('Continue')
                    ->icon(Heroicon::ArrowRight)
                    ->iconPosition(IconPosition::After))
                // The step list beside the card follows the wizard's current step.
                ->extraAlpineAttributes(['x-effect' => "\$dispatch('onboarding-step-changed', { index: getStepIndex(step) })"])
                ->hiddenHeader(),
        ]);
    }

    /**
     * @return array<string, mixed>
     */
    #[\Override]
    protected function getViewData(): array
    {
        $store = resolve(TenantSettings::class)->store;

        return [
            'bakeryName' => $store->name,
            'bakeryLogoUrl' => $store->logoUrl(),
            'stepLabels' => OnboardingStepRegistry::labels(),
        ];
    }

    public function completeOnboarding(): void
    {
        resolve(SettingsManager::class)->set('onboarding_completed_at', now()->toISOString());

        Notification::make()
            ->title('Welcome aboard!')
            ->body('Your bakery is all set up. Time to start baking!')
            ->success()
            ->send();

        $this->redirect(url('/admin'));
    }
}
