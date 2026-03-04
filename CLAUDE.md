# CLAUDE.md — KneadIt

## Project Overview
KneadIt — a SaaS platform for cottage food bakers. Laravel 12 + Filament 5 app with Stripe billing via Laravel Cashier.

## Tech Stack
- Laravel 12, PHP 8.4
- Filament 5 (admin panel)
- Laravel Cashier 16 (Stripe billing)
- Tailwind CSS, Alpine.js
- SQLite (dev), MySQL (production)

## Git Workflow
- Simplified gitflow: `main` + `develop`
- Commit format: `type: description`
- Git email: `jdavidsonwebdev@gmail.com`
- Remote: `git@github-kneadit:JeffreyDavidson/kneadit.git`

## Domain
- getkneadit.app (Cloudflare DNS → 137.184.194.56)
- Landing page in `landing/` subfolder (currently deployed to Forge)

## Pricing (3 tiers, all with 30-day free trial)
- Starter: $9/mo founding ($15 regular)
- Growth: $19/mo founding ($29 regular)
- Pro: $29/mo founding ($45 regular)

## Stripe (Test Mode)
- Products and prices created in Stripe test mode
- Price IDs in `.env` (STRIPE_PRICE_STARTER, STRIPE_PRICE_GROWTH, STRIPE_PRICE_PRO)
- Webhook endpoint: `/stripe/webhook`

## Key Files
- `config/saas.php` — plan definitions, features, limits, trial days
- `app/Http/Controllers/BillingController.php` — checkout, portal, plan swap
- `app/Http/Middleware/EnsureSubscribed.php` — plan tier gating
- `app/Models/User.php` — Billable trait, currentPlan(), hasPlan(), hasAccess()

## Filament 5 Rules
- `form(Schema $form): Schema` NOT `Form $form`
- All actions: `Filament\Actions\*` namespace
- Get/Set: `Filament\Schemas\Components\Utilities\Get` and `Set`
- `$view` is non-static: `protected string $view`
- `$navigationGroup` → `string|UnitEnum|null`
- `$navigationIcon` → `string|BackedEnum|null`

## Color Palette
- `--warm-black` #1c1410, `--espresso` #2a1f18, `--walnut` #4a3728
- `--cinnamon` #8b6844, `--honey` #d4920c, `--golden` #e8b04a
- `--butter` #f5d88e, `--flour` #faf4e8, `--cream` #fef9ef
