# Deep application audit

This audit is based on the current `develop` tree (Laravel 13.23, PHP 8.5, Filament 5.3) and is intended to guide small, reviewable improvements. It identifies opportunities; it does not authorize a wholesale rewrite or a mass file move.

## Executive summary

KneadIt has unusually strong foundations for a growing Laravel application:

- central and tenant databases are explicitly modeled and documented;
- business code is already grouped by domain in Actions, Models, Queries, Builders, Policies, and Reports;
- PHPStan is configured at maximum level with Pest type analysis and architecture tests;
- money values, backed enums, typed DTOs, tenancy queue bootstrapping, and protected storage boundaries are established;
- CI covers tests, static analysis, Rector, Pint, Filament linting, type coverage, security, and frontend builds.

The largest opportunities are not framework replacement. They are reducing orchestration size, making boundaries explicit, standardizing read/write shapes, and extracting reusable Laravel-native components from presentation-heavy classes.

## Priority roadmap

| Priority | Finding | Evidence | Recommended first slice |
| --- | --- | --- | --- |
| P0 | Legacy import is a 602-line all-domain transaction using raw tables and unvalidated arrays | `app/Actions/Tenants/ImportLegacyBakeryData.php` | Introduce a validated import DTO/schema and per-domain importer contracts; retain one transaction and idempotency tests |
| P1 | Several Filament pages and widgets contain substantial queries, mutation workflows, and formatting | `app/Filament/Resources/CateringInquiries/Pages/ViewCateringInquiry.php`, `app/Filament/Pages/Settings/ManageSettings.php`, `app/Filament/Pages/Operations/StaffManagement.php` | Extract one use case or query at a time into Actions/Queries; leave Filament as an adapter |
| P1 | Reporting returns mixed currencies, arrays, raw aggregates, and formatted strings | `app/Reports/*`, `app/Services/Reporting/WeeklyDigestDataCollector.php` | Define report result DTOs and a single money presentation boundary |
| P1 | Provider and command code still resolves application actions in a few delivery adapters | `app/Console/Commands/PayPal/CheckPayPalPaymentsCommand.php`, `app/Http/Controllers/Stripe/StripeWebhookController.php` | Inject actions into commands/controllers where direct construction is practical; keep container resolution only at framework entry points |
| P1 | Central Filament pages open tenant databases inline | `app/Filament/Central/Pages/DataExport.php`, `app/Filament/Central/Resources/TenantResource/Pages/ViewTenant.php` | Move tenant aggregation into named tenant queries/services and add central-context tests for bounded iteration |
| P2 | Raw SQL is spread across reports, analytics, widgets, and services | 38 production `DB::table`/`selectRaw`/`DB::raw` sites | Promote repeated projections to domain Queries or custom Builders; isolate dialect-specific SQL behind one class |
| P2 | Route binding resolves a resolver while routes are registered | `routes/web.php` | Use a lazy binding closure or explicit route resolver registration so boot-time resolution does not capture request state |
| P2 | Test suite is broad but slow and has shared helper complexity | 843 PHP test files; large `tests/Pest.php` | Keep integration coverage, but extract fixtures into domain helpers and add targeted architecture/unit suites for fast feedback |
| P2 | A few infrastructure adapters expose internal exception messages to HTML | `app/Services/Filament/WidgetPreviewRenderer.php` | Log details server-side and return a stable safe placeholder message |

## Organization and domain structure

The current horizontal technical layout is appropriate for Laravel delivery concerns. Do not create a second parallel tree such as `app/Domains/*` until the existing domain namespaces have demonstrable ownership problems. The better rule is:

- business state and transitions live beside their domain models;
- Queries and Builders own reusable reads;
- Services integrate external systems or coordinate multiple domains;
- HTTP, Filament, Console, Mail, and Notifications remain adapters;
- central and tenant context is explicit at the boundary.

Specific seams to clarify over time:

1. `Content`, `Marketing`, and `Engagement` overlap around reviews, campaigns, messaging, and public content. Decide ownership by state transition, not by screen name, and migrate one use case at a time.
2. `Products` has already been reduced to an orphaned action namespace; new catalog behavior should use `Inventory` consistently.
3. `Reports`, `Queries/Analytics`, `Queries/Dashboard`, and `Services/Reporting` need a documented distinction between reusable reads, report composition, and delivery-specific digest formatting.
4. `Services/Platform` contains both platform business behavior and infrastructure health/Forge adapters. Keep platform use cases there, but isolate external adapters under explicit integration subnamespaces when a second use case appears.

## Laravel-native opportunities

### Prefer container injection at application boundaries

Recent slices removed service-locator calls from domain actions and provider services. Continue that rule for commands and controllers when dependencies are application behavior. Laravel should resolve the entry-point class; the class should receive its collaborators through its constructor or method injection.

### Use Form Requests and typed input objects consistently

The application already uses Form Requests and DTOs. Extend that pattern to legacy imports, large Filament forms, and any controller that still passes unstructured arrays into an Action. Use `validated()` arrays only at the edge, then convert once into a typed object.

### Prefer Eloquent scopes, relations, and custom Builders for repeated reads

Laravel 13 supports attribute-based scopes, and the repository already uses custom builders. Repeated `whereBetween`, paid-order, active-order, and aggregate expressions should become named scopes or query objects rather than being copied across Reports, Widgets, and Services.

### Use Resources/DTOs for API and report boundaries

API Resources are already present. Report and dashboard payloads should similarly have typed result objects where they cross from application code into Filament, Blade, mail, or JSON. Keep currency formatting at the presentation edge.

### Use events/jobs for slow or consequential work

Tenant-wide analytics, exports, email campaigns, imports, and external API synchronization should expose a queued application action when latency or failure isolation matters. Keep synchronous behavior only where the caller needs an immediate result or transactionally coupled state.

### Let Laravel own serialization and validation

Avoid manually recreating framework behavior for validation, URL generation, authorization, and notifications. Prefer Form Requests, Policies, signed routes, `route()`/resource `getUrl()`, `Notification`, `Storage`, `Number`, and typed configuration accessors already established in the codebase.

## Class design findings

### `ImportLegacyBakeryData`

This class is the clearest extraction target. It coordinates categories, products, customers, orders, recipes, financials, operations, engagement, and settings in one transaction. The transaction boundary is valuable; the table-specific mapping should be delegated to small importers that receive a validated, normalized dataset and return legacy-to-new ID maps. Add:

- an import envelope DTO with required sections and version metadata;
- explicit conflict/idempotency policy;
- per-section counts and failure context;
- a dry-run mode before writes;
- tests for missing foreign keys, duplicate natural keys, and rerunning the same payload.

### Large Filament classes

Classes over roughly 250 lines are not automatically wrong, but the current largest pages combine schema declarations, queries, mutations, authorization, notifications, and formatting. Extract only when a responsibility has a name and a testable contract. A good first target is one mutation from `ViewCateringInquiry`, followed by a query from `DataExport`.

### Models and relationships

`Order` has relationships spanning Orders, Customers, Financial, Engagement, and Staff. These relationships are convenient, but they also make the model a coupling hub. Keep the relationships needed for navigation and authorization; move cross-domain aggregates and workflow decisions into Queries and Actions rather than adding more methods to the model.

### Services versus Actions

Use an Action for one business command or state transition, a Query for reusable reads, and a Service for an external adapter or multi-step coordinator. If a Service only wraps one model write, it is a candidate for an Action. If a Report only executes one reusable read, it is a candidate for a Query plus a result DTO.

## Persistence, money, and SQL

- Money columns are integer cents, but report payloads frequently convert to floats or formatted currency strings. Establish a result contract that carries `Money` until the final UI/mail serialization step.
- Raw `SUM()` and `AVG()` values bypass Eloquent casts. Keep explicit conversions, but centralize them in query result mappers so every consumer handles cents and nulls identically.
- Raw table writes are justified for provisioning/import compatibility, but ordinary domain writes should use model queries, Actions, casts, and events where observers matter.
- The 38 raw SQL sites should be classified as either (a) unavoidable aggregate/dialect SQL, (b) repeated query logic, or (c) import/provisioning compatibility. Only category (b) needs immediate extraction.
- SQLite-specific `GROUP_CONCAT` and date expressions should remain behind dedicated Queries so application callers do not need to know the active database dialect.

## HTTP, Filament, and view layer

- Keep controllers and Filament pages thin: authorize, validate, call an Action/Query, and shape output.
- Replace repeated inline `resolve(...)` in Filament pages with method/constructor injection where lifecycle permits it.
- Prefer Filament resource/page URLs and typed component closures; avoid hard-coded route construction in presentation classes when a resource owns the destination.
- Treat `HtmlString` as an explicit trust boundary. Do not concatenate exception messages into user-visible markup; use a stable message and log the exception context.
- Central pages that inspect tenant data should call named tenant aggregation services and show bounded, paginated data rather than opening every tenant database in a request.

## Queues, scheduling, and integrations

- Provider clients should be constructed through injectable adapters and should return typed results or domain exceptions, not provider payloads throughout the application.
- Commands should dispatch Actions or Jobs; they should not contain provider HTTP details or duplicate payment transitions.
- Every tenant-wide scheduled task should have bounded work, retry/backoff behavior, tenant context initialization, and an idempotency/observability story.
- External calls should record correlation IDs and safe status metadata without logging credentials, raw payloads, or customer-sensitive data.

## Testing and quality

- Preserve the current architecture tests and add rules only when they describe a real boundary.
- Add unit tests for pure mappers/calculators and integration tests for transaction/tenancy behavior. Keep framework-heavy tests out of unit suites.
- The shared `tests/Pest.php` helper file is powerful but large. Move domain-specific setup into narrowly named helpers when a new domain slice is added; this reduces hidden fixture coupling.
- Replace test doubles consistently with `JMac\\Testing\\Double` for typed collaborators, retaining Mockery only where its partial/mock behavior is specifically needed.
- Add regression tests before extracting importers, report DTOs, or Filament mutations. Snapshot tests should cover stable output contracts, not implementation details.

## Sequenced implementation plan

1. Finish the current small dependency-injection cleanup and keep every PR based on the latest `develop` tip.
2. Extract one `ViewCateringInquiry` mutation into an Action with an integration test.
3. Extract central tenant export aggregation into a named Query/Service with bounded iteration tests.
4. Introduce a typed report result for one report (Sales or Financial) and normalize money at serialization.
5. Split `ImportLegacyBakeryData` behind per-domain importer contracts without changing its public command behavior.
6. Add import dry-run, idempotency, and foreign-key diagnostics.
7. Consolidate repeated analytics aggregates into Queries/Builders.
8. Replace unsafe user-facing exception rendering with stable placeholders and structured logs.
9. Audit queued tenant-wide tasks for idempotency, retries, and bounded work.
10. Reassess namespace moves only after the above contracts and architecture tests have stabilized.

## Changes deliberately deferred

- Do not introduce repositories for Eloquent models without a real alternate persistence boundary.
- Do not create a generic `Domain` base class, generic `Manager`, or generic `DatabaseValue` abstraction.
- Do not move all Filament classes into business namespaces; Filament is a delivery adapter.
- Do not convert every raw SQL aggregate to Eloquent if it would make the query slower, less portable, or less readable.
- Do not combine central and tenant databases into one schema; the current isolation is a core product and security boundary.
