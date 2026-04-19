---
name: kneadit-tenant-settings
description: "TenantSettings DTO and SettingsManager patterns for the KneadIt multi-tenant project. Activate whenever editing controllers, working with tenant settings, hero images, store branding, or when the user mentions TenantSettings, SettingsManager, settings(), Storage::url for hero images, tenant-scoped configuration, or AbstractSettingsManager. Covers the read-only DTO pattern, computed image URL methods, dependency injection rules for controllers vs closures, and how to extend the settings manager hierarchy for new scopes."
---

# KneadIt Tenant Settings

## TenantSettings DTO

- `App\Services\Settings\TenantSettings` — readonly DTO loaded once per request (singleton)
- Contains all shared tenant settings as typed properties (store info, branding, order config, etc.)
- Computed methods: `heroImageUrl()`, `cateringHeroImageUrl()`, `loyaltyHeroImageUrl()`, `giftCardsHeroImageUrl()`, `storeLogoUrl()`, `defaultTagline()`, `leadTimeDays()`
- Inject via method injection in controllers: `public function show(TenantSettings $settings)`
- In closures/components where DI isn't available: `app(TenantSettings::class)`

## Controller Rules (settings-related)

- **No direct `settings()` calls** — inject `TenantSettings` DTO via method injection
- **No `Storage::url()` for hero images** — use `TenantSettings` computed methods (`heroImageUrl()`, etc.)
- Pass `'settings' => $settings` to views; templates access via `$settings->propertyName`
- `settingsPageContent()` calls remain in controllers (page-specific, not on TenantSettings)

## Settings Managers

- `AbstractSettingsManager` (`app/Services/Settings/AbstractSettingsManager.php`) provides shared get/set/loadAll/flushCache logic
- `SettingsManager` (tenant-scoped) and `PlatformSettingsManager` extend it, implementing `cacheKey()` and `modelClass()`
- New settings scopes should extend `AbstractSettingsManager`
