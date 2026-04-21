# CLAUDE.md — KneadIt Project Rules

## Hard Rules (NEVER break these)

### 1. Verify before you use
- **NEVER** add a `use` statement, trait, or class reference without first confirming it exists in `vendor/`
- Run: `grep -r "ClassName" vendor/ --include='*.php'` before adding ANY Filament class/trait
- If it doesn't exist in vendor, it doesn't exist. Period.

### 2. Lint before you commit
- Pre-commit hook runs `php -l` on all staged PHP files (`.githooks/pre-commit`)
- If the hook isn't catching errors, run manually: `php -l path/to/file.php`
- NEVER force-skip the hook (`--no-verify`)

### 3. Test on server before merging to main
- Push to `develop` first when making risky changes
- SSH to cold-moon and verify: `ssh forge@137.184.194.56 "cd /home/forge/getkneadit.app/current && php artisan tinker --execute='echo 1;'"`
- `main` triggers production deploy — treat it with respect

### 4. Don't mass-edit without understanding
- Before changing 40+ files, verify the approach works on ONE file first
- Check vendor source code, not assumptions or memory from older Filament versions
- If unsure, ask Jeffrey rather than guessing

## Project Structure
- Server: cold-moon (`forge@137.184.194.56`), site at `/home/forge/getkneadit.app/current`
- Server IP is configured in `config('services.forge.server_ip')` — don't hardcode it in PHP or Blade files

> Filament resource/page structure and custom CSS conventions live in the `kneadit-filament-5` skill.

## Code Patterns

### Controllers
- Controllers must be either **invokable** (`__invoke`) or **resourceful** (only standard resource methods)
- Resource methods must follow standard order: `index`, `create`, `store`, `show`, `edit`, `update`, `destroy` (enforced by arch test)
- Prefer splitting multi-method controllers into invokable controllers when each method serves a distinct purpose
- **No inline auth checks** (`abort_unless($user->role === ...)`) — use Gates or middleware

> Tenant settings injection rules (no `settings()`, no `Storage::url()` for hero images, etc.) live in the `kneadit-tenant-settings` skill.
> Filament page conventions (access control, schema extraction, business logic placement) live in the `kneadit-filament-5` skill.

### Authorization
- Platform-level abilities use Gates defined in `AppServiceProvider::boot()` (e.g., `platform-admin`)
- Use `Gate::authorize('platform-admin')` in controllers, not inline role checks
- Model-specific authorization uses Policies

### Actions
- Write operations use single-purpose Action classes in `app/Actions/`
- Actions are invokable via `__invoke()` and resolved from the container: `app(CreateOrder::class)($data)`
- Controllers and Filament pages are thin callers — delegate business logic to actions
- Do NOT put write logic in observers, services (for writes), or controller methods

### View Layer
- **`@money()` Blade directive** — registered in `AppServiceProvider::boot()`, outputs `$X.XX`. Use for all monetary formatting in Blade; do NOT use for loyalty points or rating numbers.
- Project-specific examples: `ReviewsPageViewModel` (ViewModel), `OrderTrackingPresenter` (Presenter), `components/storefront/product-card` (reusable Blade component).

### Scopes & Enums
- Use `#[Scope]` attribute scopes or custom query builders for reusable query constraints — never repeat `where()` conditions inline
- Use PHP backed enums for any column with a fixed set of values (statuses, types, roles)
- Cast enum columns in the model's `casts()` method
- Use the `casts()` method (not `$casts` property) for consistency
- Never use hardcoded strings when an enum exists — use the enum value/case directly

### Extraction Guidelines
- **Don't extract standard validation rules** into custom Rule classes. `['required', 'string', 'max:255']` in FormRequests is idiomatic Laravel — only create Rule classes for actual custom logic.
- **Don't split cohesive services** just for line count. If private methods are shared across public methods, the class is cohesive. Check dependency direction before splitting.
- **Don't create DTOs for Livewire page properties.** Livewire requires individual public properties for form binding — a DTO can't replace them.
- **Models with `#[WithoutTimestamps]`** need their observers that set `created_at` manually — those observers are NOT no-ops.

### Exceptions
- Custom exceptions should store relevant context as `public readonly` constructor-promoted properties
- Implement `context(): array` for structured logging

## Git
- Pre-commit hook: `.githooks/pre-commit` (PHP lint)
- Hook path configured via `git config core.hooksPath .githooks`
- Branches: `main` (production deploy) and `develop` (staging)
- Commit format: `type: description` (feat/fix/hotfix/refactor/style/docs/chore/test)
- Do NOT add `Co-Authored-By` lines to commits — attribution is disabled in settings.json

===

<laravel-boost-guidelines>
=== foundation rules ===

# Laravel Boost Guidelines

The Laravel Boost guidelines are specifically curated by Laravel maintainers for this application. These guidelines should be followed closely to ensure the best experience when building Laravel applications.

## Foundational Context

This application is a Laravel application and its main Laravel ecosystems package & versions are below. You are an expert with them all. Ensure you abide by these specific packages & versions.

- php - 8.4
- filament/filament (FILAMENT) - v5
- laravel/cashier (CASHIER) - v16
- laravel/framework (LARAVEL) - v13
- laravel/pennant (PENNANT) - v1
- laravel/prompts (PROMPTS) - v0
- livewire/livewire (LIVEWIRE) - v4
- larastan/larastan (LARASTAN) - v3
- laravel/boost (BOOST) - v2
- laravel/mcp (MCP) - v0
- laravel/pail (PAIL) - v1
- laravel/pint (PINT) - v1
- laravel/sail (SAIL) - v1
- pestphp/pest (PEST) - v4
- phpunit/phpunit (PHPUNIT) - v12
- rector/rector (RECTOR) - v2
- alpinejs (ALPINEJS) - v3
- tailwindcss (TAILWINDCSS) - v4

## Skills Activation

This project has domain-specific skills available. You MUST activate the relevant skill whenever you work in that domain—don't wait until you're stuck.

- `cashier-stripe-development` — Handles Laravel Cashier Stripe integration including subscriptions, webhooks, Stripe Checkout, invoices, charges, refunds, trials, coupons, metered billing, and payment failure handling. Triggered when a user mentions Cashier, Billable, IncompletePayment, stripe_id, newSubscription, Stripe subscriptions, or billing. Also applies when setting up webhooks, handling SCA/3DS payment failures, testing with Stripe test cards, or troubleshooting incomplete subscriptions, CSRF webhook errors, or migration publish issues.
- `laravel-best-practices` — Apply this skill whenever writing, reviewing, or refactoring Laravel PHP code. This includes creating or modifying controllers, models, migrations, form requests, policies, jobs, scheduled commands, service classes, and Eloquent queries. Triggers for N+1 and query performance issues, caching strategies, authorization and security patterns, validation, error handling, queue and job configuration, route definitions, and architectural decisions. Also use for Laravel code reviews and refactoring existing Laravel code to follow best practices. Covers any task involving Laravel backend PHP code patterns.
- `pennant-development` — Use when working with Laravel Pennant the official Laravel feature flag package. Trigger whenever the query mentions Pennant by name or involves feature flags or feature toggles in a Laravel project. Tasks include defining feature flags checking whether features are active creating class based features in `app/Features` using Blade `@feature` directives scoping flags to users or teams building custom Pennant storage drivers protecting routes with feature flags testing feature flags with Pest or PHPUnit and implementing A B testing or gradual rollouts with feature flags. Do not trigger for generic Laravel configuration authorization policies authentication or non Pennant feature management systems.
- `pest-testing` — Use this skill for Pest PHP testing in Laravel projects only. Trigger whenever any test is being written, edited, fixed, or refactored — including fixing tests that broke after a code change, adding assertions, converting PHPUnit to Pest, adding datasets, and TDD workflows. Always activate when the user asks how to write something in Pest, mentions test files or directories (tests/Feature, tests/Unit, tests/Browser), or needs browser testing, smoke testing multiple pages for JS errors, or architecture tests. Covers: test()/it()/expect() syntax, datasets, mocking, browser testing (visit/click/fill), smoke testing, arch(), Livewire component tests, RefreshDatabase, and all Pest 4 features. Do not use for factories, seeders, migrations, controllers, models, or non-test PHP code.
- `tailwindcss-development` — Always invoke when the user's message includes 'tailwind' in any form. Also invoke for: building responsive grid layouts (multi-column card grids, product grids), flex/grid page structures (dashboards with sidebars, fixed topbars, mobile-toggle navs), styling UI components (cards, tables, navbars, pricing sections, forms, inputs, badges), adding dark mode variants, fixing spacing or typography, and Tailwind v3/v4 work. The core use case: writing or fixing Tailwind utility classes in HTML templates (Blade, JSX, Vue). Skip for backend PHP logic, database queries, API routes, JavaScript with no HTML/CSS component, CSS file audits, build tool configuration, and vanilla CSS.
- `kneadit-filament-5` — Filament 5 conventions and gotchas specific to the KneadIt project. Activate whenever working in app/Filament/, editing/creating Filament Resources, Pages, Schemas, Tables, Actions, or when the user mentions Filament, slide-overs, EditAction, form schemas, or Filament resource pages. Covers Filament 5 namespace changes (Forms vs Schemas, Tables\Actions removed), forbidden traits and methods, slide-over patterns, schema extraction for large pages, access control via RequiresManagerRole, and the project-specific status transition action factory.
- `kneadit-mailables` — KneadIt-specific mailable patterns including the unified OrderStatusMail, birthday coupon generation, and email-related domain rules. Activate when editing or creating mailables, working under app/Mail/, working with order status emails, birthday emails, or when the user mentions OrderStatusMail, BirthdayService, birthday coupons, BDAY codes, status emails, or order email templates. Covers why one mailable handles all order statuses (resolved via match), how to add a new status email, deterministic birthday coupon code generation, and the separation between birthday program and birthday coupon toggles.
- `kneadit-tenant-settings` — TenantSettings DTO and SettingsManager patterns for the KneadIt multi-tenant project. Activate whenever editing controllers, working with tenant settings, hero images, store branding, or when the user mentions TenantSettings, SettingsManager, settings(), Storage::url for hero images, tenant-scoped configuration, or AbstractSettingsManager. Covers the read-only DTO pattern, computed image URL methods, dependency injection rules for controllers vs closures, and how to extend the settings manager hierarchy for new scopes.
- `kneadit-versioning` — KneadIt release-tagging discipline. Activate whenever the user mentions cutting a tag, releasing, version bumps, semver, or asks something like 'should we cut a release', 'what's next after this merge', or merges a release PR. Also activate when reviewing or opening a release PR (develop → main). Covers when a minor bump is justified vs a patch, when to skip a tag entirely, when a migration warrants its own release, and the historical anti-pattern of bumping minor for every PR cluster.

## Conventions

- You must follow all existing code conventions used in this application. When creating or editing a file, check sibling files for the correct structure, approach, and naming.
- Use descriptive names for variables and methods. For example, `isRegisteredForDiscounts`, not `discount()`.
- Check for existing components to reuse before writing a new one.

## Verification Scripts

- Do not create verification scripts or tinker when tests cover that functionality and prove they work. Unit and feature tests are more important.

## Application Structure & Architecture

- Stick to existing directory structure; don't create new base folders without approval.
- Do not change the application's dependencies without approval.

## Frontend Bundling

- If the user doesn't see a frontend change reflected in the UI, it could mean they need to run `npm run build`, `npm run dev`, or `composer run dev`. Ask them.

## Documentation Files

- You must only create documentation files if explicitly requested by the user.

## Replies

- Be concise in your explanations - focus on what's important rather than explaining obvious details.

=== boost rules ===

# Laravel Boost

## Tools

- Laravel Boost is an MCP server with tools designed specifically for this application. Prefer Boost tools over manual alternatives like shell commands or file reads.
- Use `database-query` to run read-only queries against the database instead of writing raw SQL in tinker.
- Use `database-schema` to inspect table structure before writing migrations or models.
- Use `get-absolute-url` to resolve the correct scheme, domain, and port for project URLs. Always use this before sharing a URL with the user.
- Use `browser-logs` to read browser logs, errors, and exceptions. Only recent logs are useful, ignore old entries.

## Searching Documentation (IMPORTANT)

- Always use `search-docs` before making code changes. Do not skip this step. It returns version-specific docs based on installed packages automatically.
- Pass a `packages` array to scope results when you know which packages are relevant.
- Use multiple broad, topic-based queries: `['rate limiting', 'routing rate limiting', 'routing']`. Expect the most relevant results first.
- Do not add package names to queries because package info is already shared. Use `test resource table`, not `filament 4 test resource table`.

### Search Syntax

1. Use words for auto-stemmed AND logic: `rate limit` matches both "rate" AND "limit".
2. Use `"quoted phrases"` for exact position matching: `"infinite scroll"` requires adjacent words in order.
3. Combine words and phrases for mixed queries: `middleware "rate limit"`.
4. Use multiple queries for OR logic: `queries=["authentication", "middleware"]`.

## Artisan

- Run Artisan commands directly via the command line (e.g., `php artisan route:list`). Use `php artisan list` to discover available commands and `php artisan [command] --help` to check parameters.
- Inspect routes with `php artisan route:list`. Filter with: `--method=GET`, `--name=users`, `--path=api`, `--except-vendor`, `--only-vendor`.
- Read configuration values using dot notation: `php artisan config:show app.name`, `php artisan config:show database.default`. Or read config files directly from the `config/` directory.
- To check environment variables, read the `.env` file directly.

## Tinker

- Execute PHP in app context for debugging and testing code. Do not create models without user approval, prefer tests with factories instead. Prefer existing Artisan commands over custom tinker code.
- Always use single quotes to prevent shell expansion: `php artisan tinker --execute 'Your::code();'`
  - Double quotes for PHP strings inside: `php artisan tinker --execute 'User::where("active", true)->count();'`

=== php rules ===

# PHP

- Always use curly braces for control structures, even for single-line bodies.
- Use PHP 8 constructor property promotion: `public function __construct(public GitHub $github) { }`. Do not leave empty zero-parameter `__construct()` methods unless the constructor is private.
- Use explicit return type declarations and type hints for all method parameters: `function isAccessible(User $user, ?string $path = null): bool`
- Use TitleCase for Enum keys: `FavoritePerson`, `BestLake`, `Monthly`.
- Prefer PHPDoc blocks over inline comments. Only add inline comments for exceptionally complex logic.
- Use array shape type definitions in PHPDoc blocks.

=== herd rules ===

# Laravel Herd

- The application is served by Laravel Herd at `https?://[kebab-case-project-dir].test`. Use the `get-absolute-url` tool to generate valid URLs. Never run commands to serve the site. It is always available.
- Use the `herd` CLI to manage services, PHP versions, and sites (e.g. `herd sites`, `herd services:start <service>`, `herd php:list`). Run `herd list` to discover all available commands.

=== tests rules ===

# Test Enforcement

- Every change must be programmatically tested. Write a new test or update an existing test, then run the affected tests to make sure they pass.
- Run the minimum number of tests needed to ensure code quality and speed. Use `php artisan test --compact` with a specific filename or filter.

=== laravel/core rules ===

# Do Things the Laravel Way

- Use `php artisan make:` commands to create new files (i.e. migrations, controllers, models, etc.). You can list available Artisan commands using `php artisan list` and check their parameters with `php artisan [command] --help`.
- If you're creating a generic PHP class, use `php artisan make:class`.
- Pass `--no-interaction` to all Artisan commands to ensure they work without user input. You should also pass the correct `--options` to ensure correct behavior.

### Model Creation

- When creating new models, create useful factories and seeders for them too. Ask the user if they need any other things, using `php artisan make:model --help` to check the available options.

## APIs & Eloquent Resources

- For APIs, default to using Eloquent API Resources and API versioning unless existing API routes do not, then you should follow existing application convention.

## URL Generation

- When generating links to other pages, prefer named routes and the `route()` function.

## Testing

- When creating models for tests, use the factories for the models. Check if the factory has custom states that can be used before manually setting up the model.
- Faker: Use methods such as `$this->faker->word()` or `fake()->randomDigit()`. Follow existing conventions whether to use `$this->faker` or `fake()`.
- When creating tests, make use of `php artisan make:test [options] {name}` to create a feature test, and pass `--unit` to create a unit test. Most tests should be feature tests.

## Vite Error

- If you receive an "Illuminate\Foundation\ViteException: Unable to locate file in Vite manifest" error, you can run `npm run build` or ask the user to run `npm run dev` or `composer run dev`.

## Deployment

- Laravel can be deployed using [Laravel Cloud](https://cloud.laravel.com/), which is the fastest way to deploy and scale production Laravel applications.

=== pint/core rules ===

# Laravel Pint Code Formatter

- If you have modified any PHP files, you must run `vendor/bin/pint --dirty --format agent` before finalizing changes to ensure your code matches the project's expected style.
- Do not run `vendor/bin/pint --test --format agent`, simply run `vendor/bin/pint --format agent` to fix any formatting issues.

=== pest/core rules ===

## Pest

- This project uses Pest for testing. Create tests: `php artisan make:test --pest {name}`.
- Run tests: `php artisan test --compact` or filter: `php artisan test --compact --filter=testName`.
- Do NOT delete tests without approval.

</laravel-boost-guidelines>
