# KneadIt architecture

## System shape

KneadIt is one Laravel application serving two related surfaces:

1. The **central application** serves the marketing site, bakery registration and onboarding, subscription billing, the public bakery directory, and the platform-admin Filament panel.
2. A **tenant application** serves a bakery's storefront, tenant API, customer account flows, and bakery-admin Filament panel.

Both surfaces share application code. The request host and tenancy middleware decide which database context is active.

## Data and tenancy boundaries

The central connection is named `central`. It contains the platform tenant and domain records plus central concerns such as platform settings and subscription state. Every bakery has a separate SQLite database. `config/tenancy.php` uses `TenantSQLiteDatabaseManager`, with files rooted at `TENANT_DB_PATH` or `database_path()` when the variable is unset. `TenantDatabasePath` rejects path separators/traversal, and the manager refuses symlinks before connecting.

Tenancy bootstraps five Laravel facilities:

- The database connection switches to the tenant database.
- cache operations receive a tenant-specific tag.
- the private CSV import disk receives a tenant-specific root.
- queued work carries tenant context through `QueueTenancyBootstrapper`.
- generated URLs point at the bakery through `TenantUrlBootstrapper` (see below).

`TenantUrlBootstrapper` forces the URL root and scheme to the bakery's primary storefront while tenancy is active, so `route()`, `url()` and signed URLs built by the scheduler, queue workers and central panel actions (`withinTenant()`) point at the bakery instead of `APP_URL`, where bakery routes do not exist. `TenantUrlGenerator::primaryStorefront()` picks the URL: the bakery's custom domain when `tenants.custom_domain` is set, `tenants.custom_domain_verified_at` is set (when last checked, its DNS pointed at the server and `https://{domain}/up` answered 2xx over a valid certificate) and a `domains` row ties it to that bakery, otherwise `{subdomain}.{tenant domain}`. The scheme and tenant domain come from `APP_URL` and `tenancy.tenant_domain`, and a custom domain drops the platform port. The bootstrapper does nothing when the current request is already on a non-central host (the bakery's own subdomain or custom domain), so HTTP requests keep generating URLs on the host the customer is using, and `revert()` clears the override when tenancy ends. Signed bakery links (`signed`, not `signed:relative`) are therefore signed against the same host the customer opens. `MarketingUnsubscribeLinks` builds its host from the same resolver. The "Powered by KneadIt" link in `BaseMailable` uses `APP_URL` directly so it keeps pointing at the platform.

Filesystem tenancy is deliberately scoped to the `imports` disk. Existing local/public asset URLs keep their established behavior, while sensitive imports cannot cross tenant roots.

Tenant creation runs database creation and tenant migrations synchronously from `TenancyServiceProvider`. Tenant deletion removes the tenant database synchronously. Central migrations live in `database/migrations`; tenant migrations live in `database/migrations/tenant` and are run through `tenants:migrate`.

### Request flow

The global web stack prepends `InitializeTenancyIfNeeded` before session startup:

```text
HTTP request
  -> inspect Host
  -> central domain? retain central connection
  -> tenant/custom domain? identify tenant and initialize tenancy
  -> start session/authentication in the selected database context
  -> route/controller/Filament/Livewire handling
  -> response security headers and actor context
```

Central domains are configured in `config/tenancy.php`; production includes `getkneadit.app` and `www.getkneadit.app`, while local development uses `app.getkneadit.test` for the application and retains `kneadit.test` as the tenant-development domain. The separate marketing site uses `getkneadit.test`. Tenant routes additionally apply `InitializeTenancyByDomainOrSubdomain` and reject access from central domains. This supports both bakery subdomains and domain records representing custom domains. `AddCustomDomain` normalizes a bakery's custom domain and rejects, with a validation error, anything that is not a valid hostname, is a central domain or a subdomain of the tenant domain, or already belongs to another bakery; replacing a domain releases the old record and Forge alias. A new or replaced domain starts unverified (`AddCustomDomain` and `RemoveCustomDomain` clear `custom_domain_verified_at`). `VerifyCustomDomain` asks `CustomDomainService::verify()` for a `DomainCheck` (`Verified`, `DnsMissing` or `HttpsUnavailable`): DNS through the `DnsResolver` contract (`PhpDnsResolver` in production), then, only if DNS is right, HTTPS through the `HttpsProbe` contract (`HttpHttpsProbe`: `GET https://{domain}/up`, certificate checked, no redirects followed, 5 s timeout; faked in tests with `fakeDnsRecords()` and `fakeHttpsProbe()`). It sets the timestamp only when both pass and clears it otherwise. The Settings → Custom Domain page runs it on load, on save and on "Verify domain", shows the DNS and HTTPS parts separately, and offers **Request SSL certificate** (Forge) when DNS is right but HTTPS is not; the daily `tenants:verify-custom-domains` command re-checks every bakery with a custom domain, clearing the timestamp (and logging the reason, `dns` or `https`) when a domain stops qualifying. In the central panel the bakery form shows the custom domain read-only (disabled, never saved from the form) with its verification status; platform admins change it from the **Set custom domain**, **Remove custom domain** and **Verify domain** and **Request SSL certificate** actions on the bakery's Edit page, which call `AddCustomDomain`, `RemoveCustomDomain` and `VerifyCustomDomain`, so the admin panel gets the same validation, `domains` row, Forge alias and verification reset.

An unknown tenant domain returns 404. If a central tenant record exists but its SQLite file does not, local development recreates and migrates it automatically. Production returns 503 and instructs the operator to run `php artisan tenants:doctor --fix`.

The root URL is deliberately universal: the global middleware establishes central or tenant context once, then `RootController` serves the platform landing page or bakery storefront without re-running tenancy middleware.

### Route organization

`routes/web.php` is the central route composition entry point. It loads:

- `routes/central/auth.php` for platform-user registration, login, email verification, and password reset.
- `routes/central/platform.php` for onboarding, platform-admin exports, backups, maintenance previews, and impersonation.
- `routes/central/marketing.php` for the central landing page, legal pages, directory, blog, referrals, contact, and CSP reports.
- `routes/central/seo.php` for the sitemap and robots response.
- `routes/billing.php` for subscription billing and Stripe webhooks.

`routes/tenant.php` is the tenant route composition entry point. Its outer group owns tenant initialization and central-domain protection before loading:

- `routes/tenant/access.php` for PWA metadata, invitations, tenant impersonation consumption, driver links, campaign previews, and Stripe Connect.
- `routes/tenant/admin.php` for authenticated tenant admin utilities such as invoices and product labels.
- `routes/tenant/storefront.php` for public bakery content and storefront commerce.
- `routes/tenant/account.php` for customer account authentication, profile, orders, and email verification.
- `routes/tenant/orders.php` for checkout, order access, payment callbacks, cart, capacity, and order-related AJAX endpoints.
- `routes/tenant/api.php` for tenant JSON endpoints, split into read and write throttle groups.

Routes that access tenant models belong under the tenant loader, even when their controllers are used by an admin-facing page. This keeps route middleware, model binding, and database tenancy context aligned.

### HTTP controller organization

HTTP controllers are grouped first by application surface and then by concern:

- `app/Http/Controllers/Central/` contains platform-facing controllers. `Auth/` and `Onboarding/` hold the central authentication and signup lifecycle; other controllers remain at the central surface root when they span a single platform concern.
- `app/Http/Controllers/Tenant/` contains tenant-bound controllers. `Storefront/` owns public bakery pages and customer account flows, while `Admin/`, `Api/`, `Catering/`, `Invitations/`, `Marketing/`, and `Orders/` make their route surface explicit.
- `app/Http/Controllers/Billing/` and `app/Http/Controllers/Stripe/` are provider or subscription boundaries. They may invoke domain actions but do not define tenant presentation ownership.

The controller namespace should match the route surface and the mirrored `tests/Feature/Http/Controllers/` path. New tenant controllers belong under `Tenant/<Surface>/`; do not add new top-level tenant controller directories.

## Application layers

KneadIt favors explicit Laravel boundaries rather than a generic service/repository layer:

- **Controllers and Filament pages** coordinate HTTP/UI behavior and remain thin.
- **Form Requests** validate and translate request data.
- **Actions** under `app/Actions/<Domain>` own writes and business transitions. They are generally invokable and container-resolved.
- **Queries** and custom Eloquent **Builders** own reusable read behavior.
- **Services** integrate external systems or encapsulate cohesive computations and orchestration.
- **DTOs and value objects** carry validated shapes and domain values.
- **Events/listeners** handle consequences that follow a completed domain action.
- **Policies and gates** enforce model and platform authorization.
- **Presenters/ViewModels/components** shape complex output where the view earns a separate boundary.

Models, actions, services, enums, builders, queries, policies, factories, and tests are grouped by domain. Tests mirror the `app` structure across the applicable unit, integration, and feature suites.

Application-wide framework wiring is split by responsibility in `app/Providers/`: `ApplicationBindingsServiceProvider` owns container bindings, `InfrastructureServiceProvider` owns queue, cache, tenancy, and payment infrastructure hooks, `RateLimitServiceProvider` owns named throttles, and `AppServiceProvider` owns application features and UI hooks. Keep new bootstrapping in the narrowest provider rather than expanding a catch-all provider.

Shared test setup follows the same rule: `tests/Pest.php` keeps suite-wide lifecycle configuration, while domain or environment-specific fixtures belong in named files under `tests/Support/Bootstrap/` or `tests/Support/`. Tenant database cleanup is implemented there so feature and integration tests do not each need to know which persistent browser fixtures must be preserved.

## Major domains

See [Domain ownership map](domain-ownership.md) for the ownership rules, current organization audit, and sequenced refactoring candidates.
See [Deep application audit](deep-application-audit.md) for the broader Laravel, class-design, persistence, UI, testing, and operations review.
See [Application refactoring roadmap](refactoring-roadmap.md) for the completed workstreams, delivery order, verification gates, and remaining documentation decisions.

- **Platform and tenancy:** bakery registration, onboarding, domains, plans, trials, subscriptions, referrals, support, announcements, audits, impersonation, backups, and health.
- **Storefront and content:** bakery home pages, menus, blogs, galleries, catering, gift cards, reviews, policies, branding, and PWA metadata.
  Gift cards are issued by staff in the admin panel; the storefront Gift Cards page only checks balances and points customers to the bakery, because there is no paid online gift card checkout yet.
- **Orders:** carts, checkout, capacity and stock validation, discounts, fulfillment, order messaging, tracking, invoices, payment state, and refunds.
- **Inventory and production:** products, categories, ingredients, recipes, suppliers, stock adjustments, waitlists, seasonal items, and production planning.
- **Customers and engagement:** customer profiles, favorites, notes, referrals, loyalty, campaigns, surveys, reviews, reminders, contact messages, and catering inquiries.
  Email campaigns have two separate paths. Bakeries email their own customers with customer campaigns (`CustomerCampaign` and `SendCustomerCampaign`, stored in the tenant database and managed in the bakery-admin panel). Platform email campaigns (`EmailCampaign`, stored centrally and managed only in the central panel) go to bakers: `SendEmailCampaign` picks the bakeries in the campaign's segment (All, Starter, Growth, Pro, Trial or Inactive), emails each bakery's owner address (`tenants.email`) once using `PlatformCampaignMail`, and records the unique count as `recipient_count`. It never enters a tenant database and throws `PlatformCampaignContextException` if called inside a tenant context.
- **Financial:** income, expenses, coupons, gift cards, refunds, reporting, tax export, Stripe, and PayPal.
- **Operations and staff:** schedules, blocked dates, holidays, capacity, check-ins, staff invitations and roles, activity logs, and webhook delivery.
- **Analytics:** page and product-impression records use a keyed, pseudonymous visitor identifier. Raw network/device identifiers are not persisted, recording failures are reported without breaking storefront responses, and scheduled tenant-wide retention bounds stored history.
- **Storage:** intentionally public assets remain on the established `public` disk. Sensitive CSV imports use a dedicated private disk that is neither directly served nor shared across tenant roots; all configured disks fail loudly on storage errors.

## Order lifecycle

Storefront requests are validated by `StoreOrderRequest` (the API uses `StoreApiOrderRequest`) and converted to `CreateOrderData`. Both requests apply `ProductAvailableOnDeliveryDate` to each item: a product with seasonal windows can only be ordered for a delivery date inside one of them (inclusive), and products with no windows are always available (`ProductQueryBuilder::availableOn`). `CreateOrder` then executes a database transaction containing an ordered Laravel pipeline:

1. Calculate totals and enforce the minimum order amount. Delivery is priced from the bakery's Delivery Fee Tiers setting (`delivery_tier` is the tier's position) and is free once the subtotal reaches the free-delivery minimum; delivery orders are rejected when delivery is turned off.
2. Validate date capacity and stock availability.
3. Apply sitewide sales, coupons, gift cards, referrals, and tier perks.
4. Resolve the customer and persist the order.
5. Record coupon/gift-card/referral effects and persist line items.
6. Mark the cart converted.

Customer referrals follow the order through its lifecycle. `ApplyReferral` discounts only new customers: an email with a prior non-cancelled order, or an already-referred customer whose referral wasn't cancelled, gets no discount. `PersistReferral` records the referral as Pending and fires nothing. When the order is delivered, `CompleteCustomerReferralListener` marks it Completed and fires `CustomerReferralCompleted`; when the order is cancelled, `CancelCustomerReferralListener` marks it Cancelled and no reward is issued. `SendCustomerReferralRewardEmailListener` mails the referrer's coupon, which `RewardReferrer` creates at most once per referral (a repeated event or retried job reuses the existing coupon).

If capacity rejects the request, the pipeline returns no order. If no cart line is orderable (every product was deactivated or removed since the cart was built), `CalculateOrderTotals` throws `NoOrderableItemsException`, so the customer is told to review the cart instead of that the date is full. Other domain validation failures return targeted form errors. A successful transaction logs the placement and emits `OrderCreated`. The current session is granted access to the resulting order before redirecting to payment or confirmation.

Staff quick orders skip this pipeline: the Quick Order form (`QuickOrderForm`) offers a Delivery Distance select built from the same Delivery Fee Tiers, and `CreateQuickOrder` prices delivery with `OrderSettings::deliveryFee()`. A bakery with no tiers configured gets a free-delivery quick order. `CreateQuickOrderData` takes the payment method and delivery type as enums (the form's selects hand back enum instances), and the order stores the chosen delivery type. `QuickOrder::createOrder()` reports any exception before showing the generic error notification. Every quick order needs a customer email, like storefront orders, because `orders.customer_id` is required and customers are keyed by email; an existing customer with that email is reused.

The storefront order form (`order-form-script.blade.php`) submits with `fetch` and `Accept: application/json`, so a `StoreOrderRequest` failure comes back as a 422 `{message, errors}` body. The script keeps the first error per field in `fieldErrors`, shows it under the matching input (`x-storefront.order-field-error`, with `items.*` errors shown in the cart summary), clears it when that field changes, and puts the response `message` by the submit button. `SubmitOrderController` throws the order pipeline's domain failures (minimum order, stock and no-longer-available items under `items`, a full date under `delivery_date`, a taken pickup slot under `delivery_time`) as `ValidationException`s, so the fetch form gets the same 422 body and a plain form post redirects back with the errors and old input. Other failures keep the generic message.

### Order access and tracking

Order-by-number routes (`order.access` middleware, `EnsureOrderAccess`) require `OrderAccessGuard::canAccess()`: a logged-in customer with a verified email who owns the order, or an order number the session has been granted. A session is granted an order by placing it, returning from Stripe, passing `VerifyOrderAccessController` (order number plus matching email), or opening a tracking link.

The tracking form (`POST /track`) only emails a link and never shows orders. `TrackingController::store` always redirects back with the same "check your email" message (page content key `link_sent_message`), whether or not the address has orders. When it does, it queues `OrderTrackingLinkMail` to that address, at most one per customer every five minutes (`RateLimiter`). The mail holds a 30-minute `URL::temporarySignedRoute` to `GET /track/access/{customer}` (`order.track.access`, `signed` middleware). `ShowTrackedOrdersController` renders the order list for that customer and grants the session access to each order.

Customer accounts: `RegisterCustomer` creates a customer, or claims an existing guest customer row with the same email (no password yet). A claim sets the password and clears `email_verified_at` but keeps the row's existing name and phone (blanks are filled from the form), and the customer is logged in straight away. Until the email is verified, the `customer.verified` middleware (`EnsureCustomerEmailIsVerified`) redirects `account`, `account/orders` and `account/profile` (GET and POST) to `account.email.verify.notice`, and returns 403 JSON on the favorites API (`api.favorites.index` and `api.favorites.toggle`). The verify notice, verify link, resend and logout stay reachable. The same rule applies to `OrderAccessGuard::canAccess()`: the customer-owns-order shortcut needs `hasVerifiedEmail()`. Session grants (just-placed order, Stripe return, order verify page, tracking link) are unaffected.

Customer emails are matched case-insensitively. SQLite compares text case-sensitively, so `Bob@x.com` and `bob@x.com` would otherwise be two customers. `App\Support\EmailAddress::normalize()` (lowercase and trim) is the single definition. On write, `App\Casts\EmailAddressCast` stores `customers.email` and the other customer email columns lowercased: `carts`, `customer_favorites`, `waitlist_entries`, `product_waitlists`, `customer_campaign_logs`, `customer_photos`, `reviews`, `survey_responses` and `catering_inquiries` (`customer_email`). On read, the builder methods (`CustomerQueryBuilder::forEmail()`, `OrderQueryBuilder::placedByEmail()` and `forCustomerEmail()`, `CustomerFavoriteQueryBuilder::forCustomer()`) normalize their input, so callers pass the email as typed. Eloquent casts do not apply to `where` values, so the call sites that use `updateOrCreate`, `firstOrNew` or `CartManager::updateContact` (`ResolveCustomer`, `RegisterCustomer`, `JoinProductWaitlist`) normalize explicitly, and the storefront login, forgot-password and reset-password requests normalize `email` in `prepareForValidation()` before the auth guard and password broker look the customer up. The admin customer form validates uniqueness on the normalized email. The `2026_10_01_140000_normalize_customer_email_case` tenant migration lowercases existing rows. It leaves rows that would collide untouched and logs a warning with the tenant id and the row ids and emails: customers whose emails differ only by case (`customers.email` is unique), and favorites of the same product. Those rows have to be merged by hand.

### Customer order edits

While the modification window is open, customers can change quantities and the tip on a pending, unpaid order (`ModifyOrder`). An order with a Stripe checkout session (`stripe_checkout_session_id`) cannot be edited until the payment settles, because the open session would still charge the old amount (`OrderModificationGuard::hasOpenCheckout()`). The order is re-priced inside the same transaction. `orders.original_subtotal` and `original_discount_amount` hold the values at placement (filled by `PersistOrder`, or on first edit for older orders). The aggregate discount is rescaled from them by `ModifiedOrderPricing`, never above the original, so undoing an edit restores it exactly. The coupon `Usage` transaction is scaled by the same proportion. The gift card draw is capped at what is left to pay and never grows; any unused part is credited back to the card and subtracted from the order's `Redemption` transaction, so a later cancellation (`ReverseOrderDiscounts`) refunds exactly the reduced amount (`AdjustOrderDiscountLedgers`). Delivery is not re-priced because the tier is not stored; an edit that lowers the subtotal below the pickup or delivery minimum, or below the free-delivery minimum for an order that qualified for free delivery, is rejected with `OrderNotModifiableException`.

### Deleting orders

Orders are hard-deleted (no soft deletes), and deleting cascades to the order's refunds and messages, so deletion is restricted to orders nothing financial happened on. `OrderDeletionGuard::canDelete()` allows it only when the status allows deletion (`OrderStatus::allowsDeletion()`: Pending or Cancelled), the payment status allows it (`PaymentStatus::allowsDeletion()`: Unpaid or Cancelled), and the order has no refunds. `OrderPolicy::delete()` additionally requires the Manager role or above; staff cancel instead. Every other order is cancelled, which keeps the record. The Orders table's bulk delete skips orders the policy denies and the notification says how many were not deleted. `OrderObserver::deleting()` runs `ReverseOrderDiscounts` before the row goes, so the coupon use and gift card draw are given back the same as on cancellation (idempotent, so an order that was already cancelled is not reversed twice).

### Ingredient units and stock

An ingredient has a stock unit (`ingredients.unit`), and each linked recipe line (`recipe_ingredients.unit`) has its own. Both come from the `MeasurementUnit` enum, which knows each unit's dimension (`UnitDimension`: mass, volume or count) and its factor to the dimension's base unit (grams, millilitres, each), so `MeasurementUnit::convert()` converts within a dimension and returns null across dimensions. The columns stay plain strings so a legacy value that isn't a known unit still displays; `Ingredient::measurement_unit` resolves it to the enum or null.

Everything that compares or moves stock goes through `RecipeLineConverter::inStockUnit()`, which expresses a recipe line's quantity in the ingredient's stock unit at full precision: the stock check (`IngredientDemandCalculator`, used by `ValidateStockAvailability` and `CheckOrderStockAvailability`), the Baking deduction and cancellation restock (`AdjustOrderIngredients`), and the upcoming-order needs in `ShoppingListService`. Stock and recipe quantities (`ingredients.current_stock` and `low_stock_threshold`, `recipe_ingredients.quantity`, `stock_adjustments.quantity`) are `decimal(12,4)`, so a 1 g draw from stock held in kg (0.001) isn't lost. The stock check compares at 4 decimals and `AdjustOrderIngredients` rounds the converted amount to 4 decimals at write. The UI shows stock to 2 decimals unless that would show a small amount as 0 (`StockQuantity::display()`), and editable fields keep every stored digit (`StockQuantity::input()`). A line that can't be converted (different dimensions, or an unknown unit) is skipped with a logged warning rather than blocking the order. A recipe's linked lines live in the `recipe_ingredients` table, which is read two ways: `Recipe::inventoryIngredients()` (a BelongsToMany with the quantity and unit as pivot columns) feeds deduction, demand and labels, and `Recipe::ingredientLines()` (a HasMany over the `RecipeIngredient` model) is what the recipe form's relationship repeater saves, since Filament's repeater can only create and update rows of a HasMany. The product label (`ProductLabelPresenter`) lists a recipe's linked ingredients by weight, heaviest first: it converts each mass-unit line to grams with `MeasurementUnit`, keeps equal weights in entry order, and puts lines in volume, count or unknown units after the weighed ones in entry order, since those can't be weighed without a density. `hasUnweighedIngredients()` drives a screen-only note on the label page that the order is approximate; the printed label carries no warning. The Label Generator tool page builds its ingredients, "Contains:" line and the same screen-only note from `ProductLabelPresenter` too (eager-loading `recipe.inventoryIngredients`), truncating the ordered list rather than the recipe's free-text JSON. The form rejects linking the same ingredient twice on one recipe. `RecipeForm` limits a linked line's units to the selected ingredient's dimension and shows a warning hint on a saved line that no longer fits.

### Product waitlist

The storefront menu offers a waitlist for inactive products only; `StoreProductWaitlistRequest` rejects joining for an active product ("This item is available now."). When a product's `is_active` changes from false to true, `ProductObserver` dispatches `ProductReactivated` after the transaction commits. The queued `NotifyProductWaitlistListener` runs inside the dispatching tenant and calls `NotifyProductWaitlist`, which queues one `ProductAvailableMail` per un-notified entry and stamps `notified_at`. It sends nothing for an inactive product, and nothing (leaving entries waiting) when the tenant's `product_available` email toggle is off. The admin "Notify Waitlist" row action is a manual resend for active products.

### Date capacity

`CapacityCalculator` decides how many active orders a delivery date can take. Both it and the storefront availability calendar (`AvailabilityService`) read the rules through `DateCapacityRules`, which loads everything for a date range in four queries. A date is unavailable when it is closed (an all-day `BlockedDate`, a closed day in Schedule Manager, or an active holiday whose `order_deadline` has passed; the deadline day itself stays open), or when its orders reach the max. Staff-created orders (quick orders, catering conversions) skip this check. The max comes from the first level that sets one, most specific first:

1. Capacity limit for that exact date
2. Active holiday on that date
3. Capacity limit for that weekday
4. Schedule Manager max orders for that weekday
5. Tenant default (`default_daily_capacity`)

A blocked capacity limit sets the max to 0. A blank or 0 max means "no limit here" and falls through to the next level.

### Earliest delivery date

`EarliestDeliveryDate` decides the first delivery date a customer can choose: the bakery's local today (the `timezone` order setting, UTC until set) plus the lead-time days (`minimum_order_lead_hours`, rounded up to whole days). If today's Schedule Manager order cutoff has passed, the order counts as placed tomorrow, so the date moves back one day. The storefront and API order requests validate against it, and the order page shows it and uses it as the date picker's minimum. Staff-created orders don't use it.

"Today" for all of these rules is the bakery's local date from `BakeryClock` (the same `timezone` setting). `BakeryClock::today()` returns that date as a plain date value, so it compares directly with date columns. `BakeryClock` resolves the current tenant's settings on every call, so a clock injected before a scheduled command enters a tenant (the birthday and repeat-order engagements) still follows each tenant's timezone; `TenantSettings` is bound per resolve (cached in `TenantSettingsRegistry`, which `TenancyManager` flushes on every tenant switch) rather than scoped, so it can never keep a previous tenant's values. The holiday deadline check, the availability calendar, the capacity dashboard widget, upcoming holidays and the holiday deadline/day counts in admin all use it. Customer engagement uses it too: the birthday email and `BirthdayCalculator` match birthdays against the bakery-local date, and repeat-order reminders measure their cutoff, days since the last order and next reminder date from it. Timestamps such as `reminder_sent_at` stay UTC. The orders dashboard widgets (today's orders, upcoming orders, baking sheet, revenue chart) and the stats overview query, including its week boundaries, use it as well. Timestamp series bucket by the bakery-local day too: the stats overview's pending orders and storefront views and the storefront analytics daily trend use `DateCountQuery::countByLocalDay()`, which turns each local day into a `[start, end)` range of UTC instants and counts them in one query (SQLite and MySQL alike), while the original `DateCountQuery::count()` still groups by UTC date for the central platform stats. The birthday widget's cache key includes the bakery-local date, so `days_until` never survives local midnight. The scheduled customer and baker sends (birthday emails, repeat-order reminders, the weekly digest and low-stock alerts) also run on the bakery clock: the scheduler fires them hourly and `LocalSendWindow` lets each tenant through only at its local send hour, once per local date (see `docs/operations.md`).

The storefront analytics page and the storefront views widget turn bakery-local day and week boundaries into instants in the app timezone before comparing them with `created_at`. Seasonal item availability (the `current`, `upcoming` and `expired` builder methods and `is_currently_available`), the birthday widget, and the Quick Order date picker's minimum date also use the bakery's local today.

Admin page and form date defaults use the bakery-local date from `BakeryClock::today()` as well: the baking sheet, delivery route planner, weekly prep planner, order calendar and social calendar (including their today markers), the shopping list pages, reorder reminders, the catering event date minimum, the customer birthday maximum and the expense and income date defaults. The reports center's date range and year, the finance summary and tax export year, the product trends month, the label generator's best-by date and the holiday planning calendar's year follow it too. The goal tracker's month and year and the customer insights widget's week and month turn their bakery-local boundaries into app-timezone instants (`DateRange::inAppTimezone()`) before querying timestamp columns. Every `DateRange` factory (`fromStrings`, `thisWeek`, `thisMonth`, `thisYear`, `lastDays`, `forMonth`) builds its boundaries in the bakery timezone from `BakeryClock`, so a range reads as the bakery's calendar days when compared with a date column such as `delivery_date` (the sales and product reports, the top products and weekly revenue widgets). Queries on timestamp columns call `inAppTimezone()` first: the customer report's new customers and acquisition by month (the months themselves are the bakery-local month of `created_at`, not the UTC month), and the product trends service's `orders.created_at`. The weekly prep planner loads a week's orders with `whereDate` comparisons, so the last day's orders are kept on SQLite as well as MySQL. The reorder reminders page builds its "email customer" link in `ReorderReminders::reminderMailto()` from `TenantSettings->store->name`, and an arch test (`tests/Arch/TenantSettingsPropertiesTest.php`) checks that views only read properties that exist on `TenantSettings`.

All money columns are integer cents. Eloquent models use the project's money cast/value object; raw aggregates and direct database operations bypass casts and must explicitly preserve cents.

### Payment paths

The application has two distinct Stripe concerns:

- **SaaS billing:** Laravel Cashier handles bakery subscriptions through central `/billing` routes and `/stripe/webhook`.
- **Storefront payments:** Stripe Connect charges bakery customers through the connected bakery account. The tenant's Connect account and payment toggle come from tenant settings.

For a positive-total order with Stripe enabled, `StripeCheckoutService` creates a connected-account Checkout Session, stores its session identifier, and redirects off-site. The success callback retrieves the session from Stripe and accepts only a paid session; `HandleCheckoutComplete` stores the payment intent and delegates the idempotent paid transition to `MarkOrderPaid`, but only when the session's `amount_total` equals the order total. A different amount leaves the order unpaid, stores the payment intent, logs a warning and sends the bakery owners a database notification (`ReportStripeAmountMismatch`); the success page then shows a "needs review" notice instead of "Payment successful". The webhook path goes through the same `HandleCheckoutComplete` action. The Connect webhook verifies its Stripe signature, records webhook delivery/idempotency, initializes the referenced tenant, and handles supported account or checkout events. A cancelled checkout returns the order to unpaid status.

Orders can also use enabled non-Stripe methods. PayPal support creates and sends invoices and the production scheduler checks invoice payment status hourly (`paypal:check-payments`). The command resolves everything that depends on tenant settings (the settings manager, the PayPal token manager and client, `MarkOrderPaid`) inside each tenant's `forEachTenant` callback, so every bakery is polled with its own credentials and nothing reads tenant tables on the central connection. `tests/Feature/Console/ScheduledTenantCommandsTest.php` guards every scheduled tenant command against that mistake. Manual/cash flows proceed directly to confirmation. Refund behavior is payment-specific; Stripe refunds require a paid order and a captured payment-intent identifier, then record the refund transaction and transition payment status.

In the bakery-admin Filament panel, the order form shows `status` read-only and `payment_status` read-only on edit, so saving the form never changes either. Status changes go through the table and view-page actions that call `TransitionOrderStatus`, and payments received outside checkout are recorded with the Mark Paid actions that call `MarkOrderPaid`. Mark Paid is offered while `PaymentStatus::canBeMarkedPaid()` is true (Unpaid or Partial, so a catering order with a deposit can be settled) and the order isn't Cancelled. Send PayPal Invoice stays Unpaid-only because the invoice bills the full order total. An order created from the admin form starts Pending.

## Settings architecture

Settings are database-backed key/value records with separate tenant and platform managers:

- `SettingsManager` reads tenant `Setting` records in the current tenant database.
- `PlatformSettingsManager` reads central platform settings.
- `AbstractSettingsManager` memoizes primitive values in memory for the current manager instance and provides transactional bulk writes.
- `TenantSettingCipher` transparently encrypts PayPal credentials and webhook signing secrets before persistence; tenant migrations encrypt legacy plaintext values without changing settings consumers.
- `TenantSettings` is a read-only composite DTO of typed settings groups such as store, branding, orders, payments, catering, loyalty, policies, homepage, webhooks, gift cards, and inventory.
- `TenantSettingsDefaults` supplies defaults used when provisioning or resolving unset values.

Controllers should receive the typed `TenantSettings` DTO when rendering storefront data. Write paths use the relevant action plus `SettingsManager`. Tenancy transitions flush the manager cache, preventing values from one bakery surviving into another tenant context.

Central onboarding screens read denormalized product, category, and order counts from the tenant record instead of opening every tenant database during a web request. `tenants:sync-onboarding-metrics` reconciles those counts every fifteen minutes and should be run once immediately after deploying its central migration.

The onboarding subdomain doubles as the tenant id and the bare domain row, so `StoreOnboardingRequest` lowercases and trims it before validating, requires a valid hostname label, rejects `config('kneadit.reserved_subdomains')`, and checks uniqueness against both `domains.domain` and `tenants.id`.

Tenant onboarding is coordinated by `CompleteTenantOnboarding`. `CreateTenantRecord` owns the central tenant/domain transaction and links the tenant to its owner (`tenants.user_id`, which `User::tenants()` and the free-forever checks read), `ProvisionTenantOwner` seeds the tenant owner and settings inside tenant context, and `CreateTenant` provides compensating cleanup if provisioning fails. The orchestrator then completes any referral and emits `TenantOnboarded`; the HTTP controller retains only session logout/rotation and redirect concerns.

## Email marketing and unsubscribe

Customers can opt out of marketing email. `customers.marketing_opted_out_at` (null = subscribed) is set by the customer from the unsubscribe link in any marketing email, or by staff through the Customers table's "Mark unsubscribed" action (for opt-outs received by phone or email). Staff cannot re-subscribe a customer; only the customer can, from the same link.

**Marketing mails** (a message the customer did not specifically ask for) implement `MarketingMail` and use `SendsMarketingMail`:

- `CustomerCampaignMail` (customer campaigns) and `BulkCustomerMessageMail` (the Customers "Send message" bulk action when its purpose is Promotion).
- The automated engagements: `HappyBirthdayMail`, `RepeatOrderReminderMail`, `ReviewRequestMail`, and `AbandonedCartRecoveryMail`.

**Transactional mails** are never suppressed and carry no unsubscribe link: order placed, status, modified and messages, `OrderUpdateMessageMail` (the "Send message" bulk action when its purpose is Order update), order tracking links, catering quotes, product-available alerts (the customer asked to be told), referral rewards (earned) and contact-message replies. Staff, supplier and platform notifications are not customer marketing either. For a new customer-facing mail, decide by asking whether the customer requested that specific message.

Every marketing mail sends `List-Unsubscribe` and `List-Unsubscribe-Post: List-Unsubscribe=One-Click` (RFC 8058) and shows an "Unsubscribe" link in the shared email layout. `BaseMailable` passes `unsubscribeUrl` to the view for any `MarketingMail`. `MarketingUnsubscribeLinks` builds the link: a non-expiring URL signed against its path only (`signed:relative`) and prefixed with the bakery's storefront host, so it works from queued or scheduled sends where the request host is not the bakery's.

The Customers "Send message" bulk action makes the baker choose a required `BulkMessagePurpose` (no default), so the opt-out cannot be bypassed by accident. `SendBulkCustomerMessage` takes the enum and returns a `BulkMessageOutcome` (sent, skipped for no email, skipped as ineligible) that the notification summarises, for example "3 sent; 2 skipped (no open order)". **Order update** is transactional: it goes to selected customers with an open order (`CustomerQueryBuilder::withOpenOrder()`: pending, confirmed, baking or ready), including those who unsubscribed, and sends `OrderUpdateMessageMail` with no unsubscribe headers or footer link. **Promotion** keeps the marketing behavior: `BulkCustomerMessageMail`, opted-out customers skipped.

`EmailUnsubscribesController` serves the link (routes in `routes/tenant/access.php`, outside the storefront-enabled check): `GET` shows a confirmation page, `POST` unsubscribes immediately and is CSRF-exempt because mail providers post without a token (the signature is the protection), and `DELETE` re-subscribes from the confirmation page.

Senders skip opted-out customers in the query (`CustomerQueryBuilder::subscribedToMarketing()`), so recorded recipient counts exclude them: `ResolveCampaignRecipients`, `SendBulkCustomerMessage` (promotions only), the three engagement recipient finders and `SendAbandonedCartRecoveryCommand`. Platform email campaigns go to bakery owners, not customers, so they are not customer marketing. The birthday, repeat-order and review-request listeners re-check just before sending. Abandoned-cart recovery only mails carts whose email matches an existing customer, because the opt-out lives on the customer record.

## Frontend

Blade, Livewire, Alpine.js, Filament, and Tailwind CSS make up the UI. Vite builds separate central/application, storefront, tenant Filament, and central Filament entry points defined in `vite.config.js`. Inline scripts and styles use the request-scoped CSP nonce directive.

### View organization

Blade views are organized by the application surface they serve:

- `resources/views/central/` contains central application views, grouped into `auth`, `billing`, `blog`, `legal`, `marketing`, `platform`, and `seo`.
- `resources/views/tenant/` contains tenant-facing views. `storefront/` holds public bakery pages and customer account flows, while `admin/` and `invitations/` hold tenant administration and staff invitation views.
- `resources/views/components/` contains reusable Blade components. Storefront home components live under `storefront/home`, and tenant administration components live under `tenant-admin`; their Blade tags and PHP component namespaces mirror those paths.
- `resources/views/shared/` contains cross-page includes that are not tied to a single surface, such as analytics and storefront order-form scripts.
- `resources/views/filament/`, `emails/`, `errors/`, `vendor/`, and `reference/` remain specialized top-level trees for their respective rendering contexts.

When adding a view, choose its location from the request surface first, then its feature. A view used by tenant models belongs under `tenant/` even if the controller is currently grouped in a central namespace. Component PHP classes and component integration tests should mirror the component's `resources/views/components/` path.

## Cross-cutting constraints

- Authorization belongs in policies, gates, and route middleware. Protected Filament actions require server-side authorization.
- Tenant-bound work must be executed only after tenancy initialization; queued work relies on the tenancy queue bootstrapper.
- Stable domain states use backed enums and model casts.
- Writes are transactional where multiple records or counters must remain consistent.
- Cache values must be scalars or primitive arrays; Eloquent objects are intentionally rejected by the configured serialization guard.
- Order URLs bind `{order:order_number}` rather than database IDs.
