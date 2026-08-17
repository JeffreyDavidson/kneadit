# KneadIt architecture

## System shape

KneadIt is one Laravel application serving two related surfaces:

1. The **central application** serves the marketing site, bakery registration and onboarding, subscription billing, the public bakery directory, and the platform-admin Filament panel.
2. A **tenant application** serves a bakery's storefront, tenant API, customer account flows, and bakery-admin Filament panel.

Both surfaces share application code. The request host and tenancy middleware decide which database context is active.

## Data and tenancy boundaries

The central connection is named `central`. It contains the platform tenant and domain records plus central concerns such as platform settings and subscription state. Every bakery has a separate SQLite database. `config/tenancy.php` uses `TenantSQLiteDatabaseManager`, with files rooted at `TENANT_DB_PATH` or `database_path()` when the variable is unset. `TenantDatabasePath` rejects path separators/traversal, and the manager refuses symlinks before connecting.

Tenancy bootstraps four Laravel facilities:

- The database connection switches to the tenant database.
- cache operations receive a tenant-specific tag.
- the private CSV import disk receives a tenant-specific root.
- queued work carries tenant context through `QueueTenancyBootstrapper`.

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

Central domains are configured in `config/tenancy.php`; production includes `getkneadit.app` and `www.getkneadit.app`, while local development includes `kneadit.test`. Tenant routes additionally apply `InitializeTenancyByDomainOrSubdomain` and reject access from central domains. This supports both bakery subdomains and domain records representing custom domains.

An unknown tenant domain returns 404. If a central tenant record exists but its SQLite file does not, local development recreates and migrates it automatically. Production returns 503 and instructs the operator to run `php artisan tenants:doctor --fix`.

The root URL is deliberately universal: the global middleware establishes central or tenant context once, then `RootController` serves the platform landing page or bakery storefront without re-running tenancy middleware.

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

## Major domains

- **Platform and tenancy:** bakery registration, onboarding, domains, plans, trials, subscriptions, referrals, support, announcements, audits, impersonation, backups, and health.
- **Storefront and content:** bakery home pages, menus, blogs, galleries, catering, gift cards, reviews, policies, branding, and PWA metadata.
- **Orders:** carts, checkout, capacity and stock validation, discounts, fulfillment, order messaging, tracking, invoices, payment state, and refunds.
- **Inventory and production:** products, categories, ingredients, recipes, suppliers, stock adjustments, waitlists, seasonal items, and production planning.
- **Customers and engagement:** customer profiles, favorites, notes, referrals, loyalty, campaigns, surveys, reviews, reminders, contact messages, and catering inquiries.
- **Financial:** income, expenses, coupons, gift cards, refunds, reporting, tax export, Stripe, and PayPal.
- **Operations and staff:** schedules, blocked dates, holidays, capacity, check-ins, staff invitations and roles, activity logs, and webhook delivery.
- **Analytics:** page and product-impression records use a keyed, pseudonymous visitor identifier. Raw network/device identifiers are not persisted, recording failures are reported without breaking storefront responses, and scheduled tenant-wide retention bounds stored history.
- **Storage:** intentionally public assets remain on the established `public` disk. Sensitive CSV imports use a dedicated private disk that is neither directly served nor shared across tenant roots; all configured disks fail loudly on storage errors.

## Order lifecycle

Storefront requests are validated by `StoreOrderRequest` and converted to `CreateOrderData`. `CreateOrder` then executes a database transaction containing an ordered Laravel pipeline:

1. Calculate totals and enforce the minimum order amount.
2. Validate date capacity and stock availability.
3. Apply sitewide sales, coupons, gift cards, referrals, and tier perks.
4. Resolve the customer and persist the order.
5. Record coupon/gift-card/referral effects and persist line items.
6. Mark the cart converted.

If capacity rejects the request, the pipeline returns no order. Other domain validation failures return targeted form errors. A successful transaction logs the placement and emits `OrderCreated`. The current session is granted access to the resulting order before redirecting to payment or confirmation.

All money columns are integer cents. Eloquent models use the project's money cast/value object; raw aggregates and direct database operations bypass casts and must explicitly preserve cents.

### Payment paths

The application has two distinct Stripe concerns:

- **SaaS billing:** Laravel Cashier handles bakery subscriptions through central `/billing` routes and `/stripe/webhook`.
- **Storefront payments:** Stripe Connect charges bakery customers through the connected bakery account. The tenant's Connect account and payment toggle come from tenant settings.

For a positive-total order with Stripe enabled, `StripeCheckoutService` creates a connected-account Checkout Session, stores its session identifier, and redirects off-site. The success callback retrieves the session from Stripe and accepts only a paid session; `HandleCheckoutComplete` stores the payment intent and delegates the idempotent paid transition to `MarkOrderPaid`. The Connect webhook verifies its Stripe signature, records webhook delivery/idempotency, initializes the referenced tenant, and handles supported account or checkout events. A cancelled checkout returns the order to unpaid status.

Orders can also use enabled non-Stripe methods. PayPal support creates and sends invoices and the production scheduler checks invoice payment status hourly. Manual/cash flows proceed directly to confirmation. Refund behavior is payment-specific; Stripe refunds require a paid order and a captured payment-intent identifier, then record the refund transaction and transition payment status.

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

Tenant onboarding is coordinated by `CompleteTenantOnboarding`. `CreateTenantRecord` owns the central tenant/domain transaction, `ProvisionTenantOwner` seeds the tenant owner and settings inside tenant context, and `CreateTenant` provides compensating cleanup if provisioning fails. The orchestrator then completes any referral and emits `TenantOnboarded`; the HTTP controller retains only session logout/rotation and redirect concerns.

## Frontend

Blade, Livewire, Alpine.js, Filament, and Tailwind CSS make up the UI. Vite builds separate central/application, storefront, tenant Filament, and central Filament entry points defined in `vite.config.js`. Inline scripts and styles use the request-scoped CSP nonce directive.

## Cross-cutting constraints

- Authorization belongs in policies, gates, and route middleware. Protected Filament actions require server-side authorization.
- Tenant-bound work must be executed only after tenancy initialization; queued work relies on the tenancy queue bootstrapper.
- Stable domain states use backed enums and model casts.
- Writes are transactional where multiple records or counters must remain consistent.
- Cache values must be scalars or primitive arrays; Eloquent objects are intentionally rejected by the configured serialization guard.
- Order URLs bind `{order:order_number}` rather than database IDs.
