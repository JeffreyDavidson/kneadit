# KneadIt operations

## Runtime services

A production environment needs all of the following:

- PHP 8.4 and the web server/PHP process manager
- a central database plus writable storage for per-tenant SQLite databases
- a long-running queue worker using the configured queue connection (database by default)
- Laravel's scheduler invoked once per minute
- built Vite assets and writable Laravel storage/cache directories
- configured mail and any enabled Stripe, Stripe Connect, PayPal, Resend, Fathom, AWS, or Sentry credentials

Do not run tenant database files from ephemeral storage. `TENANT_DB_PATH` must resolve to durable storage shared by every process serving the application. Tenant database names are confined to that root, symlinked database files are refused, and newly provisioned files use owner-only permissions.

Public uploads must also survive release replacement. On zero-downtime deployments, set `PUBLIC_STORAGE_PATH` to an absolute, writable directory outside the numbered release directories. Both the `public` filesystem disk and Laravel's `public/storage` link resolve to this path. Preserve and back up this directory with the tenant databases.

## Queue operations

Local `composer run dev` starts `queue:listen` alongside the web server, logs, and Vite. Production should use a supervised `queue:work` process appropriate to the configured connection and restart workers after deployments.

The tenancy queue bootstrapper serializes tenant context with queued work and restores it before the job executes. Jobs dispatched from central context remain central. When diagnosing a job, verify both its queue failure and the tenant/database context in its logs.

Operational checks:

```bash
php artisan queue:failed
php artisan queue:retry <id>
php artisan queue:restart
```

Retry only after correcting the cause and confirming the operation is safe to repeat. Payment and webhook paths have explicit idempotency protections, but not every arbitrary job is necessarily repeatable.

## Scheduled commands

`routes/console.php` registers one production-only, single-server, non-overlapping background group:

| Frequency | Command | Responsibility |
| --- | --- | --- |
| Every 15 minutes | `tenants:sync-onboarding-metrics` | Reconcile central onboarding counts from tenant databases |
| Every 30 minutes | `health:check` | Application health checks |
| Hourly | `paypal:check-payments` | Reconcile PayPal invoices |
| Hourly | `reviews:send-requests` | Send eligible review requests |
| Hourly | `carts:send-abandonment-emails` | Send abandoned-cart reminders |
| 03:00 and 15:00 | `backup:databases --keep=7` | Back up central and tenant databases |
| Daily 04:00 | `webhooks:prune` | Prune webhook delivery history |
| Daily 04:15 | `analytics:prune-page-views` | Prune page-view analytics after the configured retention window |
| Daily 06:00 | `platform:audit-free-forever` | Audit free-forever grants |
| Daily 07:00 | `churn:check` | Detect at-risk tenants |
| Daily 07:00 | `inventory:send-low-stock-alert` | Send low-stock alerts |
| Daily 08:00 | `birthday:send-emails` | Send birthday engagement email |
| Daily 09:00 | `checkins:send` | Send scheduled check-ins |
| Daily 10:00 | `orders:send-repeat-reminders` | Send repeat-order reminders |
| Daily 10:00 | `trial:check` | Enforce/notify trial state |
| Monday 08:00 | `digest:weekly` | Send the weekly digest |

The scheduler group requires a cache/lock backend compatible with `onOneServer()` and `withoutOverlapping()`. Because tasks run in the background, monitor command logs and process failures rather than relying solely on the scheduler invocation exit code.

Inspect registration and next-run times with:

```bash
php artisan schedule:list
```

## Database operations and backups

Central schema changes use the normal migration command. Tenant schema changes belong under `database/migrations/tenant` and must be applied to every tenant:

```bash
php artisan migrate --force
php artisan tenants:migrate --force
php artisan tenants:sync-onboarding-metrics
```

Tenant provisioning runs tenant migrations automatically. Existing tenant migrations are forward-only history and must not be edited after merge.

`backup:databases --keep=7` is the application-owned backup entry point. Confirm its output after deployments and periodically test restoration of both the central database and at least one tenant database. A complete recovery requires the central tenant/domain records and matching tenant SQLite files.

Use `php artisan tenants:doctor` to inspect mismatches between central tenant records and database files; production request handling directs operators to `tenants:doctor --fix` when a database is missing.

## Deployment and release

The repository uses simplified gitflow:

- `develop` deploys to staging.
- `main` deploys to production through Laravel Forge.
- release versions are tags surfaced through the root `VERSION` file and `config/kneadit.php`.

Before merging a release:

1. Review the complete `develop...main` diff and all migrations.
2. Run the PHP quality suite and production frontend build.
3. Run targeted browser smoke tests against a realistic seeded tenant.
4. Verify Stripe webhook endpoints/secrets and other environment changes before traffic reaches new code.
5. Deploy central migrations, tenant migrations, cached configuration/routes/views as appropriate, and built frontend assets. Tenant migrations encrypt credentials and pseudonymize analytics identifiers, so the production `APP_KEY` must remain stable and available.
6. Restart queue workers so they load the new release.
7. Confirm `/up`, the central landing page, a tenant storefront, both Filament login pages, queue processing, scheduler execution, and recent error logs.
8. Verify database backups before any migration that is difficult to reverse.

Canonical pre-release commands:

```bash
composer run check
npm run build
php artisan test --testsuite=Browser
git diff --check
```

The full suite may be lengthy. Use bounded targeted tests during development, but do not replace release-level coverage with a narrow selection.

## Testing

Pest suites are organized as Unit, Integration, Feature, Arch, and Browser. `composer test` and CI intentionally exclude Browser; `php artisan test` includes it unless a suite is excluded.

Useful commands:

```bash
composer test
php artisan test --testsuite=Unit
php artisan test --testsuite=Integration
php artisan test --testsuite=Feature
php artisan test --testsuite=Arch
php artisan test --filter='descriptive test name'
```

Tests default to in-memory SQLite, synchronous queues, array mail/cache/session drivers, and a placeholder Stripe secret via `phpunit.xml`. Feature helpers in `tests/Pest.php` create central tenant/domain records and initialize isolated tenant databases. Tests that exercise request tenancy should use a host/domain record rather than bypassing middleware.

### Browser tests

Browser tests use Pest Browser/Playwright against live local URLs. Defaults are:

- `BROWSER_TEST_CENTRAL_URL=http://kneadit.test`
- `BROWSER_TEST_STOREFRONT_URL=http://browser-test.kneadit.test`

They expect the central application and a `browser-test` tenant to be reachable, frontend assets to be available, and fixture records/authentication state required by admin tests to exist. `tests/Browser/Helpers/prepare-admin-session.py` creates browser authentication state used by authenticated central and tenant visits.

A practical local sequence is:

1. Install Playwright's Chromium browser if it is not already installed: `npx playwright install chromium`.
2. Start the application and Vite with `composer run dev` (or serve prebuilt assets).
3. Provision the tenant fixture: `php artisan tenants:provision-test-tenant`.
4. Seed the central fixture: `php artisan db:seed --class="Database\\Seeders\\BrowserTestCentralFixtureSeeder"`.
5. Create the authenticated browser sessions: `python3 tests/Browser/Helpers/prepare-admin-session.py`.
6. Run `php artisan test --testsuite=Browser` or a targeted browser file.

Browser tests perform real writes. Use disposable local fixture data, never a production tenant.

## Security

- Web responses add `nosniff`, `SAMEORIGIN`, and strict-origin referrer headers.
- CSP is enforced by default. Per-request nonces authorize inline script/style blocks, while inline script blocks without a nonce are rejected. Violations POST a bounded, allow-listed payload to `/csp-report`; `CSP_MODE=report-only` is an explicit temporary rollback switch.
- Stripe and Stripe Connect webhook endpoints are excluded from CSRF but verify Stripe signatures. Connect delivery records provide idempotency across supported events.
- Sensitive write routes use named throttles; signed URLs protect verification, exports, impersonation, and customer links where configured.
- Policies, gates, and middleware protect application and Filament operations. A successful UI hide is not a substitute for server-side authorization.
- Sentry defaults to no-op when its DSN is unset. PII transmission is disabled by default.
- Page-view analytics store only an `APP_KEY`-derived visitor identifier, not raw session IDs, IP addresses, or user-agent strings. `PAGE_VIEW_RETENTION_DAYS` defaults to 90 days, and `analytics:prune-page-views` enforces it across tenants.
- Filesystem disks throw on failed operations instead of silently returning false. Product CSVs use a dedicated, private, non-servable `imports` disk; public storage is reserved for intentionally public assets.
- Never expose Stripe/PayPal/Resend/AWS/Sentry credentials, `.env`, tenant databases, or backup archives through public storage or logs.

Before every release, check for debug helpers, temporary routes, unexpected authorization changes, unsafe mass assignment, raw SQL interpolation, user-controlled paths, and secrets in the diff.

## Logging, health, and incident diagnosis

Application logs use the configured Laravel log stack. Sentry receives unhandled exceptions and can trace requests, SQL, Livewire, queues, notifications, and outbound HTTP when configured. `/up` is Laravel's basic health endpoint and is excluded from Sentry performance tracing.

Important structured events include order placement, Stripe session creation/completion/refunds, Stripe Connect account changes/webhooks, PayPal failures, analytics recording failures, missing tenant databases, and tenancy auto-recovery. Logs should include identifiers such as tenant, order number, checkout session, or webhook event; they should not include payment credentials or unnecessary customer data.

For an incident, determine the active layer before changing data:

1. Confirm central versus tenant host and identify the tenant record/domain.
2. Confirm the tenant SQLite file exists and is readable by web, queue, and scheduler processes.
3. Inspect application/Sentry logs with the tenant and domain identifiers.
4. Inspect failed jobs and recent scheduler/health output.
5. For payments, compare the order state with Stripe/PayPal using the recorded external identifiers and webhook delivery history.
6. Repair with an existing idempotent command/action where possible; take a backup before manual data correction.

Minimum production monitoring should alert on `/up` failure, queue backlog/failed jobs, scheduler silence, backup failure or age, tenant database filesystem capacity, repeated webhook failure, and elevated 5xx/Sentry error rates.
