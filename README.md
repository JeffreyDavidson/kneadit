# KneadIt

KneadIt is a multi-tenant SaaS application for independent bakeries. It provides each bakery with a public storefront and a Filament administration panel for orders, products, customers, operations, marketing, and reporting. The central application handles registration, onboarding, subscriptions, platform administration, and public marketing content.

KneadIt runs on PHP 8.5, Laravel 13, Filament 5, Livewire 4, Tailwind CSS 4, Vite 7, Pest 5, PHPUnit 13, and `stancl/tenancy` 3.

## Local setup

Prerequisites:

- PHP 8.5 with the extensions required by Laravel and SQLite
- Composer
- Node.js 24+ and npm
- A local domain that resolves `kneadit.test` and `*.kneadit.test` to the application (Laravel Herd supplies this on the primary development machine)

Install the application:

```bash
composer run setup
```

The setup script installs PHP and JavaScript dependencies, creates `.env`, generates an application key, runs central migrations, and builds frontend assets. Review `.env` before using integrations. The default local configuration uses SQLite, the database queue, log mail, and no live Sentry or payment credentials.

Start the application, queue listener, log viewer, and Vite development server:

```bash
composer run dev
```

The central application is expected at `http://kneadit.test`. Tenant storefronts use subdomains such as `http://example-bakery.kneadit.test`.

## Common commands

```bash
composer test              # Unit, integration, feature, and architecture tests
php artisan test           # All suites, including Browser
composer run lint          # Pint on changed PHP files
composer run analyse       # Application and Pest PHPStan configurations
composer run types         # 100% Pest type-coverage requirement
composer run filacheck     # Filament conventions
composer run test:rector   # Rector dry run
composer run check         # Full PHP quality suite
npm run build              # Production frontend build
```

Browser tests require a running local application, seeded browser fixtures, and Playwright. See [Testing](docs/operations.md#browser-tests).

### Shared development commands

KneadIt and Ringside share explicit command names while retaining each application's
Pint rules, Rector exclusions, PHPStan levels, and test coverage requirements:

| Command | Behavior |
| --- | --- |
| `composer lint:dirty` | Fix formatting in changed PHP and Blade files |
| `composer test:lint` | Check PHP and Blade formatting |
| `composer test:types` | Run application and Pest static analysis |
| `composer test:type-coverage` | Check the 100% Pest type-coverage requirement |
| `composer test:rector` | Check application and Pest Rector rules without changing files |
| `composer rector:fix` | Apply application and Pest Rector transformations |
| `composer test:application` | Run non-browser tests, including architecture tests |
| `composer test:browser` | Run browser tests after the fixture setup linked above |
| `composer check` | Run the existing PHP, Filament, and frontend checks |
| `composer test:push` | Run `check`, then browser tests |

Existing commands remain available. In KneadIt, `lint` fixes only changed files,
`rector` is a dry run, and `test` clears the configuration cache before running
non-browser tests. Use the explicit shared names when switching between apps.
For a filtered run, pass arguments directly: `composer test:application -- --filter=Example`.

Both apps use Pint's Laravel preset with Blade formatting and keep their additional
rules in their own `pint.json`. Pint owns Blade formatting through its Prettier
plugins; application-specific exceptions are not copied between projects.

## Architecture

The central database owns platform users, tenants, domains, subscriptions, and platform administration data. Each tenant has a separate SQLite database under `TENANT_DB_PATH` (the project database directory by default). Tenant identification occurs from the request domain before sessions and authentication use the database.

Important entry points:

- `routes/web.php` — central route composition entry point
- `routes/central/` — central authentication, platform operations, marketing, and SEO routes
- `routes/billing.php` — SaaS subscription checkout and Stripe webhooks
- `routes/tenant.php` — tenant middleware boundary and route composition
- `routes/tenant/access.php` — tenant PWA, invitations, impersonation, driver, and integration routes
- `routes/tenant/admin.php` — authenticated tenant admin utilities
- `routes/tenant/account.php` — customer account authentication and profile routes
- `routes/tenant/storefront.php` — tenant public storefront and content routes
- `routes/tenant/orders.php` — tenant ordering, payment callbacks, cart, and order-access routes
- `routes/tenant/api.php` — tenant JSON endpoints grouped by read/write throttling
- `app/Http/Controllers/Central` — platform-facing HTTP controllers grouped by central concern
- `app/Http/Controllers/Tenant` — tenant HTTP controllers grouped by surface (`Admin`, `Api`, `Catering`, `Invitations`, `Marketing`, `Orders`, and `Storefront`)
- `app/Providers` — framework wiring split into bindings, infrastructure, rate limits, and application/UI hooks
- `app/Actions` — single-purpose write operations
- `app/Queries` and `app/Builders` — reusable read behavior
- `app/Services/Settings` — tenant and platform settings access
- `app/Filament` — tenant administration
- `app/Filament/Central` — platform administration
- `resources/views/central` — central application views by concern
- `resources/views/tenant` — tenant storefront, account, admin, and invitation views
- `resources/views/components` — reusable Blade components grouped by surface
- `resources/views/shared` — shared Blade includes such as analytics and order-form scripts

See [Architecture](docs/architecture.md) for request flow, domain boundaries, order/payment behavior, and settings design. See [Operations](docs/operations.md) for queues, scheduling, deployment, testing, security, and monitoring.

## Development rules

Project conventions are documented in `AGENTS.md` and the applicable repository skills under `.ai/skills/`. In particular:

- Keep write logic in invokable action classes.
- Keep tenant data access inside an initialized tenancy context.
- Store money as integer cents and use the existing money casts/value objects.
- Use policies and gates for authorization.
- Add or update tests for behavioral changes.
- Never commit credentials, `.env` contents, or temporary debugging code.

## Deployment model

The repository uses `develop` for staging and `main` for production. Production is deployed to Laravel Forge; merging or pushing to `main` is therefore a release action, not a routine verification step. Deployment requirements and the release checklist are in [Operations](docs/operations.md#deployment-and-release).
