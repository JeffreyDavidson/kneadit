# Session Summary — 2026-03-28

## Completed

### Naming Convention Fixes (40 classes)
- [x] Phase 1: Rename 19 Mail classes → `*Mail` suffix
- [x] Phase 2: Rename 6 Listener classes → `*Listener` suffix
- [x] Phase 3: Rename 15 Command classes → `*Command` suffix
- [x] Phase 4: Add 3 arch tests enforcing naming conventions

### Model Cleanup
- [x] Holiday: `daysUntilDeadline()`, `isDeadlinePassed()` → Attributes
- [x] Ingredient: `isLowStock()`, `isOutOfStock()`, `getStockStatus()` → Attributes
- [x] GiftCard: `isUsable()` → Attribute
- [x] Coupon: `calculateDiscount()` → moved to CouponService
- [x] Product: `getPrimaryImageUrl()`, `isInSeason()` → Attributes, removed dead `pendingWaitlistCount()`

### Filament Resource Test Gaps
- [x] GiftCards: fix create bug (code + balance), add create test
- [x] CustomerPhotos: add create/edit/validation with file upload
- [x] Recipes: add create test with ingredients repeater
- [x] Surveys: add create test with questions repeater
- [x] CapacityLimits: fix create bug (date column), add weekday create test

### Remaining Todos Resolved
- [x] BlogPosts: fix category filter null label crash, replace todo with working test
- [x] CreateStripeProducts: replace todo with signature + config tests

## Remaining (infrastructure-limited, 3 todos)
- [ ] OpenTicketsWidget — central panel routes not registered in tests
- [ ] ViewTenant — Filament 5 component issue
- [ ] ReferralProgram — needs real tenancy initialization

## Bugs Found & Fixed This Session: 4
- GiftCard create: missing auto-generated code and current_balance
- CapacityLimit create: missing required date column
- BlogPosts: SelectFilter null label crash on empty categories
- Product: dead `pendingWaitlistCount()` method removed

## Test Suite: 1,396 tests, 3,750 assertions, 3 todos
