<?php

namespace App\Providers;

use App\Events\Customers\BirthdayDiscountGenerated;
use App\Events\Customers\CustomerBirthday;
use App\Events\Customers\RepeatOrderReminderDue;
use App\Events\Customers\ReviewRequested;
use App\Events\Marketing\CampaignEmailQueued;
use App\Events\Marketing\CateringQuoteRequested;
use App\Events\Marketing\PurchaseOrderRequested;
use App\Events\Orders\OrderCreated;
use App\Events\Platform\HealthCheckFailed;
use App\Events\Platform\PaymentFailed;
use App\Events\Platform\ScheduledCheckinDue;
use App\Events\Platform\StaffInvitationSent;
use App\Events\Platform\TenantOnboarded;
use App\Events\Platform\TrialExpired;
use App\Events\Platform\TrialReminding;
use App\Events\Platform\WeeklyDigestRequested;
use App\Listeners\Customers\SendBirthdayDiscountEmailListener;
use App\Listeners\Customers\SendHappyBirthdayEmailListener;
use App\Listeners\Customers\SendRepeatOrderReminderEmailListener;
use App\Listeners\Customers\SendReviewRequestEmailListener;
use App\Listeners\Marketing\SendCampaignEmailListener;
use App\Listeners\Marketing\SendCateringQuoteEmailListener;
use App\Listeners\Marketing\SendPurchaseOrderEmailListener;
use App\Listeners\Orders\NotifyBakerOfNewOrderListener;
use App\Listeners\Orders\SendOrderPlacedEmailListener;
use App\Listeners\Platform\NotifyPlatformOfNewTenantListener;
use App\Listeners\Platform\SendHealthCheckAlertListener;
use App\Listeners\Platform\SendPaymentFailedAlertListener;
use App\Listeners\Platform\SendPaymentFailedEmailListener;
use App\Listeners\Platform\SendScheduledCheckinEmailListener;
use App\Listeners\Platform\SendStaffInvitationEmailListener;
use App\Listeners\Platform\SendTrialExpiredEmailListener;
use App\Listeners\Platform\SendTrialReminderEmailListener;
use App\Listeners\Platform\SendWeeklyDigestEmailListener;
use App\Listeners\Platform\SendWelcomeBakerEmailListener;
use Illuminate\Support\ServiceProvider;

/**
 * Explicit event-listener registration for SendEmailListener subclasses.
 *
 * These listeners use `handle(object $event)` via the abstract base class,
 * so Laravel's auto-discovery (which reads type-hints) cannot detect them.
 * Other listeners with typed handle() methods are still auto-discovered.
 */
class EventServiceProvider extends ServiceProvider
{
    /** @var array<class-string, list<class-string>> */
    protected array $listen = [
        BirthdayDiscountGenerated::class => [
            SendBirthdayDiscountEmailListener::class,
        ],
        CustomerBirthday::class => [
            SendHappyBirthdayEmailListener::class,
        ],
        RepeatOrderReminderDue::class => [
            SendRepeatOrderReminderEmailListener::class,
        ],
        ReviewRequested::class => [
            SendReviewRequestEmailListener::class,
        ],
        CampaignEmailQueued::class => [
            SendCampaignEmailListener::class,
        ],
        CateringQuoteRequested::class => [
            SendCateringQuoteEmailListener::class,
        ],
        PurchaseOrderRequested::class => [
            SendPurchaseOrderEmailListener::class,
        ],
        OrderCreated::class => [
            SendOrderPlacedEmailListener::class,
            NotifyBakerOfNewOrderListener::class,
        ],
        TenantOnboarded::class => [
            NotifyPlatformOfNewTenantListener::class,
            SendWelcomeBakerEmailListener::class,
        ],
        HealthCheckFailed::class => [
            SendHealthCheckAlertListener::class,
        ],
        PaymentFailed::class => [
            SendPaymentFailedAlertListener::class,
            SendPaymentFailedEmailListener::class,
        ],
        ScheduledCheckinDue::class => [
            SendScheduledCheckinEmailListener::class,
        ],
        StaffInvitationSent::class => [
            SendStaffInvitationEmailListener::class,
        ],
        TrialExpired::class => [
            SendTrialExpiredEmailListener::class,
        ],
        TrialReminding::class => [
            SendTrialReminderEmailListener::class,
        ],
        WeeklyDigestRequested::class => [
            SendWeeklyDigestEmailListener::class,
        ],
    ];

    public function register(): void
    {
        foreach ($this->listen as $event => $listeners) {
            foreach ($listeners as $listener) {
                $this->app['events']->listen($event, $listener);
            }
        }
    }
}
