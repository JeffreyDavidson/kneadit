# CLAUDE.md — KneadIt

## Project Overview
KneadIt — a SaaS platform for cottage food bakers. Laravel 12 + Filament 5 app with Stripe billing via Laravel Cashier.

## Filament 5 Rules
- `form(Schema $form): Schema` NOT `Form $form`
- All actions: `Filament\Actions\*` namespace
- Get/Set: `Filament\Schemas\Components\Utilities\Get` and `Set`
- `$view` is non-static: `protected string $view`
- `$navigationGroup` → `string|UnitEnum|null`
- `$navigationIcon` → `string|BackedEnum|null`
