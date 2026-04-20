<?php

namespace App\Filament\Pages\Engagement;

use App\Actions\Tenants\GenerateReferralCode;
use App\Filament\Concerns\RequiresManagerRole;
use App\Models\Customers\Referral;
use BackedEnum;
use Filament\Pages\Page;
use Filament\Schemas\Components\View;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Illuminate\Database\Eloquent\Collection;

class ReferralProgram extends Page
{
    use RequiresManagerRole;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedUserPlus;

    protected static string|\UnitEnum|null $navigationGroup = 'Settings';

    protected static ?string $navigationLabel = 'Referral Program';

    protected static ?int $navigationSort = 3;

    protected string $view = 'filament.pages.engagement.referral-program';

    protected static ?string $title = 'Referral Program';

    public function content(Schema $schema): Schema
    {
        return $schema->schema([
            View::make('filament.pages.engagement.referral-program-content'),
        ]);
    }

    public function getReferralCode(): string
    {
        return resolve(GenerateReferralCode::class)(tenant());
    }

    public function getReferralLink(): string
    {
        return config('app.url') . '/ref/' . $this->getReferralCode();
    }

    public function getTotalReferrals(): int
    {
        return Referral::query()->where('referrer_tenant_id', tenant()->id)
            ->whereNotNull('referred_tenant_id')
            ->count();
    }

    public function getCompletedReferrals(): int
    {
        return Referral::query()->where('referrer_tenant_id', tenant()->id)
            ->successful()
            ->count();
    }

    public function getMonthsEarned(): int
    {
        return (int) Referral::query()->where('referrer_tenant_id', tenant()->id)
            ->rewarded()
            ->sum('reward_months');
    }

    /** @return Collection<int, Referral> */
    public function getReferrals(): Collection
    {
        return Referral::query()->where('referrer_tenant_id', tenant()->id)
            ->whereNotNull('referred_tenant_id')->latest()
            ->get();
    }
}
