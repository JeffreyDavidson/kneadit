# Full Application Refactor Audit (Round 3)

## Plan

Third comprehensive audit focusing on views, events/listeners, config, security, and cross-cutting patterns. Organized by severity.

---

## Critical — Security Issues

- [ ] **XSS: Storefront blog body unescaped** — `resources/views/storefront/blog/show.blade.php:46`: Uses `{!! $post->body !!}` without `clean()`. The central blog view correctly uses `{!! clean($post->body) !!}`. Fix: add the `clean()` wrapper.
- [ ] **XSS: Scheduled checkin email** — `resources/views/emails/platform/scheduled-checkin-text.blade.php:14`: `{!! $body !!}` renders unescaped. Add `clean()`.
- [ ] **Sensitive data in logs** — `app/Services/PayPal/TokenManager.php:47`: Logs full PayPal API response body on failure. Should log only status code and error message.

---

## Critical — Bugs (Multi-Tenant Data Leaks)

- [ ] **Hardcoded contact info in order emails** — Three email templates use literal phone/email instead of tenant's `$storePhone`/`$storeEmail`:
  - `emails/orders/order-cancelled.blade.php:87,94,110`: `(555) 123-BAKE` and `hello@kneaditbakery.com`
  - `emails/orders/order-delivered.blade.php:79,80,97`: `hello@kneaditbakery.com`, `@kneaditbakery`, `(555) 123-BAKE`
  - `emails/orders/order-baking.blade.php:68`: `123 Baker Street, Sweet City, SC 12345`
- [ ] **Hardcoded "KneadIt Bakery" brand in email templates** — Four email templates use literal `KneadIt Bakery` in `@section('title')` and body where `$storeName` should be used:
  - `emails/orders/order-cancelled.blade.php:14,107`
  - `emails/orders/order-baking.blade.php:14,78`
  - `emails/orders/order-delivered.blade.php:14,85`
  - `emails/layout.blade.php:6` (default title)
- [ ] **Phantom event/listener in EventServiceProvider** — `app/Providers/EventServiceProvider.php:5,22,55-57`: References non-existent `BirthdayDiscountGenerated` event and `SendBirthdayDiscountEmailListener`. Will cause runtime error if dispatched.
- [ ] **CreateQuickOrder skips OrderCreated event** — `app/Actions/Orders/CreateQuickOrder.php`: Unlike `CreateOrder`, quick orders don't dispatch `OrderCreated`, so no customer email, baker notification, or webhook fires.

---

## High Priority — Behavioral Inconsistencies

- [ ] **Double-queuing mail in 2 listeners** — `app/Listeners/Platform/SendTrialExpiredEmailListener.php:31-34` and `SendTrialReminderEmailListener.php:31-34`: Override `shouldQueueMail()` to `true`, causing mail to be queued a second time from an already-queued listener. Remove the overrides.
- [ ] **CheckPayPalPaymentsCommand type bug** — `app/Console/Commands/Stripe/CheckPayPalPaymentsCommand.php:81,85`: Uses `\stdClass $o` instead of `Order $o` in `tap()` closures for CANCELLED and REFUNDED cases.
- [ ] **CheckPayPalPaymentsCommand wrong directory** — File is in `Commands/Stripe/` but handles PayPal. Move to `Commands/PayPal/` or `Commands/Payments/`.
- [ ] **Missing config key** — `config/monitoring.php` doesn't define `churn_low_health_threshold` even though it's referenced in `ChurnAlertService.php:117`.

---

## High Priority — Blade View Issues

- [ ] **Missing `@session` directive** — 8 files use `@if(session('...'))` instead of `@session('...')`:
  - `billing/plans.blade.php:12`, `storefront/contact.blade.php:66`, `storefront/survey.blade.php:7`
  - `storefront/catering.blade.php:117`, `auth/forgot-password.blade.php:21`, `storefront/driver.blade.php:22`
  - `storefront/gallery.blade.php:119`, `auth/verify-email.blade.php:34`
- [ ] **Missing `@selected` directive** — 12+ occurrences of raw ternary `{{ old('x') === 'y' ? 'selected' : '' }}`:
  - `storefront/catering.blade.php:151-155` (5 occurrences)
  - `storefront/gallery.blade.php:178`
  - `filament/pages/settings/homepage-builder.blade.php:87,126,150,174,205` (5 occurrences)
  - `filament/pages/tools/shopping-list-generator.blade.php:51`
- [ ] **Missing `@checked` directive** — 2 occurrences:
  - `auth/login.blade.php:36`
  - `filament/pages/settings/homepage-builder.blade.php:58`
- [ ] **Inconsistent `@money` usage** — Several views use `number_format()` with manual `$` prefix instead of the custom `@money()` directive:
  - `billing/plans.blade.php:33,36`, `admin/orders/invoice.blade.php:132,138`
  - `filament/resources/orders/view-order-items.blade.php:86`
  - `filament/widgets/catering-pipeline-widget.blade.php:20`
  - `filament/widgets/goal-tracker.blade.php:37,60`
  - `filament/central/pages/view-tenant.blade.php:12`

---

## Medium Priority — Code Duplication in Views

- [ ] **Social media icons duplicated 3 times** — Same SVG blocks in `components/layouts/storefront.blade.php:242-257`, `partials/home/social.blade.php:10-45`, `storefront/about.blade.php:143-159`. Extract to `<x-storefront.social-links>`.
- [ ] **Order items list duplicated 10+ times** — Same iteration pattern across storefront, admin, and email views. Extract storefront version to `<x-storefront.order-items-list>`, email version to `@include('emails.partials.order-items')`.
- [ ] **Delivery/pickup info block duplicated in 3 email templates** — Extract to `@include('emails.partials.delivery-info')`.
- [ ] **Hardcoded tagline** — `storefront layout:228`: `Baked with love, served with care` should come from settings.
- [ ] **Hardcoded fallback about text** — `storefront/about.blade.php:73-74`: Fallback paragraphs should be in a lang file.
- [ ] **Hardcoded billing page title** — `billing/plans.blade.php:6`: `Choose Your Plan -- KneadIt` hardcoded.

---

## Medium Priority — Architecture

- [ ] **Dead `$daysText` variable** — `app/Console/Commands/Platform/CheckTrialExpirationsCommand.php:56`: Assigned but never used.
- [ ] **`CheckChurnAlertsCommand` has 90 lines of business logic** — Lines 19-109 should be in a service or action. A `ChurnAlertService` already exists at `app/Services/Tenants/ChurnAlertService.php` — check if this command duplicates its logic.
- [ ] **`SendPaymentFailedAlertListener` reuses `HealthAlertMail`** — `app/Listeners/Platform/SendPaymentFailedAlertListener.php:19`: Should use a dedicated `PaymentFailedAlertMail` instead.
- [ ] **Duplicate `ShoppingListService` class names** — `app/Services/Orders/ShoppingListService.php` and `app/Services/Inventory/ShoppingListService.php`. Rename or consolidate.
- [ ] **Password validation rule duplicated 3 times** — `Password::min(8)->letters()->numbers()` in `RegisterRequest`, `ResetPasswordRequest`, `AcceptInvitationRequest`. Extract to a shared location.

---

## Medium Priority — Enum Migrations

- [ ] **7 migrations use `enum()` columns instead of `string()`** — All have PHP backed enums + model casts already. Create new migrations to alter these columns to `string`:
  - `platform_announcements.type`, `expenses.category`, `waitlist_entries.status`
  - `incomes.source`, `coupons.type`, `social_posts.platform`, `social_posts.status`

---

## Low Priority — View Quality

- [ ] **Inline `style` attributes in storefront views** — `storefront/contact.blade.php`, `storefront/order.blade.php`, `admin/orders/invoice.blade.php` use inline styles instead of Tailwind classes.
- [ ] **50+ inline `onmouseover`/`onclick` handlers** — Should use CSS `:hover` or Alpine.js instead of inline JavaScript event handlers.
- [ ] **Large embedded JavaScript blocks** — `partials/order-form-script.blade.php` (351 lines), `storefront/order-tracking.blade.php` (87 lines), `storefront/gift-cards.blade.php` (86 lines) should be compiled JS modules.
- [ ] **`ucfirst()` on enum value** — `admin/orders/invoice.blade.php:79`: Should use `$order->payment_method?->getLabel()`.
- [ ] **Config calls in Blade** — `billing/plans.blade.php:19,23,69` and `billing/success.blade.php:15` call `config('kneadit.plans')` directly instead of receiving data from the controller.

---

## Low Priority — Miscellaneous

- [ ] **`ImpersonationToken` model missing factory** — No factory exists at `database/factories/Platform/`.
- [ ] **`TenantSeeder` vs `DatabaseSeeder` mismatch** — `TenantSeeder` includes `BlogPostSeeder` and `BusinessScheduleSeeder` but `DatabaseSeeder::seedTenantData()` does not.
- [ ] **`TenantHealthScore::color()` has UI logic in value object** — `app/ValueObjects/TenantHealthScore.php:37-42`: Returns Filament color names. Move to presenter or widget.
- [ ] **Presenters not `final`** — All 4 presenters are `class` instead of `final class`.
- [ ] **Storefront layout `@php` blocks** — `components/layouts/storefront.blade.php:45-52,158-164,195-203,230-237`: Variable preparation belongs in a ViewComposer.
- [ ] **`app(TenantSettings::class)` used where constructor injection would work** — Several Filament pages and `LoyaltyLedger` use `app()` instead of DI.

---

## What's Done Well (No Changes Needed)

- All events use constructor property promotion with `public readonly`
- All observers registered via `#[ObservedBy]`
- All observers delegate to Actions (no business logic)
- All listeners extend `QueuedListener` (all queued with proper retry/timeout config)
- All commands follow `XxxCommand` naming convention
- All FormRequests use array notation for validation rules
- All FormRequests have explicit `authorize()` methods
- No `env()` calls in Blade views
- No `settings()` calls in Blade views (consistently uses `$settings` DTO)
- All models use `#[Fillable]` (no mass assignment risks)
- All raw SQL uses parameterized queries (no SQL injection risks)
- All 23 DTOs use `final readonly class`
- All 6 value objects use `final readonly class`

---

## Review — Test Suite Reorganization (2026-04-15)

### Summary

Reorganized the test suite to establish strict 1:1 mapping between `app/` files and `tests/` files, eliminating catch-all test files and ensuring consistent naming.

### Phase 1: Moved 9 misplaced test files
- `OnboardingTest` -> `Auth/OnboardingTest`
- `InvoiceTest` -> `Central/InvoiceControllerTest`
- `ReferralTest` -> `Central/ReferralControllerTest`
- `DriverTest` -> `Storefront/DriverDashboardControllerTest`
- `MarkOrderDeliveredControllerTest` -> `Storefront/MarkOrderDeliveredControllerTest`
- `AcceptInvitationControllerTest` -> `Auth/AcceptInvitationControllerTest`
- `StripeWebhookTest` -> `Stripe/StripeWebhookControllerTest`
- `StripeConnectWebhookTest` -> `Stripe/StripeConnectWebhookControllerTest`
- `ShowInvitationControllerTest` -> `Auth/ShowInvitationControllerTest` (merged with `InvitationTest`)
- Normalized `$this->` to Pest global functions in all moved files

### Phase 2: Split 3 catch-all files + renamed 9 files
- **CentralPagesTest.php** (8 tests) -> 6 new controller test files + 2 tests added to existing files
- **BladePhpRefactorTest.php** (14 tests) -> 7 new controller test files + 7 tests merged into existing files
- **StorefrontTest.php** (12 tests) -> menu tests merged into `MenuControllerTest`, contact tests into `ContactControllerTest`
- Root-level `ContactControllerTest` and `BlogControllerTest` merged into Storefront equivalents
- `ChangelogTest` moved from Storefront to Central (correct controller location)
- `OrderFormTest` and `OrderTest` deleted (all tests covered by dedicated per-controller files)
- 7 existing files renamed to match controller names exactly (e.g., `ReviewTest` -> `ReviewsIndexControllerTest`)

### Phase 3: Created 15 new test files
- **Tier 1 (6 files):** ApplyCoupon, CreateBirthdayCoupon, AdjustLoyaltyPoints, RedeemLoyaltyPoints, HandleCheckoutComplete, CancelStripeCheckout
- **Tier 2 (4 files):** AddCustomDomain, RemoveCustomDomain, CreateUser, RecordPayPalInvoice
- **Tier 3 (5 files):** VerifyEmailController, SendVerificationNotificationController, CaptionGeneratorService, QrCodeService, ReauthenticateFromCheckoutSession

### Test Results
- Feature tests: 999 passing
- Integration tests: 1571 passing
- New tests: 38 tests, all passing
- Pint: clean
- Pre-existing failures (unrelated): 1 enum label test, 6 PayPal settings table tests, 4 arch tests
