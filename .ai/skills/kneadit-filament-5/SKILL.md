---
name: kneadit-filament-5
description: "Filament 5 conventions and gotchas specific to the KneadIt project. Activate whenever working in app/Filament/, editing/creating Filament Resources, Pages, Schemas, Tables, Actions, or when the user mentions Filament, slide-overs, EditAction, form schemas, or Filament resource pages. Covers Filament 5 namespace changes (Forms vs Schemas, Tables\\Actions removed), forbidden traits and methods, slide-over patterns, schema extraction for large pages, access control via RequiresManagerRole, and the project-specific status transition action factory."
---

# KneadIt Filament 5 Rules

## Hard Rules — Filament 5 specifics

- Form signature: `form(Schema $schema): Schema` — NOT `Form $form`
- **NO** `Filament\Tables\Actions` namespace — use `Filament\Actions\*` for everything
- **NO** `Filament\Forms\Get` / `Filament\Forms\Set` — use `Filament\Schemas\Components\Utilities\Get` and `Set`
- **NO** `HasSlideOverForm` trait — it doesn't exist in Filament 5
- Slide-overs: use `EditAction::make()->slideOver()` on tables + index-only page routes
- BlogPosts are the ONLY resource with dedicated create/edit page routes (full pages, not slide-overs)
- Sections in slide-over forms need `->columnSpanFull()` to fill width

## Project Structure

- Resources: `app/Filament/Resources/{Name}/` with separate `Schemas/`, `Tables/`, `Pages/` dirs
- Some resources (LoyaltyRewards, CustomerPhotos, GalleryPhotos) have inline table config in the Resource file
- Custom CSS: `public/css/filament-custom.css` (cache-busted via `?v=filemtime()`)

## Filament Pages

- **Access control:** Use the `RequiresManagerRole` trait (`app/Filament/Concerns/RequiresManagerRole.php`) for pages requiring manager access. Pages that also need a feature flag override `canAccess()` using `static::hasManagerAccess() && Feature::active('...')`.
- **Large pages:** When a Filament page exceeds ~200 lines of form/schema config, extract independent sections into static schema classes under a `Schemas/` subdirectory (e.g., `Schemas/PageContent/MenuTabSchema.php` with `public static function make(): Tab`). See `ManagePageContent` for the pattern.
- **Business logic:** Extract DNS checking, API calls, image generation, and similar logic to service classes. Filament pages should be thin coordinators that call services and send notifications. See `CustomDomainService`, `AppIconGeneratorService`, `CaptionGeneratorService` for examples.
- **Status transition actions:** Use a factory method for near-identical Filament table actions that vary only in name/label/icon/color/target. See `OrdersTable::statusTransitionAction()`.

## Verify before you use

- Filament 5 changed many namespaces from v4. Before adding ANY Filament class/trait, run: `grep -r "ClassName" vendor/ --include='*.php'` to confirm it exists.
- If it doesn't exist in vendor, it doesn't exist. Don't trust memory from older Filament versions.
