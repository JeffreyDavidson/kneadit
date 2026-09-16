# Sentry

Server-side error tracking + performance monitoring via `sentry/sentry-laravel`.

## Enabling

Sentry is wired up project-wide but **disabled by default** — the SDK no-ops
when `SENTRY_LARAVEL_DSN` is empty. To turn it on for a given environment:

```env
SENTRY_LARAVEL_DSN=https://<key>@o000000.ingest.sentry.io/0000000

# Optional: throttle traces/profiles in high-traffic environments.
# Defaults to 1.0 (capture everything) when unset.
SENTRY_TRACES_SAMPLE_RATE=0.2
SENTRY_PROFILES_SAMPLE_RATE=0.2
```

Local/test/CI: leave `SENTRY_LARAVEL_DSN` blank. `phpunit.xml` doesn't need
to touch it; the SDK simply skips capture.

## Where it's wired in

- `bootstrap/app.php` — `Integration::handles($exceptions)` inside
  `withExceptions()` forwards every unhandled exception to Sentry after
  Laravel's reporters run.
- `app/Http/Middleware/SetActorContext.php` — tags the active scope with
  the authenticated user (id + email) and the active `tenant_id` on every
  web request, so each Sentry event carries who-and-where context. Guarded
  by `app()->bound('sentry')` so the middleware is safe in tests.
- `config/sentry.php` — published from the package; ignores `/up` traces
  by default. Capture all the breadcrumbs (logs, cache, SQL, queue, HTTP)
  but **not** SQL bindings (avoid leaking PII like customer emails into
  the breadcrumb payload).

## Sensitive-data posture

`send_default_pii` is `false` (the package default). User context we attach
in `SetActorContext` is intentional and minimal (id + email). Don't widen
that without reading `config/sentry.php`'s scrubbing options first.

## Verifying

After setting a real DSN locally:

```bash
php artisan tinker --execute='throw new RuntimeException("sentry test");'
```

The exception will appear in Sentry within a few seconds, tagged with
`tenant_id` if a tenant context was active.

## Adding context manually

For ad-hoc breadcrumbs/extra data inside a job or service:

```php
\Sentry\addBreadcrumb(new \Sentry\Breadcrumb(
    \Sentry\Breadcrumb::LEVEL_INFO,
    \Sentry\Breadcrumb::TYPE_DEFAULT,
    'order',
    'attempting Stripe charge',
    ['order_id' => $order->id],
));
```

Use `\Sentry\captureException($e)` to report a caught exception that you
chose to swallow (otherwise `Integration::handles()` does the work).
