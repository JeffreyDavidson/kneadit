# Application Refactoring Roadmap

This roadmap turns the September 2026 application audit into eight reviewable workstreams. Each workstream should be implemented on its own branch and merged into `develop` through a squash-merge pull request.

## Delivery status

Workstreams 1–7 are complete and merged into `develop`. Workstream 8 is the
documentation closeout in this pull request.

| Workstream | Pull request | Status |
| --- | --- | --- |
| 1. Regression coverage and confirmed defects | [#1017](https://github.com/JeffreyDavidson/kneadit/pull/1017) | Merged |
| 2. Money-safe order pipeline | [#1018](https://github.com/JeffreyDavidson/kneadit/pull/1018) | Merged |
| 3. Customer metrics correction/consolidation | Included in #1017 | Merged |
| 4. Typed platform read models | [#1019](https://github.com/JeffreyDavidson/kneadit/pull/1019) | Merged |
| 5. Analytics and date-series projections | [#1020](https://github.com/JeffreyDavidson/kneadit/pull/1020) | Merged |
| 6. External provider contracts | [#1021](https://github.com/JeffreyDavidson/kneadit/pull/1021) | Merged |
| 7. Prep scheduling and abstraction hygiene | [#1022](https://github.com/JeffreyDavidson/kneadit/pull/1022) | Merged |
| 8. Documentation and delivery controls | This PR | In progress |

The implementation work was verified with focused integration tests,
architecture tests, PHPStan, Pint, Rector, and 100% application type coverage.
The local full-suite run is subject to a Pest browser-plugin shutdown/port
limitation; CI or an environment where the browser plugin can bind and release
its port should provide the final full-suite result.

## Goals and guardrails

- Preserve current behavior unless a workstream explicitly fixes a confirmed defect.
- Keep writes disjoint between workstreams; do not combine unrelated refactors in one pull request.
- Prefer existing `Money`, DTO, Query, Builder, Action, and Contract patterns.
- Do not introduce generic repositories, generic collections, or traits without repeated domain behavior.
- Run the narrowest relevant tests first, then `composer check` before each pull request.

## Dependency graph

```text
1 Regression tests and defect fixes
        ├──> 2 Money-safe order pipeline
        └──> 3 Customer metrics builder correction

4 Typed platform read models ──┐
5 Analytics projections       ├──> 7 Architecture cleanup review
6 Provider contracts          ┘

8 Documentation and delivery plan runs throughout and closes the program.
```

Workstream 1 should land first because it establishes regression coverage for the two confirmed defects. Workstreams 2 and 3 can then proceed independently. Workstreams 4–6 are independent of the order work, but should not be bundled together. Workstream 7 is a consolidation pass after the focused changes have landed.

## Workstream 1 — Regression coverage and confirmed defects

Branch: `fix/discount-and-customer-metrics-regressions`

Scope:

- Add a discount-pipeline test where sitewide/referral discounts and a coupon are applied together.
- Confirm that the coupon transaction records only the coupon discount.
- Confirm that cancellation reverses only the original coupon transaction amount.
- Add a customer-builder test where the newest order is cancelled and the older active order remains the last eligible order.
- Make only the smallest fixes required by those tests.

Primary files:

- `app/Pipes/Orders/RecordCouponUsage.php`
- `app/Actions/Orders/ReverseOrderDiscounts.php`
- `app/Pipes/Orders/OrderPipelineData.php`
- `app/Builders/Customers/CustomerQueryBuilder.php`
- `tests/Integration/Pipes/Orders/DiscountPipelineTest.php`
- `tests/Integration/Builders/Customers/CustomerQueryBuilderTest.php` (or the established equivalent)

Acceptance criteria:

- `discount_amount` remains the total order discount.
- `coupon_transactions.amount` contains only the coupon-specific amount.
- Coupon reversal uses the original coupon-specific transaction amount.
- Cancelled orders do not influence `last_order_date` in `withOrderMetrics()`.
- Existing discount and customer tests continue to pass.

## Workstream 2 — Money-safe order pipeline

Branch: `refactor/order-pipeline-money-state`

Depends on: Workstream 1

Scope:

- Replace mutable float monetary state in `OrderPipelineData` with `Money` or integer-cent values, following the project’s established `Money` type.
- Track discount sources separately: sitewide, referral, tier, coupon, and total discount.
- Centralize total recalculation and discount application to make pipe invariants explicit.
- Convert UI/input floats at the pipeline boundary only.

Primary files:

- `app/Pipes/Orders/OrderPipelineData.php`
- `app/Pipes/Orders/CalculateOrderTotals.php`
- `app/Pipes/Orders/ApplySitewideSale.php`
- `app/Pipes/Orders/ApplyCoupon.php`
- `app/Pipes/Orders/ApplyReferral.php`
- `app/Pipes/Orders/ApplyTierPerks.php`
- `app/Pipes/Orders/ApplyGiftCard.php`
- `app/Pipes/Orders/PersistOrder.php`
- Related order-pipeline tests

Acceptance criteria:

- No monetary accumulation uses binary floating-point arithmetic inside the pipeline.
- Existing order totals, discount stacking, gift-card behavior, and persistence remain unchanged.
- Tests cover fractional prices, percentage discounts, discount stacking, and totals clamped at zero.
- Pipe ordering is unchanged unless a test demonstrates that the order is incorrect.

## Workstream 3 — Customer metrics builder correction and consolidation

Branch: `fix/customer-order-metrics-scope`

Depends on: Workstream 1

Scope:

- Apply the active-order scope consistently to count, sum, and last-order-date metrics.
- Verify whether `withRfmMetrics()` should exclude cancelled orders; document the decision in a test.
- Consolidate duplicated customer order aggregate logic between `CustomerQueryBuilder` and `CustomerIntelligence` only if the resulting query remains readable.

Primary files:

- `app/Builders/Customers/CustomerQueryBuilder.php`
- `app/Services/Customers/CustomerIntelligence.php`
- Customer builder/intelligence tests

Acceptance criteria:

- Builder and service metrics use the same documented order eligibility rules.
- Cancelled-order, no-order, and mixed-order datasets are covered.
- No customer-facing metric changes occur without an explicit test explaining them.

## Workstream 4 — Typed platform read models

Branch: `refactor/platform-read-models`

Scope:

- Convert `TenantComparisonQuery` from a large static/service-locator API to an injectable query.
- Introduce typed DTOs for comparison rows, leaderboard rows, and summaries.
- Split `FeatureUsageQuery` into focused read models only where its current methods have different result shapes.
- Preserve existing callers through a small adapter where migration cannot be completed atomically.

Primary files:

- `app/Queries/Platform/TenantComparisonQuery.php`
- `app/Queries/Platform/TenantComparisonMetricsQuery.php`
- `app/Queries/Platform/FeatureUsageQuery.php`
- `app/DataTransferObjects/Platform/*`
- Filament widgets/pages and tests consuming these queries

Acceptance criteria:

- Public result boundaries no longer depend on unstructured mixed arrays where a stable shape exists.
- Tenant metric queries are injected once rather than resolved inside loops.
- Leaderboards and feature-usage visualizations render exactly as before.
- Query behavior is covered by focused integration tests.

## Workstream 5 — Analytics and date-series projections

Branch: `refactor/typed-analytics-projections`

Scope:

- Extract the repeated date-series aggregation pattern from `StatsOverviewQuery`.
- Give `StorefrontAnalyticsQuery` and `ReviewAnalyticsService` typed result boundaries.
- Keep database-dialect-specific expressions isolated and documented.
- Separate data retrieval from formatting and sentiment calculations.

Primary files:

- `app/Queries/Dashboard/StatsOverviewQuery.php`
- `app/Queries/Analytics/StorefrontAnalyticsQuery.php`
- `app/Services/Analytics/ReviewAnalyticsService.php`
- New analytics DTOs under `app/DataTransferObjects/Analytics`
- Dashboard, reporting, and analytics tests

Acceptance criteria:

- Existing chart series, date ranges, empty states, and timezone behavior remain unchanged.
- Aggregate rows are represented by named types at service/UI boundaries.
- Raw SQL is reduced only where the extracted projection preserves readability and database compatibility.

## Workstream 6 — External provider contracts

Branch: `refactor/external-provider-clients`

Scope:

- Introduce injectable client/adaptor boundaries for Forge, Stripe, and PayPal operations currently constructed inside services.
- Preserve provider-specific behavior while standardizing failure handling.
- Replace unsafe or overly broad provider response logging with safe structured context.
- Keep contracts focused on operations the application actually uses.

Primary files:

- `app/Services/Platform/ForgeService.php`
- Stripe checkout/connect/deposit services
- PayPal verification/payment services
- New provider contracts and adapters in the existing service structure
- Integration tests and fakes

Acceptance criteria:

- Provider-dependent services can be tested without constructing real SDK clients or making network calls.
- Failure details remain available to callers without leaking credentials or full provider payloads.
- Existing success/failure behavior and retry/idempotency semantics are preserved.

## Workstream 7 — Prep scheduling and abstraction hygiene

Branch: `refactor/prep-schedule-boundaries`

Scope:

- Separate `PrepScheduleService` data loading from pure schedule calculation.
- Introduce schedule/task DTOs only for stable consumer-facing shapes.
- Review Actions, Services, Contracts, Traits, Builders, and Eloquent collection usage after workstreams 1–6.
- Do not add custom Eloquent collections or generic traits unless repeated behavior now justifies them.
- Review duplicated stock-demand logic between order validation and stock services.

Primary files:

- `app/Services/Production/PrepScheduleService.php`
- Related production DTOs and consumers
- `app/Services/Orders/CheckOrderStockAvailability.php`
- `app/Pipes/Orders/ValidateStockAvailability.php`
- Associated tests

Acceptance criteria:

- Schedule generation can be tested independently from database loading.
- Existing schedule output, ordering, and performance remain stable.
- The review records explicit “keep as-is” decisions for focused traits, simple Actions, and the absence of custom collections.

## Workstream 8 — Documentation and delivery controls

Branch: `docs/application-refactoring-roadmap`

Scope:

- Update `docs/deep-application-audit.md` so completed PR #1010–#1016 work is no longer presented as outstanding.
- Link this roadmap from the architecture documentation.
- Record the confirmed order/customer defects and their eventual fixes.
- Add the final verification commands and known local test-environment limitation.
- Close each workstream only after its PR is merged and `develop` is synchronized.

Acceptance criteria:

- Documentation reflects the current codebase rather than the August archived snapshot.
- Every workstream has a linked PR, verification result, and explicit follow-up decision.
- No planning document is used as a substitute for the repository’s existing task/Kanban process.

## Verification gate for every workstream

Run focused tests first, then the applicable project gates:

```text
vendor/bin/pest <focused-tests> --colors=never
vendor/bin/phpstan analyse --no-progress --memory-limit=4G --error-format=table
vendor/bin/pint --test
vendor/bin/pest tests/Arch --colors=never
composer check
```

The full non-browser test suite must be run in CI or a local environment where the Pest browser plugin can shut down cleanly. The previous audit observed 100% type coverage and a browser-plugin port shutdown error locally; that environment issue must not be confused with a test failure.

## Delivery order

1. Workstream 1: regression tests and confirmed defect fixes.
2. Workstream 2: money-safe order pipeline.
3. Workstream 3: customer metrics correction/consolidation.
4. Workstream 4: platform read models.
5. Workstream 5: analytics projections.
6. Workstream 6: provider contracts.
7. Workstream 7: prep scheduling and final abstraction review.
8. Workstream 8: documentation closeout after the implementation PRs land.
