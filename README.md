# KneadIt

KneadIt is a multi-tenant SaaS application for independent bakeries. It provides each bakery with a public storefront and a Filament administration panel for orders, products, customers, operations, marketing, and reporting. The central application handles registration, onboarding, subscriptions, platform administration, and public marketing content.

KneadIt runs on PHP 8.4, Laravel 13, Filament 5, Livewire 4, Tailwind CSS 4, Vite 7, Pest 4, and `stancl/tenancy` 3.

## Local setup

Prerequisites:

- PHP 8.4 with the extensions required by Laravel and SQLite
- Composer
- Node.js and npm
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

## Architecture

The central database owns platform users, tenants, domains, subscriptions, and platform administration data. Each tenant has a separate SQLite database under `TENANT_DB_PATH` (the project database directory by default). Tenant identification occurs from the request domain before sessions and authentication use the database.

Important entry points:

- `routes/web.php` — central marketing, authentication, onboarding, and shared root routing
- `routes/billing.php` — SaaS subscription checkout and Stripe webhooks
- `routes/tenant.php` — tenant storefront, administration support, API, Stripe Connect, and invitations
- `app/Actions` — single-purpose write operations
- `app/Queries` and `app/Builders` — reusable read behavior
- `app/Services/Settings` — tenant and platform settings access
- `app/Filament` — tenant administration
- `app/Filament/Central` — platform administration

See [Architecture](docs/architecture.md) for request flow, domain boundaries, order/payment behavior, and settings design. See [Operations](docs/operations.md) for queues, scheduling, deployment, testing, security, and monitoring.

## Development rules

Project conventions are documented in `CLAUDE.md` and any applicable repository agent skills. In particular:

- Keep write logic in invokable action classes.
- Keep tenant data access inside an initialized tenancy context.
- Store money as integer cents and use the existing money casts/value objects.
- Use policies and gates for authorization.
- Add or update tests for behavioral changes.
- Never commit credentials, `.env` contents, or temporary debugging code.

## Deployment model

The repository uses `develop` for staging and `main` for production. Production is deployed to Laravel Forge; merging or pushing to `main` is therefore a release action, not a routine verification step. Deployment requirements and the release checklist are in [Operations](docs/operations.md#deployment-and-release).
