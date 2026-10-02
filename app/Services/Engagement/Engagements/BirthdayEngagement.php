<?php

namespace App\Services\Engagement\Engagements;

use App\Actions\Customers\CreateBirthdayCoupon;
use App\Events\Customers\CustomerBirthday;
use App\Models\Customers\Customer;
use App\Services\Engagement\Contracts\CustomerEngagement;
use App\Services\Engagement\Contracts\EngagementRecipient;
use App\Services\Scheduling\BakeryClock;
use App\Services\Settings\TenantSettings;
use Illuminate\Support\Collection;

class BirthdayEngagement implements CustomerEngagement
{
    public function __construct(
        private readonly CreateBirthdayCoupon $createBirthdayCoupon,
        private readonly BakeryClock $clock,
    ) {}

    public function isEnabled(TenantSettings $settings): bool
    {
        return $settings->engagement->birthdayProgramEnabled;
    }

    /** @return Collection<int, EngagementRecipient> */
    public function findRecipients(TenantSettings $settings): Collection
    {
        $today = $this->clock->today();

        return Customer::query()
            ->subscribedToMarketing()
            ->whereNotNull('birthday')
            ->where('email', '!=', '')
            ->whereMonth('birthday', $today->month)
            ->whereDay('birthday', $today->day)
            ->get()
            ->map(fn (Customer $customer): EngagementRecipient => new EngagementRecipient(
                email: $customer->email,
                name: $customer->name,
                model: $customer,
            ));
    }

    public function dispatchForRecipient(EngagementRecipient $recipient, TenantSettings $settings): void
    {
        /** @var Customer $customer */
        $customer = $recipient->model;

        $coupon = $settings->engagement->birthdayCouponEnabled
            ? ($this->createBirthdayCoupon)(
                $customer,
                $settings->engagement->birthdayDiscountPercentage,
                $settings->engagement->birthdayCouponValidDays,
            )
            : null;

        event(new CustomerBirthday($customer, $coupon));
    }
}
