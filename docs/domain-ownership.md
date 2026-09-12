# Domain ownership map

This document records the intended ownership boundaries for the application. It is an audit baseline for future refactors; it does not require moving files immediately.

## Boundaries

KneadIt has two deployment/data contexts and several business domains. A domain owns its business vocabulary, state transitions, persistence models, queries, and policies. Laravel and third-party adapters remain at the edge of the domain that uses them.

| Domain | Owns | Context |
| --- | --- | --- |
| Platform & tenancy | tenants, domains, plans, subscriptions, trials, onboarding, referrals, platform settings, support, audits, health, impersonation | Central, with explicit tenant context hand-off |
| Storefront & content | storefront presentation, branding, menus, blog, galleries, catering presentation, reviews, policies, PWA metadata | Tenant |
| Orders & checkout | carts, checkout orchestration, order lifecycle, discounts applied to orders, fulfillment, invoices, payment state, refunds | Tenant; payment providers are external adapters |
| Inventory & production | products, categories, ingredients, recipes, suppliers, stock, waitlists, seasonal items, production planning | Tenant |
| Customers | customer profiles, notes, favorites, customer-facing inquiries and account flows | Tenant |
| Engagement & marketing | loyalty, campaigns, surveys, reminders, review workflows, contact messaging, customer communications | Tenant |
| Financial | income, expenses, coupons, gift cards, refunds, tax/export reporting, financial summaries | Tenant; Stripe/PayPal transport is not financial ownership |
| Operations & staff | schedules, blocked dates, holidays, capacity, check-ins, staff, roles, activity and webhook operations | Tenant, with platform operational tooling kept in Platform |
| Analytics | page views, product impressions, retention and reporting projections | Tenant; identifiers and retention are privacy-sensitive |

## Current code organization

The codebase already groups most Actions, Builders, DTOs, Enums, Models, Policies, Queries, Reports, and Services by domain. The top-level `Http`, `Filament`, `Console`, `Mail`, and `Notifications` trees are delivery adapters and should remain organized by surface or framework concern rather than becoming a second business taxonomy.

The following existing placements are intentional and should be preserved:

- `app/Actions/Orders` owns order transitions even when invoked by a Stripe or PayPal adapter.
- `app/Models/Financial` owns coupons and gift cards while `app/Actions/GiftCards` and `app/Actions/Financial` expose their use cases.
- `app/Services/Stripe` and `app/Services/PayPal` translate provider payloads; they delegate state changes to Orders, Financial, or Platform actions.
- `app/Services/Tenants` owns tenancy initialization and database lifecycle; it is infrastructure for all tenant domains, not a replacement for a business domain.
- `app/Services/Settings` owns settings storage and typed settings composition. Consumers should depend on the typed settings contract rather than reaching into setting records.

## Refactoring rules

1. Put a new business class beside the domain model it changes. Name it after the use case (`CreateOrder`, `AdjustLoyaltyPoints`) rather than after a technical layer.
2. Keep controllers, Livewire components, Filament pages, mailables, and commands thin. They validate input, authorize, call a domain action/query, and shape the response.
3. A cross-domain dependency must be explicit. Prefer an action, query, DTO, value object, or event over reaching into another domain's tables or private service state.
4. Provider code belongs in an integration namespace (`Stripe`, `PayPal`, Forge, mail delivery). It may call domain actions, but domain code should not depend on provider response classes.
5. Keep central/tenant boundaries visible in names and constructors. Central queries must not open every tenant database during a request; tenant actions must run after tenancy initialization.
6. Use events for consequential side effects (notifications, analytics, audit entries) so the initiating domain does not accumulate delivery concerns.

## Audit candidates

These are follow-up slices, not a mandate for a bulk move:

- Review `Marketing`, `Engagement`, and `Content` names where a class crosses customer messaging, public content, and review workflows. Preserve behavior first, then move one cohesive use case at a time.
- Review `Products` actions alongside `Inventory` actions. Product catalog state belongs to Inventory; keep compatibility namespaces only while callers are migrated.
- Review `Services/Reporting` and `Reports/*` for a single reporting boundary, separating read projections from export/delivery adapters.
- Review `Services/Platform` for classes that are actually operational infrastructure or integrations; move only when ownership and tests are unambiguous.
- Add architecture tests or static rules for forbidden central-to-tenant database access and provider-to-domain direction before large reorganizations.

The next implementation work should take one candidate at a time, add or adjust tests, and use a focused pull request. This map is the source of truth for deciding where that slice belongs.
