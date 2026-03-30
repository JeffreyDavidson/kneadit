# Refactor Test Files: Replace query()->create() with Factory Usage

## Tasks
- [x] 1. BakerBrandedEmailTest.php
- [x] 2. LoyaltyRewardTest.php
- [x] 3. LoyaltyTest.php
- [x] 4. ActivityLogTest.php
- [x] 5. CustomerTest.php
- [x] 6. ProductTest.php
- [x] 7. SurveyTest.php
- [x] 8. SupplierTest.php
- [x] 9. Run php -l on all modified files
- [x] 10. Run vendor/bin/pint --dirty

## Review
- Replaced all `Model::query()->create([...])` calls with proper factory usage across all 8 test files
- Used `User::factory()->owner()->create()` instead of manual User creation with bcrypt
- Used `Customer::factory()->create()` instead of manual Customer creation
- Used `Order::factory()->for($customer)->recycle($user)` with state methods (`.delivered()`, `.cancelled()`, `.confirmed()`, `.ready()`) instead of manual Order creation with inline status enums
- Used `Product::factory()->for($category)->create()` instead of manual Product creation with `category_id`
- Used `LoyaltyReward::factory()->for($product)->create()` with `RewardType` enum instead of raw strings
- Used `LoyaltyPoint::factory()->for($customer)->earned(N)->create()` and `.redeemed(N)` states
- Used `Recipe::factory()->for($product)`, `SeasonalItem::factory()->for($product)`, `SurveyResponse::factory()->for($survey)` for belongsTo relationships
- Used `Supplier::factory()`, `Ingredient::factory()`, `Survey::factory()`, `Category::factory()` throughout
- Pint fixed unused imports in CustomerTest.php (removed `OrderStatus`) and ProductTest.php (removed `Category`, `Date`)
- All 54 tests pass with 83 assertions, unchanged from before refactoring
