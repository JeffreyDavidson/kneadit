# Fix 28 Test Failures from Architecture Deepening

## Root Cause Analysis

The architecture deepening refactors introduced new classes (Registry, Services, DTOs) that reference
sub-DTO properties on TenantSettings (e.g., `$settings->loyalty->enabled`), but TenantSettings still
had flat scalar properties (`$loyaltyEnabled`). Several new classes were referenced but never created.

## Fix Plan

### Group 1: TenantSettings Sub-DTO Alignment (16 failures)
- [x] Created 11 sub-DTO classes in `app/DataTransferObjects/Settings/`
- [x] Added PHP 8.4 virtual property hooks to TenantSettings (changed from `final readonly` to `final` with per-property `readonly`)
- [x] Added 7 new engagement properties to TenantSettings constructor + resolve()
- [x] Updated 6 test files that construct TenantSettings directly

### Group 2: Missing CreateBirthdayCoupon Action (3 failures)
- [x] Created `app/Actions/Customers/CreateBirthdayCoupon.php`

### Group 3: Missing CapacityExceededException (2 failures)
- [x] Created `app/Exceptions/Orders/CapacityExceededException.php`

### Group 4: PricingEngine Blade Array→DTO (1 failure)
- [x] Updated blade template from `$result['ingredient_cost']` to `$result->ingredientCost`
- [x] Implemented `Wireable` on `PricingRecommendation` DTO for Livewire serialization

### Group 5: ProfitAnalysis Missing Methods (1 failure)
- [x] Added `getOverallStats()`, `getTotalRevenuePotential()`, `getProductAnalysis()`, `getTopProfitableProducts()`, `getLowestMarginProducts()`, `getMissingCostProducts()` — all delegate to the `ProductPortfolioSummary` DTO

### Group 6: RecipeCostCalculator Method Name (1 failure)
- [x] Updated test to call `refreshAnalysis()` instead of non-existent `calculateCosts()`

### Group 7: TenantAwareCommands forEachTenant (1 failure)
- [x] Updated test assertion from `forEachTenant` to `withinTenant`

### Group 8: PricingPosition + MarginHealth HasLabel (2 failures)
- [x] Added `implements HasLabel` and `getLabel()` to both enums

### Group 9: InventoryManager Closed Day Check (1 failure)
- [x] Added `BusinessSchedule` lookup in `CapacityCalculator::isAvailable()` to check `is_open`

## Review

**Result: 1930 tests passing, 0 failures**

### Files Created (14)
- `app/DataTransferObjects/Settings/` — 11 sub-DTO classes (StoreInfo, LoyaltySettings, OrderSettings, BrandingSettings, PaymentSettings, CateringSettings, EngagementSettings, PolicySettings, HomepageSettings, WebhookSettings, OnboardingSettings)
- `app/Actions/Customers/CreateBirthdayCoupon.php`
- `app/Exceptions/Orders/CapacityExceededException.php`

### Files Modified (13)
- `app/Services/Settings/TenantSettings.php` — changed to `final class` with per-property `readonly`, added virtual properties + 7 engagement params
- `app/DataTransferObjects/Financial/PricingRecommendation.php` — implemented `Wireable`
- `app/Enums/Financial/PricingPosition.php` — added `HasLabel`
- `app/Enums/Financial/MarginHealth.php` — added `HasLabel`
- `app/Filament/Pages/Analytics/ProfitAnalysis.php` — added 6 blade-facing methods
- `app/Services/Inventory/CapacityCalculator.php` — added BusinessSchedule closed-day check
- `resources/views/filament/pages/tools/pricing-engine.blade.php` — array→object property access
- 6 test files — updated TenantSettings constructor calls + assertion fixes
