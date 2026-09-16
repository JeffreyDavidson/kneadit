# KneadIt Testing Audit & Style Guide

> Archived audit snapshot from May 13, 2026. The durable testing conventions
> are maintained in the project skills and current CI/workflow configuration;
> recheck inventory and recommendations before relying on this audit.

Date: 2026-05-13 15:24 EDT  
Branch audited: `develop` (`538b6587` locally)  
Scope: local repository inspection, test inventory, PHPUnit/Pest config, GitHub Actions workflows. No test suite execution was possible locally because `php` is not available in this OpenClaw runtime.

## Executive summary

KneadIt has a broad and unusually mature Pest suite for a pre-launch SaaS: 806 `*Test.php` files and roughly 3,361 `test()` / `it()` declarations across Unit, Integration, Feature, Arch, and Browser suites. The strongest coverage is around tenant/business actions, HTTP controllers, Filament resources/pages, models, services, request validation, commands, and architecture rules.

Launch readiness risk is not the absence of tests; it is test boundary drift and a few high-risk flows that are covered but sometimes by shallow/source-assertion tests instead of behavior-driven tests. Before launch, prioritize hardening checkout/billing/webhook/onboarding/multi-tenancy tests and making the suite taxonomy explicit so new tests land at the correct level.

## Current inventory

### Test suites configured in `phpunit.xml`

| Suite | Directory | Files found | Notes |
| --- | ---: | ---: | --- |
| Unit | `tests/Unit` | 94 | Low-level tests exist, but some middleware/listener/service tests use Laravel container, routing, DB, or source inspection and are not true unit tests. |
| Integration | `tests/Integration` | 424 | Largest suite. Good fit for actions, services, models, observers, listeners, queries, request validation, mail rendering, and Filament page internals. Some tests are high-level workflows and may belong in Feature. |
| Feature | `tests/Feature` | 230 | Strong HTTP/controller, command, Filament resource/page, route integrity, mail, and middleware coverage. Several tests assert source text rather than behavior. |
| Arch | `tests/Arch` | 12 | Good guardrail layer for structure, naming, and best practices. |
| Browser | `tests/Browser` | 46 | Useful Playwright/Pest browser smoke coverage for central pages, storefront pages/forms, and admin pages. Excluded from CI tests workflow. |

Additional quality gates in CI: PHPStan, Pest type coverage at 100%, Pint, TLint, FilaCheck, Ward security scan, and Rector dry-run on PRs with `continue-on-error: true`.

## Required test type boundaries

Jeffrey’s testing standard should be treated as the rule for all new and moved tests:

### Unit tests

Unit tests are low-level tests of isolated logic.

Use Unit tests for:
- pure value objects, DTOs, enum helpers, casts, presenters, small calculators, and deterministic helper methods;
- branch-heavy logic that can run without Laravel HTTP, Livewire, database migrations, queues, mailers, filesystem, or external services;
- mocks/stubs only at the immediate dependency boundary.

Avoid in Unit tests:
- `get()`, `post()`, `route()`, `artisan()`, `Livewire::test()`, real database writes, mail/queue/event assertions through Laravel fakes, tenancy bootstrapping, or source-code string assertions.

### Integration tests

Integration tests prove how classes/modules work together.

Use Integration tests for:
- actions coordinating models/events/mail/transactions;
- services using repositories, Eloquent, settings managers, tenancy helpers, or external API wrappers with mocked clients;
- observers, listeners, policies, request validators, query builders, mail rendering, model scopes, and Filament page/component internals;
- database-backed module behavior without asserting the full user-facing HTTP workflow.

Avoid in Integration tests:
- full browser-style journeys, public route behavior from a user perspective, and end-to-end registration/checkout/onboarding workflows. Those belong in Feature or Browser.

### Feature tests

Feature tests are high-level behavior/workflow tests.

Use Feature tests for:
- user-visible HTTP behavior: route access, validation, redirects, session state, flash messages, auth, middleware, and rendered page outcomes;
- business workflows spanning multiple modules, such as signup → tenant creation, order submission → payment redirect, webhook → subscription state update, invitation acceptance, plan swap, and storefront ordering;
- console command behavior from the operator’s perspective;
- Livewire/Filament resource/page behavior when tested as a user-visible page/resource.

Avoid in Feature tests:
- source-string assertions as substitutes for behavior, isolated DTO/value assertions, and tests that merely check that a class contains a method or import.

### Arch tests

Arch tests are structural guardrails, not behavior tests.

Use Arch tests for:
- namespace layering, naming conventions, forbidden dependencies, Filament 5 namespace rules, model/factory conventions, and project-wide static constraints.

### Browser tests

Browser tests are thin user-path smoke checks.

Use Browser tests for:
- confidence that critical pages/forms actually render and basic interactions work in a real browser.

Keep Browser tests sparse and focused on launch-critical journeys because they are slower and currently excluded from CI’s main Pest job.

## Current suite organization problems against these boundaries

### 1. Unit suite contains higher-level Laravel behavior

Observed examples flagged by static inspection:
- `tests/Unit/Http/Middleware/InitializeTenancyIfNeededTest.php`
- `tests/Unit/Http/Middleware/SecurityHeadersTest.php`
- `tests/Unit/Listeners/Platform/NotifyPlatformOfNewTenantListenerTest.php`
- `tests/Unit/Listeners/Platform/SendWelcomeBakerEmailListenerTest.php`
- `tests/Unit/Services/Settings/TenantSettingsTest.php`
- `tests/Unit/Services/WebhookServiceTest.php`
- `tests/Unit/Casts/MoneyCentsCastTest.php` and `PercentageCastTest.php` may be fine as unit tests if they remain isolated, but static inspection saw framework-level signals and they should be reviewed.

Recommendation: move middleware/listener/service tests that use Laravel fakes, routing, DB, or container resolution into Integration. Keep only pure input/output logic in Unit.

### 2. Integration suite contains behavior/workflow signals

Static inspection found many Integration files using route/HTTP/Livewire/artisan-style signals. Some are legitimate module integration tests, but the following categories need review:
- Filament page/widget tests under `tests/Integration/Filament/...` that render Livewire/UI behavior may belong in Feature if asserting user-visible behavior.
- Middleware tests under `tests/Integration/Http/Middleware/...` are acceptable Integration if they exercise middleware in isolation, but route-level auth/access outcomes should be Feature.
- Query builder/model scope tests are fine as Integration even though they hit DB-backed Eloquent.

Recommendation: keep module-level DB collaboration in Integration; promote user-facing route/page workflows to Feature.

### 3. Feature and Integration suites use source-code string assertions

Source assertions are useful as temporary regression tripwires but weak as launch confidence. Examples:
- `tests/Feature/Http/Controllers/Stripe/StripeConnectWebhookControllerTest.php`
- `tests/Feature/Http/Controllers/Stripe/StripeWebhookControllerTest.php`
- `tests/Feature/Console/Commands/TenantAwareCommandsTest.php`
- `tests/Feature/Console/Commands/CheckPayPalPaymentsTest.php`
- `tests/Feature/Console/Commands/SendScheduledCampaignsTest.php`
- `tests/Integration/Actions/Stripe/ReauthenticateFromCheckoutSessionTest.php`
- `tests/Integration/Services/Stripe/StripeWebhookHandlerTest.php`
- several Unit tests for actions/listeners/mail/services.

Recommendation: either move pure structural assertions to Arch tests, or replace them with behavior tests that execute the route/action/command and assert state changes, events, mail/queue behavior, cache idempotency, and responses.

### 4. Browser suite is excluded from CI

`.github/workflows/tests.yml` runs `php artisan test --compact --exclude-testsuite=Browser`. That is reasonable for speed. The separate `.github/workflows/browser-smoke.yml` provides the curated launch-smoke workflow.

Recommendation: keep `.github/workflows/browser-smoke.yml` healthy and expand its curated launch-smoke subset only when a launch-critical journey earns coverage.

## Coverage strengths

- **HTTP/controller coverage is broad:** 109 Feature HTTP test files cover auth, billing, Stripe webhooks, storefront, order, API, central, marketing, and middleware paths.
- **Action layer is well-covered:** 92 Integration action files cover orders, Stripe, tenants, staff, content, loyalty, gift cards, inventory, customers, marketing, platform, products, and operations.
- **Filament coverage is strong:** 93 Feature Filament files plus 48 Integration Filament files cover pages, resources, widgets, central admin pages, schemas, and onboarding.
- **Architecture guardrails exist:** 12 Arch tests plus PHPStan/type coverage/FilaCheck help catch Laravel/Filament drift.
- **No skipped/todo/only tests found** in `*Test.php` files by static scan.
- **Test harness has thoughtful tenancy cleanup:** `tests/Pest.php` cleans tenant SQLite files while preserving browser fixture tenants.

## Launch-critical gaps / risks

### Priority 0 — must be hardened before launch

1. **Stripe billing and webhook confidence**
   - Current coverage exists for checkout, Stripe Connect, Cashier webhook overrides, and Stripe actions.
   - Risk: several Stripe tests assert controller source strings instead of exercising full event payload behavior.
   - Add/strengthen Feature tests for: valid signed webhook → idempotency cache → action called/state changed; duplicate event ignored; invalid signature rejected; subscription updated/deleted maps tenant/customer correctly; connect account updated toggles settings inside tenant context.

2. **Signup/onboarding/multi-tenancy happy path**
   - Coverage exists for register, signup pipeline, onboarding request/page internals, tenant creation commands, central/tenant setup.
   - Risk: launch depends on the entire chain, not just modules.
   - Add one high-level Feature workflow: visitor registers → tenant created with domain → welcome/new subscriber notifications queued → checkout/onboarding redirect state correct → central and tenant DB records present.
   - Add one Browser smoke path if feasible: `/register` render/validation and post-signup onboarding first screen.

3. **Order checkout flow with payments**
   - Coverage exists for order submission, Stripe redirect, order actions, stock, capacity, coupons, gift cards, tracking, modification, and confirmation.
   - Risk: mocks can prove controller branching without proving the assembled workflow.
   - Add a Feature workflow for realistic storefront cart/order payload using actual `CreateOrder` and mocked only Stripe client/session boundary. Assert order/items/customer/payment status/session redirect.

4. **Tenant isolation boundaries**
   - Stancl tenancy is central to launch. Current helpers are careful, but tenant leakage is a launch-class defect.
   - Add explicit cross-tenant Feature/Integration tests proving tenant A cannot read tenant B orders/products/settings via storefront/admin/API, including custom domain/subdomain routing if implemented.

### Priority 1 — should be done before public beta

5. **Browser/pre-release smoke workflow**
   - Keep `.github/workflows/browser-smoke.yml` healthy and expand its curated subset only when a launch-critical journey earns coverage.

6. **Email and queue workflows**
   - Many mail/command tests exist, but launch-critical customer emails should have a compact Feature checklist: signup welcome, password reset, order confirmation, order status update, abandoned cart/review request if enabled.

7. **Plan/subscription gates**
   - Ensure Feature tests cover plan restrictions, expired trial behavior, subscription requirement middleware, upgrade flow, billing portal access, and plan swap from the user perspective.

8. **Custom domain/storefront enabled/maintenance modes**
   - Middleware tests exist. Add behavior tests around real routes for central domains vs tenant domains, disabled storefront, custom domain pending/active, and platform maintenance bypass rules.

### Priority 2 — quality improvements

9. **Move structural source assertions to Arch tests**
   - Use Arch for forbidden namespaces/imports and Filament 5 API constraints. Behavior tests should not inspect source text.

10. **Add coverage reporting threshold later**
   - Composer includes `test:coverage`, but CI does not enforce line/branch coverage. Do not block launch on this. Add after suite boundaries are cleaned up.

11. **Maintain browser fixture lifecycle documentation**
   - The current lifecycle is documented in `docs/operations.md`; update that runbook when fixture commands or generated session files change.

## Recommended testing style guide

### Naming and placement

- Put the test in the lowest suite that proves the intended risk:
  - pure logic → Unit;
  - module collaboration → Integration;
  - user/operator behavior → Feature;
  - real browser smoke → Browser;
  - structure/convention → Arch.
- Name tests by behavior: `test('valid stripe webhook updates tenant subscription')`, not `test('method exists')`.
- Prefer one clear business assertion per test, with additional state assertions only when they prove the same behavior.

### Mocking policy

- Mock external boundaries: Stripe API client, PayPal verifier, Resend/mail transport, HTTP clients, filesystem where needed.
- Do not mock the class under test’s core collaborators in Feature workflow tests unless the collaborator is the external boundary.
- For high-risk flows, prefer real Laravel container + DB + model/action wiring with fake mail/queue/events.

### Source assertions policy

Allowed:
- Arch tests for forbidden namespaces, forbidden methods, layering, and naming.
- Temporary regression tests during refactor, clearly marked and later replaced.

Avoid:
- Feature tests that pass because a string exists in a PHP file.
- Source assertions for webhook dispatch, idempotency, route behavior, or billing behavior. Execute the behavior instead.

### Tenancy policy

Every test touching tenant-aware data should state which context it runs in:
- central test setup via `setUpCentralTest()`;
- tenant test setup via `setUpTenantTest()`;
- route/domain setup via `tenancy.central_domains` and host headers when relevant.

Add negative isolation assertions when testing tenant data access:
- tenant A can read its own record;
- tenant A cannot read tenant B’s record;
- central admin access is explicitly separate.

### Filament 5 constraints to encode in tests/Arch

Keep explicit guards for known recurring mistakes:
- Do not use `Filament\Tables\Actions\*`; use `Filament\Actions\*`.
- Do not use `Filament\Forms\Get` / `Filament\Forms\Set`; use `Filament\Schemas\Components\Utilities\Get` / `Set`.
- Do not use `HasSlideOverForm`; use `EditAction::make()->slideOver()`.
- Do not call `->css()` on Panel; use `renderHook('panels::head.end')` with a link tag.
- BlogPosts are the only resource expected to have full create/edit page routes; other resources should use slide-overs.

## Suggested next actions

1. Create/merge this style guide into project docs or `CLAUDE.md` so all subagents use the same suite boundaries.
2. Add an Arch test file for the explicit Filament 5 forbidden APIs and source-level structure checks currently scattered in Feature/Integration/Unit.
3. Pick 6 launch-critical workflows and add/upgrade high-level Feature tests:
   - signup → tenant → notifications → onboarding redirect;
   - Stripe subscription webhook updated/deleted;
   - Stripe Connect account update;
   - storefront order → Stripe checkout redirect;
   - cross-tenant isolation for products/orders/settings;
   - plan/trial gate behavior.
4. Add a manual `browser-smoke.yml` workflow for the Browser suite or a curated `--group=launch-smoke` subset.
5. Review and move misfiled Unit/Integration tests as touched; do not churn the entire suite in one PR.

## Verification notes

Commands attempted/used:
- `find tests -type f -name '*Test.php'` for suite counts.
- `rg "^(it|test)\(" tests --glob '*Test.php'` for approximate test declaration count.
- `find . -maxdepth 3` and `read` on `composer.json`, `phpunit.xml`, `tests/Pest.php`, and CI workflows.
- Static scans for `skip(`, `todo(`, `only(`, route/HTTP/Livewire/artisan signals, and source-code assertion patterns.

Local execution blocked:
- `php -v` failed with `command not found: php`, so no Pest/PHPStan/Pint gate was run in this environment.
