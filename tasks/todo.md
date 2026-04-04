# Issue #4: Discount System Follow-ups

## Task 1: Reversal logic on order cancellation
- [x] Create `CouponTransactionType` enum (Usage, Reversal)
- [x] Create `CouponTransaction` model, migration, factory
- [x] Add `reverseDiscounts` effect to `OrderStatusEffectDispatcher`
- [x] Write tests for reversal logic (3 tests added)

## Task 2: View/UI updates for gift card amount
- [x] Create migration to add `gift_card_amount` to orders table
- [x] Update Order model (`$fillable`, `$attributes`, casts)
- [x] Update `ApplyGiftCard` pipe to populate `gift_card_amount`
- [x] Update invoice template
- [x] Update order confirmation view
- [x] Update Filament ViewOrder page (Grid 4→5)
- [x] Update Filament OrderForm (Grid 4→5)

## Task 3: Controller consolidation
- [x] Analyzed `ApplyCouponController` vs `CouponValidationController`
- [x] Decision: keep separate — different consumers (storefront AJAX vs API) with different response shapes

## Task 4: Backfill migration for historical orders
- [x] Create migration to backfill `gift_card_amount` from `gift_card_transactions`

## Task 5: CouponValidationController bug fix
- [x] Write tests reproducing the bug (TDD — 2 failing tests)
- [x] Fix `ApiResponse::success()` → `ApiResponse::error()` for invalid coupons
