# Issue #16: Extract abstract SendEmailListener base class

## Analysis

21 total listeners in `app/Listeners/`. After review:

**19 listeners will use the new base class** — all follow the pattern of: get recipient, build mailable, send, log failures.

**2 listeners stay as-is (extend QueuedListener directly):**
- `DispatchOrderCreatedWebhookListener` — dispatches HTTP webhook, not an email
- `SendOrderMessageEmailListener` — complex conditional routing (different recipients based on sender type)

## Design

### Base class: `app/Listeners/SendEmailListener.php`

```
abstract class SendEmailListener extends QueuedListener
```

Three abstract methods:
- `getRecipient(object $event): ?string` — returns email address (null = skip sending)
- `getMailable(object $event): Mailable` — builds and returns the mailable
- `getFailureContext(object $event): array` — returns context for failure logging

One optional override:
- `shouldQueueMail(): bool` — returns `false` by default; `SendTrialExpiredEmailListener` and `SendTrialReminderEmailListener` override to `true` (they use `->queue()` instead of `->send()`)

The `handle()` and `failed()` methods are `final`.

### Handling edge cases in `getRecipient()` and `getMailable()`

- **Null guards**: Returning `null` from `getRecipient()` skips sending (handles `NotifyBakerOfNewOrderListener`, `SendOrderPlacedEmailListener`, etc.)
- **Relationship loading**: `loadMissing()` calls go in `getMailable()` (e.g., `SendOrderPlacedEmailListener`, `SendReviewRequestEmailListener`, `NotifyBakerOfNewOrderListener`)
- **Config-based recipients**: `getRecipient()` returns `config(...)` (e.g., `NotifyPlatformOfNewTenantListener`, `SendPaymentFailedAlertListener`, `SendHealthCheckAlertListener`)
- **Complex mailable construction**: Stays in `getMailable()` (e.g., `SendPaymentFailedAlertListener` building a message string, `SendWelcomeBakerEmailListener` computing trial end date)

## Tasks

- [x] 1. Create `app/Listeners/SendEmailListener.php` base class
- [x] 2. Refactor Customer listeners (4 files)
- [x] 3. Refactor Marketing listeners (3 files)
- [x] 4. Refactor Order listeners — `SendOrderPlacedEmailListener`, `NotifyBakerOfNewOrderListener` (2 files)
- [x] 5. Refactor Platform listeners (10 files)
- [x] 6. Create `EventServiceProvider` for explicit listener registration
- [x] 7. Update 18 unit tests for new log message format
- [x] 8. Run existing tests to verify no regressions
- [x] 9. Run Pint

## Review

### Summary

Extracted an abstract `SendEmailListener` base class to eliminate boilerplate across 19 email listener classes. Each listener now only defines 3 things: recipient, mailable, and failure context.

### New files (2)
- `app/Listeners/SendEmailListener.php` — abstract base class with `final handle()`, `final failed()`, and `shouldQueueMail()` hook
- `app/Providers/EventServiceProvider.php` — explicit event-listener registration (required because `handle(object $event)` prevents Laravel's type-hint-based auto-discovery)

### Modified files

**Listener files (19)** — all refactored from ~25 lines of inline handle/failed logic to ~15-20 lines implementing 3 abstract methods:
- `Customers/`: SendBirthdayDiscountEmailListener, SendHappyBirthdayEmailListener, SendRepeatOrderReminderEmailListener, SendReviewRequestEmailListener
- `Marketing/`: SendCampaignEmailListener, SendCateringQuoteEmailListener, SendPurchaseOrderEmailListener
- `Orders/`: SendOrderPlacedEmailListener, NotifyBakerOfNewOrderListener
- `Platform/`: NotifyPlatformOfNewTenantListener, SendHealthCheckAlertListener, SendPaymentFailedAlertListener, SendPaymentFailedEmailListener, SendScheduledCheckinEmailListener, SendStaffInvitationEmailListener, SendTrialExpiredEmailListener, SendTrialReminderEmailListener, SendWeeklyDigestEmailListener, SendWelcomeBakerEmailListener

**Test files (18)** — updated `Log::warning` message assertions to match new `class_basename($this) . ' failed'` format

**Config (1)** — `bootstrap/providers.php` updated to register `EventServiceProvider`

### Unchanged listeners (2)
- `DispatchOrderCreatedWebhookListener` — not an email, dispatches HTTP webhook
- `SendOrderMessageEmailListener` — complex conditional routing based on sender type

### Key design decisions

1. **`handle(object $event)` + EventServiceProvider** — Using `object` type-hint in the final `handle()` method means Laravel's auto-discovery can't detect these listeners. Solved by explicitly registering them in `EventServiceProvider`. The 2 unchanged listeners still auto-discover via their typed `handle()` methods.

2. **`shouldQueueMail()` hook** — Only `SendTrialExpiredEmailListener` and `SendTrialReminderEmailListener` use `->queue()` instead of `->send()`. Rather than making every listener declare this, a simple boolean override keeps the common case clean.

3. **`SendHealthCheckAlertListener`** gained a `failed()` method (via the base class) that it didn't have before — previously failures were silently lost.

### Test results
3100/3101 passing. 1 pre-existing failure (`QrCodeGeneratorPageTest` — `SubscriptionTier` cast issue, unrelated).
