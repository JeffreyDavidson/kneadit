<?php

namespace App\Filament\Pages;

use App\Enums\ReferralStatus;
use App\Models\Referral;
use BackedEnum;
use Filament\Pages\Page;
use Filament\Schemas\Components\View;
use Filament\Schemas\Schema;
use Illuminate\Database\Eloquent\Collection;

class ReferralProgram extends Page
{
    protected static string|BackedEnum|null $navigationIcon = 'heroicon-o-user-plus';

    protected static string|\UnitEnum|null $navigationGroup = 'Settings';

    protected static ?string $navigationLabel = 'Referral Program';

    protected static ?int $navigationSort = 3;

    protected string $view = 'filament.pages.referral-program';

    protected static ?string $title = 'Referral Program';

    public function content(Schema $schema): Schema
    {
        return $schema->schema([
            View::make('filament.pages.referral-program-content'),
        ]);
    }

    public function getReferralCode(): string
    {
        return tenant()->findOrCreateReferralCode();
    }

    public function getReferralLink(): string
    {
        return 'https://getkneadit.app/ref/'.$this->getReferralCode();
    }

    public function getTotalReferrals(): int
    {
        return Referral::where('referrer_tenant_id', tenant()->id)
            ->whereNotNull('referred_tenant_id')
            ->count();
    }

    public function getCompletedReferrals(): int
    {
        return Referral::where('referrer_tenant_id', tenant()->id)
            ->whereIn('status', [ReferralStatus::Completed, ReferralStatus::Rewarded])
            ->count();
    }

    public function getMonthsEarned(): int
    {
        return Referral::where('referrer_tenant_id', tenant()->id)
            ->where('status', ReferralStatus::Rewarded)
            ->sum('reward_months');
    }

    public function getReferrals(): Collection
    {
        return Referral::where('referrer_tenant_id', tenant()->id)
            ->whereNotNull('referred_tenant_id')
            ->orderByDesc('created_at')
            ->get();
    }
}
