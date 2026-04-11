<?php

namespace App\Services\Engagement\Engagements;

use App\Actions\Customers\CreateBirthdayCoupon;
use App\Contracts\Engagement\CustomerEngagement;
use App\Contracts\Engagement\EngagementRecipient;
use App\Events\Customers\CustomerBirthday;
use App\Models\Customers\Customer;
use App\Services\Settings\TenantSettings;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Date;

class BirthdayEmailEngagement implements CustomerEngagement
{
    public function __construct(
        private CreateBirthdayCoupon $createBirthdayCoupon,
    ) {}

    public function isEnabled(TenantSettings $settings): bool
    {
        return $settings->engagement->birthdayProgramEnabled;
    }

    /** @return Collection<int, EngagementRecipient> */
    public function findRecipients(TenantSettings $settings): Collection
    {
        $today = Date::today();

        return Customer::query()
            ->whereNotNull('birthday')
            ->where('email', '!=', '')
            ->whereMonth('birthday', $today->month)
            ->whereDay('birthday', $today->day)
            ->get()
            ->map(fn (Customer $customer) => new EngagementRecipient(
                email: $customer->email,
                name: $customer->name,
                model: $customer,
            ));
    }

    public function dispatchForRecipient(EngagementRecipient $recipient, TenantSettings $settings): void
    {
        /** @var Customer $customer */
        $customer = $recipient->model;

        $coupon = ($this->createBirthdayCoupon)(
            $customer,
            $settings->engagement->birthdayDiscountPercentage,
            $settings->engagement->birthdayCouponValidDays,
        );

        CustomerBirthday::dispatch($customer, $coupon);
    }
}
